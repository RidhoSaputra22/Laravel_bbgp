@props([
    'title' => 'Sisa Waktu',
    'targetAt' => null,
    'caption' => null,
    'expiredLabel' => 'Waktu sesi telah berakhir.',
    'fallbackLabel' => 'Timer belum tersedia untuk sesi ini.',
])

@php
    $deadline = match (true) {
        $targetAt instanceof \Carbon\CarbonInterface => $targetAt->copy(),
        filled($targetAt) => \Illuminate\Support\Carbon::parse($targetAt),
        default => null,
    };

    $deadlineIso = $deadline?->toIso8601String();
    $deadlineText = $deadline?->format('d M Y H:i').' WITA';
@endphp

<div
    x-data="{
        targetAt: @js($deadlineIso),
        unavailable: {{ $deadlineIso ? 'false' : 'true' }},
        expired: false,
        intervalId: null,
        segments: {
            days: '00',
            hours: '00',
            minutes: '00',
            seconds: '00',
        },
        init() {
            if (this.unavailable) {
                return;
            }

            this.updateCountdown();
            this.intervalId = setInterval(() => this.updateCountdown(), 1000);
        },
        destroy() {
            if (this.intervalId) {
                clearInterval(this.intervalId);
            }
        },
        updateCountdown() {
            const diffSeconds = Math.floor((new Date(this.targetAt).getTime() - Date.now()) / 1000);

            if (diffSeconds <= 0) {
                this.expired = true;
                this.segments = {
                    days: '00',
                    hours: '00',
                    minutes: '00',
                    seconds: '00',
                };

                if (this.intervalId) {
                    clearInterval(this.intervalId);
                }

                return;
            }

            this.expired = false;

            const days = Math.floor(diffSeconds / 86400);
            const hours = Math.floor((diffSeconds % 86400) / 3600);
            const minutes = Math.floor((diffSeconds % 3600) / 60);
            const seconds = diffSeconds % 60;

            this.segments = {
                days: String(days).padStart(2, '0'),
                hours: String(hours).padStart(2, '0'),
                minutes: String(minutes).padStart(2, '0'),
                seconds: String(seconds).padStart(2, '0'),
            };
        },
    }"
    {{ $attributes->class(['rounded-sm bg-gradient-to-br from-[#0c5a90] to-[#0f3557] p-5 text-white shadow-sm']) }}
>
    <div class="mb-4 flex items-start justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/70">
                Countdown Timer
            </p>
            <h3 class="mt-1 text-lg font-bold">
                {{ $title }}
            </h3>
        </div>

        <div class="rounded-full bg-white/12 px-3 py-1 text-xs font-semibold text-white/90">
            WITA
        </div>
    </div>

    <template x-if="unavailable">
        <div class="rounded-sm border border-white/10 bg-white/10 px-4 py-4 text-sm text-white/85">
            {{ $fallbackLabel }}
        </div>
    </template>

    <template x-if="!unavailable">
        <div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-sm bg-white/12 px-3 py-4 text-center">
                    <div class="text-2xl font-bold leading-none" x-text="segments.days"></div>
                    <div class="mt-2 text-[11px] uppercase tracking-[0.18em] text-white/70">Hari</div>
                </div>

                <div class="rounded-sm bg-white/12 px-3 py-4 text-center">
                    <div class="text-2xl font-bold leading-none" x-text="segments.hours"></div>
                    <div class="mt-2 text-[11px] uppercase tracking-[0.18em] text-white/70">Jam</div>
                </div>

                <div class="rounded-sm bg-white/12 px-3 py-4 text-center">
                    <div class="text-2xl font-bold leading-none" x-text="segments.minutes"></div>
                    <div class="mt-2 text-[11px] uppercase tracking-[0.18em] text-white/70">Menit</div>
                </div>

                <div class="rounded-sm bg-white/12 px-3 py-4 text-center">
                    <div class="text-2xl font-bold leading-none" x-text="segments.seconds"></div>
                    <div class="mt-2 text-[11px] uppercase tracking-[0.18em] text-white/70">Detik</div>
                </div>
            </div>

            <p x-show="expired" class="mt-4 rounded-sm bg-[#f59e0b]/20 px-4 py-3 text-sm font-medium text-white">
                {{ $expiredLabel }}
            </p>
        </div>
    </template>

    @if ($caption)
        <p class="mt-4 text-sm leading-relaxed text-white/78">
            {{ $caption }}
        </p>
    @endif

    @if ($deadlineText)
        <div class="mt-3 text-xs font-medium uppercase tracking-[0.14em] text-white/65">
            Berakhir pada {{ $deadlineText }}
        </div>
    @endif
</div>
