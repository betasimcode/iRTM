<div
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
    style="display:none">

    <div
        @click.outside="open=false"
        class="w-full max-w-md rounded-lg bg-[var(--card)] shadow-xl">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-3 border-b border-[var(--b-card)]">

            <h2 class="text-lg font-semibold text-[var(--text-card-title)]">

                {{ $title }}

            </h2>

            <button
                @click="open=false"
                class="text-[var(--text-card-title)] hover:text-[var(--btn-hover)]">

                ✕

            </button>

        </div>

        {{-- Body --}}
        <div class="p-4">

            {{ $slot }}

        </div>

    </div>

</div>
