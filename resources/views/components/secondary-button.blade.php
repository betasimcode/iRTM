<button {{ $attributes->merge([
'class' => 'inline-flex items-center px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg font-semibold text-xs text-gray-200 uppercase tracking-widest hover:bg-gray-600 transition'
]) }}>
    {{ $slot }}
</button>
