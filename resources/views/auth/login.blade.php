<x-layouts.auth title="Log in" heading="Welcome back" subheading="Log in to check on your monitors.">
    @session('status')
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ $value }}
        </div>
    @endsession

    <form method="POST" action="{{ route('login') }}" class="grid gap-5">
        @csrf

        <x-form.input name="email" type="email" label="Email address" placeholder="you@company.com" autocomplete="email" required autofocus />

        <x-form.input name="password" type="password" label="Password" placeholder="••••••••" autocomplete="current-password" required />

        <label class="flex items-center gap-2.5 text-sm text-zinc-600 dark:text-zinc-400">
            <input type="checkbox" name="remember" class="size-4 rounded accent-emerald-600" @checked(old('remember'))>
            Remember me
        </label>

        <x-form.button class="mt-1">Log in</x-form.button>
    </form>

    <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Don't have an account?
        <a href="{{ route('register') }}" class="font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">Create one</a>
    </p>
</x-layouts.auth>
