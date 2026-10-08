<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        @include('partials.head', ['title' => 'Website uptime monitoring'])
    </head>
    <body class="bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="relative overflow-hidden bg-zinc-950 text-white">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                <div class="absolute -top-48 right-0 size-[40rem] rounded-full bg-emerald-500/20 blur-3xl"></div>
                <div class="absolute -bottom-64 -left-32 size-[32rem] rounded-full bg-teal-400/10 blur-3xl"></div>
                <div class="absolute inset-0 [background-size:40px_40px] [mask-image:radial-gradient(ellipse_at_center,black_35%,transparent_75%)] bg-[linear-gradient(to_right,rgb(255_255_255/0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.05)_1px,transparent_1px)]"></div>
            </div>

            <header class="relative mx-auto flex max-w-6xl items-center justify-between gap-4 px-6 py-6">
                <a href="{{ url('/') }}">
                    <x-app-logo />
                </a>

                <nav class="hidden items-center gap-8 text-sm text-zinc-300 md:flex">
                    <a href="#features" class="transition hover:text-white">Features</a>
                    <a href="#how-it-works" class="transition hover:text-white">How it works</a>
                </nav>

                <div class="flex items-center gap-5 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-zinc-950 transition hover:bg-emerald-400">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden font-medium text-zinc-300 transition hover:text-white sm:inline">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-emerald-500 px-4 py-2 font-semibold text-zinc-950 transition hover:bg-emerald-400">Get started</a>
                    @endauth
                </div>
            </header>

            <section class="relative mx-auto grid max-w-6xl items-center gap-16 px-6 pt-16 pb-24 lg:grid-cols-2 lg:pt-24 lg:pb-32">
                <div>
                    <span class="inline-flex items-center gap-2.5 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-300">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span>
                        </span>
                        Uptime monitoring for websites and APIs
                    </span>

                    <h1 class="mt-6 text-5xl font-semibold tracking-tight text-balance sm:text-6xl">Know the moment your site goes down.</h1>

                    <p class="mt-6 max-w-lg text-lg text-zinc-400">Round-the-clock checks on your websites and APIs, with alerts that reach you before your customers do.</p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-zinc-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">Go to your dashboard</a>
                        @else
                            <a href="{{ route('register') }}" class="rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-zinc-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400">Start monitoring for free</a>
                            <a href="{{ route('login') }}" class="rounded-lg border border-white/15 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/5">Log in</a>
                        @endauth
                    </div>
                </div>

                <x-uptime-preview />
            </section>
        </div>

        <section id="features" class="mx-auto max-w-6xl px-6 py-24">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">Features</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Everything you need to keep your sites online</h2>
                <p class="mt-4 text-zinc-600 dark:text-zinc-400">Simple monitoring that tells you what's wrong, when it started, and when it's fixed.</p>
            </div>

            <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    [
                        'title' => 'Uptime checks',
                        'description' => 'We request your websites and APIs around the clock and record every response, so you know they are really reachable.',
                        'icon' => '<circle cx="12" cy="12" r="10" /><path d="M2 12h20" /><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />',
                    ],
                    [
                        'title' => 'Instant alerts',
                        'description' => 'Get an email the moment a site stops responding, and another one as soon as it is back up.',
                        'icon' => '<path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />',
                    ],
                    [
                        'title' => 'Response times',
                        'description' => 'Track how fast each site responds and spot slowdowns before they turn into outages.',
                        'icon' => '<path d="M22 12h-4l-3 9L9 3l-3 9H2" />',
                    ],
                    [
                        'title' => 'SSL certificate checks',
                        'description' => 'Get a heads-up before a certificate expires and visitors start seeing browser warnings.',
                        'icon' => '<rect x="3" y="11" width="18" height="11" rx="2" /><path d="M7 11V7a5 5 0 0 1 10 0v4" />',
                    ],
                    [
                        'title' => 'Uptime history',
                        'description' => 'See uptime percentages and past incidents for every monitor at a glance.',
                        'icon' => '<path d="M3 3v18h18" /><path d="M7 16v-5" /><path d="M12 16V8" /><path d="M17 16v-3" />',
                    ],
                    [
                        'title' => 'Secure accounts',
                        'description' => 'Hashed passwords, rate-limited logins and secure password resets keep your account safe.',
                        'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" /><path d="m9 12 2 2 4-4" />',
                    ],
                ] as $feature)
                    <div class="rounded-2xl border border-zinc-200 p-6 transition hover:border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/5 dark:border-zinc-800 dark:hover:border-emerald-500/40">
                        <span class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5" aria-hidden="true">
                                {!! $feature['icon'] !!}
                            </svg>
                        </span>
                        <h3 class="mt-5 font-semibold">{{ $feature['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $feature['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="how-it-works" class="border-y border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900/50">
            <div class="mx-auto max-w-6xl px-6 py-24">
                <div class="max-w-2xl">
                    <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">How it works</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Up and running in three steps</h2>
                </div>

                <ol class="mt-14 grid gap-10 md:grid-cols-3">
                    @foreach ([
                        ['title' => 'Add your website', 'description' => 'Paste the URL of any website or API endpoint you want to keep an eye on.'],
                        ['title' => 'We keep checking', 'description' => 'Your monitor runs on a schedule and records the status code and response time of every check.'],
                        ['title' => 'Get alerted', 'description' => 'If something breaks, you hear about it right away, not from your customers.'],
                    ] as $step)
                        <li>
                            <span class="flex size-10 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">{{ $loop->iteration }}</span>
                            <h3 class="mt-5 font-semibold">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">{{ $step['description'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>

        <section class="mx-auto max-w-6xl px-6 py-24">
            <div class="relative overflow-hidden rounded-3xl bg-zinc-950 px-8 py-16 text-center text-white sm:px-16">
                <div aria-hidden="true" class="pointer-events-none absolute -top-32 left-1/2 size-[36rem] -translate-x-1/2 rounded-full bg-emerald-500/20 blur-3xl"></div>

                <h2 class="relative text-3xl font-semibold tracking-tight sm:text-4xl">Start monitoring in under a minute</h2>
                <p class="relative mx-auto mt-4 max-w-xl text-zinc-400">Create a free account, add your first website, and let us keep watch day and night.</p>

                <div class="relative mt-8">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-400">Go to your dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="inline-flex rounded-lg bg-emerald-500 px-5 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-emerald-400">Create your free account</a>
                    @endauth
                </div>
            </div>
        </section>

        <footer class="border-t border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-6 py-8 sm:flex-row">
                <x-app-logo />
                <p class="text-sm text-zinc-500 dark:text-zinc-400">&copy; {{ date('Y') }} Uptime Monitor. All rights reserved.</p>
            </div>
        </footer>
    </body>
</html>
