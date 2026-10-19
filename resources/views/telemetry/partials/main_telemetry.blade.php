<div class="min-w-52 flex items-center justify-center gap-3">
    <div class="min-w-16 rounded-lg bg-[var(--digital)] border border-[var(--border)] px-1 py-1 text-center">
        <div id="telemetry-current-gear"
             class="text-3xl text-[var(--char)] font-mono-timing font-bold tabular-nums">—</div>
    </div>

<div class="min-w-22 h-10 rounded-lg bg-[var(--digital)]
            border border-[var(--border)] px-1 py-1
            flex items-center justify-center text-center">
    <div class="flex h-full w-full items-center justify-center
                text-3xl leading-none text-[var(--char)]
                font-mono-timing font-bold tabular-nums">
        <span id="telemetry-current-speed">—</span>
    </div>
</div>

    <div class="rounded-lg bg-[var(--digital)] border border-[var(--border)] px-1 py-1 mr-5 text-center">
        <div id="telemetry-current-lap-time"
             class="text-3xl text-[var(--char)] font-mono-timing font-bold tabular-nums">00:00.000</div>
    </div>

<div class="flex items-end justify-center gap-4 px-1 pt-1 pb-1">
        <div class="flex w-7 flex-col items-center gap-1">
            <div class="flex h-14 w-7 items-end overflow-hidden rounded-sm border border-[var(--border)] bg-black/30 p-[2px]" role="meter" aria-label="Clutch" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div id="telemetry-current-clutch-bar" class="w-full rounded-[2px] bg-blue-600 transition-[height] duration-75" style="height:0%"></div>
            </div>
            <span id="telemetry-current-clutch-value" class="font-mono text-xs tabular-nums">0%</span>
        </div>
        <div class="flex w-7 flex-col items-center gap-1">
            <div class="flex h-14 w-7 items-end overflow-hidden rounded-sm border border-[var(--border)] bg-black/30 p-[2px]" role="meter" aria-label="Brake" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div id="telemetry-current-brake-bar" class="w-full rounded-[2px] bg-red-500 transition-[height] duration-75" style="height:0%"></div>
            </div>
            <span id="telemetry-current-brake-value" class="font-mono text-xs tabular-nums">0%</span>
        </div>
        <div class="flex w-7 flex-col items-center gap-1">
            <div class="flex h-14 w-7 items-end overflow-hidden rounded-sm border border-[var(--border)] bg-black/30 p-[2px]" role="meter" aria-label="Throttle" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <div id="telemetry-current-throttle-bar" class="w-full rounded-[2px] bg-green-500 transition-[height] duration-75" style="height:0%"></div>
            </div>
            <span id="telemetry-current-throttle-value" class="font-mono text-xs tabular-nums">0%</span>
        </div>
    </div>
    <div class="flex w-24 flex-col items-center justify-center gap-1 px-1 py-1">
@include('telemetry.partials.steering.classic')
        <span id="telemetry-current-steering-value" class="font-mono text-sm font-semibold tabular-nums">—°</span>
    </div>

</div>
