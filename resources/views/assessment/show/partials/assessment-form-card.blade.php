<x-assessment::ui.card>
    <h4 class="text-lg font-bold text-slate-800">
        {{ $form['judul_form'] }}
    </h4>

    <div class="mb-3 text-slate-700">
        {{ $form['deskripsi'] ?: 'Isi pertanyaan pada bagian ini sesuai kondisi terbaru Anda.' }}
    </div>

    @foreach ($form['fields'] ?? [] as $field)
        @include('assessment.show.partials.assessment-field', ['field' => $field])
    @endforeach
</x-assessment::ui.card>
