<button {{ $attributes->merge([
'class' => 'inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition'
]) }}>
    {{ $slot }}
</button>
