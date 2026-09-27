@props([
    'title',
    'items',
    'type' => null,
])

@php
$tooltipKeyMap = [
    'camber' => 'camber',
    'pressure' => 'pressure',
    'ride height' => 'ride_height',
    'spring rate' => 'spring_rate',
    'toe' => 'toe',
    'corner weight' => 'corner_weight',
    'brake bias' => 'brake_bias',
    'arb' => 'arb',
    'balance' => 'balance'
];
@endphp

@php
$valueColors = [
    'tyre' => 'text-[var(--value-tyre)]',       // oscuro → verde brillante
    'susp' => 'text-[var(--value-susp)]',        // claro → oscuro
    'chassis' => 'text-[var(--value-chassis)]',     // coherente con borde azul
    'aero' => 'text-[var(--value-aero)]',
    'drive' => 'text-[var(--value-drive)]',
    'config' => 'text-[var(--value-config)]',
    'telemetry' => 'text-[var(--value-telemetry)]',
    'default' => 'text-gray-800',
];
@endphp

@php
$styles = [
    'tyre' => 'bg-[var(--bg-tyre)] text-[var(--text-tyre)] rounded-lg border border-[var(--border)]',
    'susp' => 'bg-[var(--bg-susp)] text-[var(--text-susp)] border border-gray-500 text-[var(--text-susp)]',
    'chassis' => 'bg-[var(--bg-chassis)] text-[var(--text-chassis)] border border-blue-700',
    'aero' => 'bg-[var(--bg-aero)] text-[var(--text-aero)] rounded-xl border border-blue-300',
    'drive' => 'bg-[var(--bg-drive)] text-[var(--text-drive)] border border-blue-700',
    'config' => 'bg-[var(--bg-config)] text-[var(--text-config)] border border-blue-500',
    'default' => 'bg-gray-100 border border-gray-300',
    'telemetry' => 'bg-[var(--bg-telemetry)] border border-[var(--border)] text-[var(--text-telemetry)]'
];



$class = $styles[$type] ?? $styles['default'];
@endphp
@php
    $valueClass = $valueColors[$type] ?? $valueColors['default'];
@endphp


@if(!empty($items))
<div
    x-data
    x-show="view === 'all' || view === @js($type)">
    <div class="p-3 {{ $class }}">

        <h4 class="mb-2 text-center text-[var(--text-model)] bg-[var(--header)] rounded-md text-xs uppercase">
            {{ $title }}
        </h4>

        <table class="w-full text-xs uppercase">
            @foreach($items as $item)

            @php
                $label = strtolower($item->mapped_label ?? '');
                $tooltipKey = null;

                foreach ($tooltipKeyMap as $match => $key) {
                    if (str_contains($label, $match)) {
                        $tooltipKey = $key;
                        break;
                    }
                }
            @endphp

                <tr class="">
                    <td class="text-left">

                        <div class="flex items-center gap-1 group">

                            <span>
                                {{ $item->mapped_label }}
                            </span>

                            @if($tooltipKey)
                            <div class="relative inline-block">

                                <span class="text-xs text-gray-400 hover:text-blue-500 cursor-help">
                                    <svg width="15" height="15" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <rect width="48" height="48" fill="white" fill-opacity="0.01"></rect> <path d="M24 44C29.5228 44 34.5228 41.7614 38.1421 38.1421C41.7614 34.5228 44 29.5228 44 24C44 18.4772 41.7614 13.4772 38.1421 9.85786C34.5228 6.23858 29.5228 4 24 4C18.4772 4 13.4772 6.23858 9.85786 9.85786C6.23858 13.4772 4 18.4772 4 24C4 29.5228 6.23858 34.5228 9.85786 38.1421C13.4772 41.7614 18.4772 44 24 44Z" fill="#2F88FF" stroke="#000000" stroke-width="4" stroke-linejoin="round"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M24 11C25.3807 11 26.5 12.1193 26.5 13.5C26.5 14.8807 25.3807 16 24 16C22.6193 16 21.5 14.8807 21.5 13.5C21.5 12.1193 22.6193 11 24 11Z" fill="white"></path> <path d="M24.5 34V20H23.5H22.5" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M21 34H28" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
                                </span>

                                <div class="
                                    absolute left-1/2 -translate-x-1/2 bottom-full mb-1
                                    opacity-0 invisible
                                    group-hover:opacity-100 group-hover:visible
                                    transition-all duration-150
                                    bg-gray-900 text-white text-xs px-2 py-1
                                    rounded shadow-lg z-50 whitespace-nowrap
                                ">
                                    {{ __('tooltips.' . $tooltipKey) }}
                                </div>

                            </div>
                            @endif

                        </div>

                    </td>

                    <td class="text-right font-bold {{ $valueClass }}">
                        {{ $item->value }}
                    </td>
                </tr>
            @endforeach
        </table>

    </div>
</div>
@endif
