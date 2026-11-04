
<div class="flex items-center gap-1" id="telemetry-panel-controls">
    <button
        type="button"
        id="telemetry-panel-toggle"
        class="rounded-lg border border-[var(--border)] px-2 py-2 text-sm"
        aria-pressed="true"
        title="{{ __('ui.show_hide_instruments') }}"
        aria-label="{{ __('ui.hide_instruments') }}"
    >
        <span id="telemetry-panel-toggle-icon">{{ __('ui.hide_instruments') }}</span>
    </button>

    <button
        type="button"
        id="telemetry-panel-detach"
        data-label-dock="{{ __('ui.dock') }}"
        data-label-undock="{{ __('ui.undock') }}"
        data-title-dock="{{ __('ui.dock_instruments') }}"
        data-title-undock="{{ __('ui.undock_instruments') }}"
        class="rounded-lg border border-[var(--border)] px-2 py-2 text-sm"
        aria-pressed="false"
        title="{{ __('ui.undock_instruments_on_map') }}"
    >
        {{ __('ui.undock') }}
    </button>
</div>
