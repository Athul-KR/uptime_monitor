<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => 'Dashboard'])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <x-app-logo />

                <div class="flex items-center gap-4 text-sm">
                    <span class="text-zinc-500 dark:text-zinc-400">{{ auth()->user()->name }}</span>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg border border-zinc-300 px-3 py-1.5 font-medium transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800">Log out</button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-12">
            <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>

            <div class="mt-8 rounded-2xl border border-dashed border-zinc-300 bg-white p-12 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <p class="font-medium">No monitors yet</p>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">Monitors you add will show up here.</p>
            </div>
        </main>
    </body>
</html>
