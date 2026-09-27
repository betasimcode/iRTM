@extends('layouts.app')
@section('title', 'Series')
@section('page-title', 'Registered iRacing Series')
@section('content')




@if(session('success'))
    <div class="mb-6 px-4 py-3 rounded-lg bg-emerald-600/20 border border-emerald-600 text-emerald-400 text-sm">
        {{ session('success') }}
    </div>
@endif


<div class="bg-[var(--bg)] border-[var(--border)] rounded-xl shadow overflow-hidden">
    <div class="flex m-10 justify-between items-center mb-2">
        <a href="{{ route('admin.iracing-series.create') }}"
           class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700
                  text-white text-sm font-medium rounded-lg transition shadow">
            {{ __('ui.add') }}
        </a>
    </div>
        <table class="min-w-full text-m ">
            <div class="overflow-x-auto">
            <thead class="bg-[var(--card)] border-[var(--border)] text-[var(--text-title)] uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">ID</th>
                    <th class="px-6 py-3 text-left">Logo</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.name') }}</th>
                    <th class="px-6 py-3 text-left">iRacing ID</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.class') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.category') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.start') }}</th>
                    <th class="px-6 py-3 text-left">{{ __('ui.duration') }}</th>
                    <th class="px-6 py-3 text-right">{{ __('ui.actions') }}</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-[var(--div)] align-middle ">

                @foreach($series as $s)
                <tr class="hover:bg-[var(--card-hover)] text-[var(--text)] transition">

                    <td class="w-3 px-6 py-4 text-xs">
                        {{ $s->id }}
                    </td>
                    <td class="px-4 py-2 w-32">

                    @if($s->logo_path)
                    <a href="{{$s->ir_url}}" target="_blank">
                    <img src="{{ asset('storage/'.$s->logo_path) }}" class="h-9 w-auto text-center align-middle">
                    </a>
                    @endif

                    </td>


                    <td class="px-6 py-4 text[var(--text)] text-xs w-96 uppercase">
                        {{ $s->name }}
                    </td>
                    <td class="px-6 py-4 text[var(--text)] text-xs w-96 uppercase">
                        {{ $s->iracing_series_id }}
                    </td>
                    <td class="w-10 px-6 py-4 text[var(--text)] text-xs">
                        {{ $s->iracing_class }}
                    </td>
                    <td class="px-6 py-4 text[var(--text)] text-xs w-32">
                        {{ $s->category ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text[var(--text)] text-xs w-32">
                        {{ $s->start_type ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text[var(--text)] text-xs w-72">
                        @if ($s->race_type == 'Time')
                        {{ $s->race_length ?? '—' }} - Laps
                        @else
                        {{ $s->race_length ?? '—' }} - Mins
                        @endif
                    </td>


                    <td class="flex px-6 pt-4 text-right space-x-4">

                        <a href="{{ route('admin.iracing-series.edit',$s) }}"
                        class="text-[var(--text-soft)] hover:text-[var(--warning)] font-xs transition">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                         <g id="SVGRepo_bgCarrier" stroke-width="0"/>

                         <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>

                         <g id="SVGRepo_iconCarrier"> <path d="M11 4.00023H6.8C5.11984 4.00023 4.27976 4.00023 3.63803 4.32721C3.07354 4.61483 2.6146 5.07377 2.32698 5.63826C2 6.27999 2 7.12007 2 8.80023V17.2002C2 18.8804 2 19.7205 2.32698 20.3622C2.6146 20.9267 3.07354 21.3856 3.63803 21.6732C4.27976 22.0002 5.11984 22.0002 6.8 22.0002H15.2C16.8802 22.0002 17.7202 22.0002 18.362 21.6732C18.9265 21.3856 19.3854 20.9267 19.673 20.3622C20 19.7205 20 18.8804 20 17.2002V13.0002M7.99997 16.0002H9.67452C10.1637 16.0002 10.4083 16.0002 10.6385 15.945C10.8425 15.896 11.0376 15.8152 11.2166 15.7055C11.4184 15.5818 11.5914 15.4089 11.9373 15.063L21.5 5.50023C22.3284 4.6718 22.3284 3.32865 21.5 2.50023C20.6716 1.6718 19.3284 1.6718 18.5 2.50022L8.93723 12.063C8.59133 12.4089 8.41838 12.5818 8.29469 12.7837C8.18504 12.9626 8.10423 13.1577 8.05523 13.3618C7.99997 13.5919 7.99997 13.8365 7.99997 14.3257V16.0002Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </g>

                         </svg>
                        </a>
                    {{-- </td>
                    <td class="w-3 text-center space-x-4 px-5">     --}}
                        <form method="POST"
                        action="{{ route('admin.iracing-series.destroy',$s) }}"
                        class="inline">
                            @csrf
                            @method('DELETE')

                            <button
                                class="text-[var(--text-soft)] hover:text-[var(--danger)] font-medium transition"
                                onclick="return confirm('¿Seguro que quieres eliminar esta serie?')">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">

                                    <g id="SVGRepo_bgCarrier" stroke-width="0"/>

                                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"/>

                                    <g id="SVGRepo_iconCarrier"> <path d="M16 6V5.2C16 4.0799 16 3.51984 15.782 3.09202C15.5903 2.71569 15.2843 2.40973 14.908 2.21799C14.4802 2 13.9201 2 12.8 2H11.2C10.0799 2 9.51984 2 9.09202 2.21799C8.71569 2.40973 8.40973 2.71569 8.21799 3.09202C8 3.51984 8 4.0799 8 5.2V6M10 11.5V16.5M14 11.5V16.5M3 6H21M19 6V17.2C19 18.8802 19 19.7202 18.673 20.362C18.3854 20.9265 17.9265 21.3854 17.362 21.673C16.7202 22 15.8802 22 14.2 22H9.8C8.11984 22 7.27976 22 6.63803 21.673C6.07354 21.3854 5.6146 20.9265 5.32698 20.362C5 19.7202 5 18.8802 5 17.2V6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/> </g>

                                    </svg>
                            </button>
                        </form>

                    </td>

                </tr>
                @endforeach

            </tbody>
<div class="mt-6">
{{ $series->links() }}
</div>

        </table>

    </div>

</div>

@endsection
