<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAssignment;
use App\Models\Guru;
use App\Services\AssessmentAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AssessmentAssignmentController extends Controller
{
    private string $menu = 'assessment-penugasan';

    public function __construct(
        private readonly AssessmentAssignmentService $assignmentService
    ) {}

    public function index()
    {
        $this->authorizeAccess();

        $datas = AssessmentAssignment::with(['assessment', 'creator'])
            ->withCount(['targets', 'sessions'])
            ->orderByDesc('id')
            ->get();

        return view('pages.admin.assessment.assignment.index', [
            'menu' => $this->menu,
            'datas' => $datas,
        ]);
    }

    public function create()
    {
        $this->authorizeAccess();

        $assessmentList = Assessment::with(['forms.fields'])
            ->withCount('forms')
            ->where('is_active', true)
            ->whereIn('status', ['draft', 'publish'])
            ->orderBy('judul')
            ->get();

        $guruList = Guru::query()
            ->select([
                'id',
                'nama_lengkap',
                'email',
                'satuan_pendidikan',
                'kabupaten',
                'status_kepegawaian',
                'is_verif',
            ])
            ->orderBy('nama_lengkap')
            ->get();

        return view('pages.admin.assessment.assignment.create', [
            'menu' => $this->menu,
            'assessmentList' => $assessmentList,
            'guruList' => $guruList,
            'batchThreshold' => AssessmentAssignmentService::BATCH_THRESHOLD,
            'sessionCapacity' => AssessmentAssignmentService::TARGETS_PER_SESSION,
            'defaultSessionDurationHours' => AssessmentAssignmentService::DEFAULT_SESSION_DURATION_HOURS,
            'sessionDurationOptions' => AssessmentAssignmentService::SESSION_DURATION_OPTIONS,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $this->validatePayload($request);

        try {
            $assignment = $this->assignmentService->createAssignment(
                $validated,
                session('user_id') ? (int) session('user_id') : null
            );

            return redirect()
                ->route('assessment.assignment.show', $assignment->id)
                ->with('message', 'store');
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'assignment' => 'Terjadi kesalahan saat memproses penugasan assesment.',
                ]);
        }
    }

    public function show(string $id)
    {
        $this->authorizeAccess();

        $assignment = AssessmentAssignment::with([
            'assessment.forms.fields',
            'creator',
            'sessions.targets',
            'targets.guru',
            'targets.session',
        ])
            ->withCount(['targets', 'sessions'])
            ->findOrFail($id);

        return view('pages.admin.assessment.assignment.show', [
            'menu' => $this->menu,
            'assignment' => $assignment,
        ]);
    }

    private function authorizeAccess(): void
    {
        abort_unless(
            in_array(session('role'), ['admin', 'superadmin', 'kepala', 'database'], true),
            403
        );
    }

    private function validatePayload(Request $request): array
    {
        return Validator::make(
            $request->all(),
            [
                'kode_penugasan' => 'nullable|string|max:100|unique:assessment_assignments,kode_penugasan',
                'judul_penugasan' => 'required|string|max:255',
                'assessment_id' => [
                    'required',
                    'integer',
                    Rule::exists('assessments', 'id')->where(function ($query) {
                        $query->where('is_active', true)
                            ->whereIn('status', ['draft', 'publish']);
                    }),
                ],
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'durasi_sesi_jam' => [
                    'required',
                    'integer',
                    Rule::in(AssessmentAssignmentService::SESSION_DURATION_OPTIONS),
                ],
                'guru_ids' => 'required|array|min:1',
                'guru_ids.*' => 'required|integer|distinct|exists:gurus,id',
            ],
            [
                'judul_penugasan.required' => 'Judul penugasan wajib diisi.',
                'assessment_id.required' => 'Form assesment wajib dipilih.',
                'assessment_id.exists' => 'Assesment yang dipilih tidak valid atau sudah nonaktif.',
                'durasi_sesi_jam.required' => 'Durasi sesi assessment wajib dipilih.',
                'durasi_sesi_jam.in' => 'Durasi sesi assessment harus sesuai pilihan yang tersedia.',
                'guru_ids.required' => 'Minimal pilih satu guru untuk ditugasi.',
                'guru_ids.min' => 'Minimal pilih satu guru untuk ditugasi.',
                'guru_ids.*.exists' => 'Ada guru yang dipilih tetapi datanya tidak ditemukan.',
                'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            ]
        )->validate();
    }
}
