<x-layouts.auth title="Forgot password" heading="Forgot your password?" subheading="Enter your email and we'll send you a link to reset it.">
    @session('status')
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300">
            {{ $value }}
        </div>
    @endsession

    <form method="POST" action="{{ route('password.email') }}" class="grid gap-5">
        @csrf

        <x-form.input name="email" type="email" label="Email address" placeholder="you@company.com" autocomplete="email" required autofocus />

        <x-form.button class="mt-1">Email reset link</x-form.button>
    </form>

    <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Remembered it?
        <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">Back to log in</a>
    </p>
</x-layouts.auth>
