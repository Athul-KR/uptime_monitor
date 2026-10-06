<x-layouts.auth title="Create account" heading="Create your account" subheading="Start monitoring your sites in under a minute.">
    <form method="POST" action="{{ route('register') }}" class="grid gap-5">
        @csrf

        <x-form.input name="name" label="Full name" placeholder="Jane Doe" autocomplete="name" required autofocus />

        <x-form.input name="email" type="email" label="Email address" placeholder="you@company.com" autocomplete="email" required />

        <x-form.input name="password" type="password" label="Password" placeholder="At least 8 characters" autocomplete="new-password" required />

        <x-form.input name="password_confirmation" type="password" label="Confirm password" placeholder="Repeat your password" autocomplete="new-password" required />

        <x-form.button class="mt-1">Create account</x-form.button>
    </form>

    <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Already have an account?
        <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-500 dark:text-emerald-400">Log in</a>
    </p>
</x-layouts.auth>
