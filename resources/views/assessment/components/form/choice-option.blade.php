@props([
    'id',
    'name',
    'type' => 'radio',
    'value',
    'checked' => false,
    'label',
    'description' => null,
    'required' => null,
    'hint' => null,
])

<label for="{{ $id }}"
    class="block cursor-pointer rounded-sm border border-[#dce8f1] bg-white px-4 py-[14px] transition hover:border-[#1376bd]/60">
    <span class="flex items-start gap-3">
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
            @checked($checked) @required($required) @class([
                'mt-1 h-4 w-4 border-slate-300 text-[#1376bd] focus:ring-[#1376bd]/30',
                'rounded' => $type === 'checkbox',
            ])>

        <span class="block">
            <div>
                @if ($label)
                    <span class="block text-sm font-semibold text-slate-700">
                        {{ $label }}
                        @if ($required)
                            <span class="text-red-600">*</span>
                        @endif
                    </span>
                @endif

                @if ($description)
                    <p class="mt-1 block text-sm text-slate-700">
                        {{ $description }}
                    </p>
                @endif
            </div>
            @if ($hint)
                <p class="block text-sm text-slate-700">
                    {{ $hint }}
                </p>
            @endif
        </span>
    </span>
</label>
