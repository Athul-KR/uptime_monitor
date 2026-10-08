@props(['name', 'label', 'type' => 'text', 'value' => null])

<div class="grid gap-1.5">
    <label for="{{ $name }}" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
        @error($name) aria-invalid="true" aria-describedby="{{ $name }}-error" @enderror
        {{ $attributes->class([
            'block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-zinc-900 shadow-xs outline-hidden transition placeholder:text-zinc-400 focus:ring-4 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500',
            'border-zinc-300 focus:border-emerald-500 focus:ring-emerald-500/15 dark:border-zinc-700' => ! $errors->has($name),
            'border-rose-400 focus:border-rose-500 focus:ring-rose-500/15 dark:border-rose-500/60' => $errors->has($name),
        ]) }}
    >

    @error($name)
        <p id="{{ $name }}-error" class="text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
</div>
