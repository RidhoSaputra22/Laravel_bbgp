@props([
    'id',
    'name',
    'type' => 'radio',
    'value',
    'checked' => false,
    'label',
    'description' => null,
])

<label
    for="{{ $id }}"
    class="block cursor-pointer rounded-2xl border border-[#dce8f1] bg-white px-4 py-[14px] transition hover:border-[#1376bd]/60"
>
    <span class="flex items-start gap-3">
        <input
            id="{{ $id }}"
            type="{{ $type }}"
            name="{{ $name }}"
            value="{{ $value }}"
            @checked($checked)
            @class([
                'mt-1 h-4 w-4 border-slate-300 text-[#1376bd] focus:ring-[#1376bd]/30',
                'rounded' => $type === 'checkbox',
            ])
        >

        <span class="block">
            <strong class="block text-slate-800">
                {{ $label }}
            </strong>

            @if ($description)
                <span class="mt-0.5 block text-sm text-slate-500">
                    {{ $description }}
                </span>
            @endif
        </span>
    </span>
</label>
