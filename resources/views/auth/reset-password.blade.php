<x-layouts.auth title="Reset password" heading="Set a new password" subheading="Choose a strong password you don't use anywhere else.">
    <form method="POST" action="{{ route('password.update') }}" class="grid gap-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <x-form.input name="email" type="email" label="Email address" :value="$request->email" autocomplete="email" required />

        <x-form.input name="password" type="password" label="New password" placeholder="At least 8 characters" autocomplete="new-password" required autofocus />

        <x-form.input name="password_confirmation" type="password" label="Confirm new password" placeholder="Repeat your password" autocomplete="new-password" required />

        <x-form.button class="mt-1">Reset password</x-form.button>
    </form>
</x-layouts.auth>
