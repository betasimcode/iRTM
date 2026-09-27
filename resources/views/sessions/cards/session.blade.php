

<a href="{{ route('sessions.show', $session->id) }}"
   class="block bg-[var(--card)] text-[var(--text)] border-[var(--b-card)] hover:bg-[var(--card-hover)] [box-shadow:var(--card-shadow)] border w mb-12 px-6 rounded-md p-2 transition">

    {{-- HEADER --}}
    <div class="ml-0 grid-flow-col flex items-center gap-4">

        <table class="m-0"><thead>
            <tr>
              <td class="w-28 px-2">
                {{-- LOGO --}}
        @if($session->series?->logo_path)
            <img src="{{ asset('storage/'.$session->series->logo_path) }}"
                class="h-28 w-auto object-contain">
        @else
            <div class="h-16 w-auto bg-gray-700 rounded"></div>
        @endif
        @php
        $firstStint = $session->stints->first();
        @endphp

              </td>

              <td class="w-96 px-2">

                        {{-- TITULO --}}
        <div class="flex-1">

            <div class="text-[var(--card-title)] font-semibold text-md leading-tight uppercase">
                {{ $session->series?->short_name ?? 'Unknown Series' }}
            </div>

            <div class="text-[var(--text-soft)] text-sm">
                {{ \Carbon\Carbon::parse($session->created_at)->format('d M H:i') }}
            </div>

        </div>

              </td>
              <td class=" w-96 px-2">

                <div class=" flex-auto">
                    <img src="{{ asset('storage/' . themedLogo($firstStint->track)) }}" class=" h-24 w-auto object-contain">
                </div>
              </td>

            </tr></thead>
          </table>

    </div>

    {{-- FOOTER --}}
    <div class="grid-flow-col grid-cols-2 mt-4 justify-between items-center text-sm">

        {{-- BADGES --}}
        <div class="flex gap-3">
            <span class="w-24 h-5 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] border border-[var(--badge-b)] rounded text-xs uppercase">
                {{ $session->iracing_subsession_id ?? 'Unknown Series' }}
            </span>
            <span class="w-56 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] border border-[var(--badge-b)] rounded text-xs uppercase">
                {{ $firstStint->car_name }}
            </span>
            <span class=" w-7/12 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] border border-[var(--badge-b)] rounded text-xs uppercase">
                {{ $firstStint->track->display_name }}
            </span>
            {{-- SETUP --}}
                    @if($session->series)
                    @if($session->series->setup_type === "open")
            <span title="OPEN setup" class="w-28 text-center px-2 rounded text-xs bg-[var(--A)] border border-[var(--badge-b)] text-yellow-200">
                    PRO
            </span>
                    @else
            <span title="FIXED setup" class="w-28 text-center px-2 rounded text-xs bg-[var(--danger)] border border-[var(--badge-b)] text-[var(--value-info)]">
                    AMATEUR
                    @endif
                </span>
            @endif

            {{-- LICENSE --}}
            @if($session->series?->iracing_class)
                @if($session->series?->iracing_class ==='D')
                    <span class="w-10 text-center bg-[var(--D)] border border-[var(--badge-b)] text-white rounded text-xs">
                        {{ $session->series?->iracing_class }}
                    </span>
                @elseif($session->series?->iracing_class ==='C')
                    <span class="w-8 text-center bg-[var(--C)] border border-[var(--badge-b)] text-gray-900 rounded text-xs">
                        {{ $session->series?->iracing_class }}
                    </span>
                @elseif($session->series?->iracing_class ==='B')
                    <span class="w-8 text-center bg-[var(--B)] border border-[var(--badge-b)] text-white rounded text-xs">
                        {{ $session->series?->iracing_class }}
                    </span>
                @elseif($session->series?->iracing_class ==='A')
                    <span class="w-8 text-center bg-[var(--A)] border border-[var(--badge-b)] text-white rounded text-xs">
                        {{ $session->series?->iracing_class }}
                    </span>
                @else($session->series?->iracing_class ==='P')
                    <span class="w-8 text-center bg-[var(--P)] border border-[var(--badge-b)] text-white rounded text-xs">
                        {{ $session->series?->iracing_class }}
                </span>
                @endif
            @endif



            {{-- STINTS --}}
            <div class="w-28 text-center px-2 bg-[var(--badge)] text-[var(--badge-text)] border border-[var(--badge-b)] rounded text-xs uppercase">
                {{ $session->stints_count }} stints
            </div>
        </div>

    </div>
</a>

