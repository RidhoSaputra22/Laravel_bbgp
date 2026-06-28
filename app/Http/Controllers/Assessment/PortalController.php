<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Services\Assessment\AssessmentAttemptService;
use App\Services\Assessment\AssessmentPortalAuthService;
use App\Services\Assessment\AssessmentPortalService;
use Illuminate\Http\Request;

class PortalController extends Controller
{
    public function __construct(
        private readonly AssessmentPortalAuthService $authService,
        private readonly AssessmentPortalService $portalService,
        private readonly AssessmentAttemptService $attemptService
    ) {}

    public function landing()
    {
        return $this->authService->isAuthenticated()
            ? redirect()->route('assessment.portal.dashboard')
            : redirect()->route('assessment.portal.auth');
    }

    public function dashboard()
    {
        $guru = $this->requireGuru();
        $dashboardCards = $this->portalService->getDashboardTargets($guru);

        return view('assessment.index', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'dashboardCards' => $dashboardCards,
        ]);
    }

    public function show(string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if ($meta['status'] === 'submitted') {
            return redirect()->route('assessment.portal.result', $target->id);
        }

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $meta['description'],
                ]);
        }

        $attempt = $this->portalService->openAttempt($target);

        return view('assessment.show.show', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $target->fresh(['assignment.assessments.forms.fields', 'session', 'attempt']),
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target->fresh(['assignment.assessments.forms.fields', 'session', 'attempt'])),
        ]);
    }

    public function submit(Request $request, string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $meta = $this->portalService->buildTargetMeta($target);

        if (! in_array($meta['status'], ['ready', 'in_progress'], true)) {
            return redirect()
                ->route('assessment.portal.dashboard')
                ->withErrors([
                    'portal' => $meta['description'],
                ]);
        }

        $attempt = $this->portalService->openAttempt($target);
        $attempt = $this->attemptService->submit(
            $attempt,
            $request->input('answers', []),
            $request->file('answers', [])
        );

        return redirect()
            ->route('assessment.portal.result', $target->id)
            ->with('assessment_portal_success', 'Jawaban assessment berhasil dikirim.');
    }

    public function result(string $id)
    {
        $guru = $this->requireGuru();
        $target = $this->portalService->findTargetForGuru($guru, (int) $id);
        $attempt = $target->attempt;

        if (! $attempt) {
            return redirect()->route('assessment.portal.show', $target->id);
        }

        if ($attempt->status !== 'submitted') {
            return redirect()
                ->route('assessment.portal.show', $target->id)
                ->withErrors([
                    'portal' => 'Assessment ini belum selesai dikirim.',
                ]);
        }

        return view('assessment.result.result', [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => $this->portalService->buildTargetMeta($target),
            'summary' => $this->attemptService->buildResultSummary($attempt),
            'scoringSummary' => $this->attemptService->buildScoringSummary($attempt),
            'answerLookup' => $this->attemptService->buildAnswerLookup($attempt),
        ]);
    }

    private function requireGuru(): Guru
    {
        $guru = $this->authService->currentGuru();

        abort_unless($guru, 403);

        return $guru;
    }
}
