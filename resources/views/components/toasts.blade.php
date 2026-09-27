<div
    x-data="{
        showSuccess: {{ session('success') ? 'true' : 'false' }},
        showError: {{ session('error') ? 'true' : 'false' }}
    }"
    class="fixed top-6 right-6 z-50 space-y-3 w-80">

    {{-- SUCCESS --}}
    @if(session('success'))
    <div
        x-show="showSuccess"
        x-init="setTimeout(() => showSuccess = false, 4000)"
        x-transition
        class="bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg">

        {{ session('success') }}

    </div>
    @endif


    {{-- ERROR --}}
    @if(session('error'))
    <div
        x-show="showError"
        x-init="setTimeout(() => showError = false, 5000)"
        x-transition
        class="bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg">

        {{ session('error') }}

    </div>
    @endif


    {{-- VALIDATION --}}
    @if ($errors->any())
    <div
        x-data="{ show:true }"
        x-show="show"
        x-init="setTimeout(() => show = false, 6000)"
        x-transition
        class="bg-red-700 text-white px-4 py-3 rounded-lg shadow-lg">

        <ul class="list-disc ml-4 text-sm">

            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach

        </ul>

    </div>
    @endif

</div>
