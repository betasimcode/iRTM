@php
    use App\Helpers\TelemetryHelper;
@endphp

@php
    $isRight = in_array($position, ['fr', 'rr']);
@endphp

@php
    $order = $isRight
        ? ['inner', 'middle', 'outer'] // 👉 invertido
        : ['outer', 'middle', 'inner']; // 👉 normal
@endphp

@props(['data', 'wear', 'title', 'position'])


<div class="bg-gray-700 border rounded-md border-gray-700 p-2 shadow-sm">

    <div class="text-center text-xs uppercase bg-blue-600 text-white rounded mb-2">
        
    </div>

    <div class="grid grid-cols-3 gap-1 text-center text-xs">

        @foreach($order as $pos)

    @php
        $t = $data[$pos] ?? null;

        $color = 'bg-green-500 text-black';

        if ($t > 100) {
            $color = 'bg-red-500 text-white';
        } elseif ($t > 90) {
            $color = 'bg-yellow-400 text-black';
        } elseif ($t < 70) {
            $color = 'bg-blue-400 text-white';
        }
    @endphp

@php
    $w = $wear[$pos] ?? 1;

    // clamp seguridad
    $w = max(0, min(1, $w));

    // interpolación gris
    $gray = intval(19 + (1 - $w) * (128 - 19)); 
    // 19 = #13, 128 = #80

    $wearColor = "rgb($gray, $gray, $gray)";
@endphp

    <div class="p-2 rounded {{ $color }}">
        <div class="text-[9px] uppercase opacity-70">
            {{ $pos }}
        </div>

        <div class="text-sm font-bold">
            {{ $t }}°
        </div>
    </div>
    
@endforeach

    </div>
    {{-- <div class="mt-1 h-6 rounded text-center text-gray-100"
    style="background-color: {{ $wearColor }}"> {{ $w }}
    </div> --}}
    <div class="mt-1 h-5 bg-gray-600 rounded overflow-hidden text-sm text-center text-yellow-200">
        <div 
            class="h-full"
            style="
                width: {{ $w * 100 }}%;
                background-color: {{ $wearColor }};
            ">
            {{ $w }}
        </div>
    </div>
</div>