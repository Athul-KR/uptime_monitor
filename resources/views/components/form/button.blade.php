<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'inline-flex w-full items-center justify-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-500 focus-visible:outline-hidden focus-visible:ring-4 focus-visible:ring-emerald-500/30 active:bg-emerald-700',
]) }}>
    {{ $slot }}
</button>
