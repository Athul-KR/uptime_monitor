@props(['title', 'heading', 'subheading'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => $title])
    </head>
    <body class="min-h-screen bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <div class="grid min-h-screen lg:grid-cols-2">
            <main class="flex flex-col px-6 py-8 sm:px-12 lg:px-16">
                <a href="{{ url('/') }}" class="self-start">
                    <x-app-logo />
                </a>

                <div class="flex flex-1 items-center justify-center py-12">
                    <div class="w-full max-w-sm">
                        <h1 class="text-3xl font-semibold tracking-tight">{{ $heading }}</h1>
                        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $subheading }}</p>

                        <div class="mt-8">
                            {{ $slot }}
                        </div>
                    </div>
                </div>

                <p class="text-xs text-zinc-400 dark:text-zinc-500">&copy; {{ date('Y') }} Uptime Monitor</p>
            </main>

            <aside class="relative hidden flex-col justify-between overflow-hidden bg-zinc-950 p-12 text-white lg:flex xl:p-16">
                <div aria-hidden="true" class="pointer-events-none absolute inset-0">
                    <div class="absolute -top-40 -right-32 size-[36rem] rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute -bottom-48 -left-24 size-[28rem] rounded-full bg-teal-400/10 blur-3xl"></div>
                    <div class="absolute inset-0 [background-size:40px_40px] [mask-image:radial-gradient(ellipse_at_center,black_35%,transparent_75%)] bg-[linear-gradient(to_right,rgb(255_255_255/0.05)_1px,transparent_1px),linear-gradient(to_bottom,rgb(255_255_255/0.05)_1px,transparent_1px)]"></div>
                </div>

                <div class="relative inline-flex items-center gap-2.5 self-start rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-xs font-medium text-emerald-300">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span>
                    </span>
                    All systems operational
                </div>

                <div class="relative">
                    <h2 class="max-w-md text-4xl font-semibold tracking-tight text-balance">Know the moment your site goes down.</h2>
                    <p class="mt-4 max-w-md text-zinc-400">Round-the-clock checks on your websites and APIs, with alerts that reach you before your customers do.</p>

                    <div class="mt-10 rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur-md">
                        <div class="flex items-center justify-between text-xs text-zinc-400">
                            <span class="font-medium tracking-wider uppercase">Last 45 days</span>
                            <span class="tabular-nums">Avg. response 168 ms</span>
                        </div>

                        <ul class="mt-5 grid gap-5">
                            @foreach ([
                                ['host' => 'acme.com', 'uptime' => '100%', 'incidents' => []],
                                ['host' => 'api.acme.com', 'uptime' => '99.98%', 'incidents' => [17 => 'bg-amber-400']],
                                ['host' => 'checkout.acme.com', 'uptime' => '99.71%', 'incidents' => [8 => 'bg-amber-400', 33 => 'bg-rose-500', 34 => 'bg-amber-400']],
                            ] as $monitor)
                                <li>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="flex items-center gap-2 font-medium">
                                            <span class="size-1.5 rounded-full bg-emerald-400"></span>
                                            {{ $monitor['host'] }}
                                        </span>
                                        <span class="text-zinc-400 tabular-nums">{{ $monitor['uptime'] }}</span>
                                    </div>

                                    <div class="mt-2 flex gap-[3px]">
                                        @foreach (range(1, 45) as $day)
                                            <span class="h-7 flex-1 rounded-[2px] {{ $monitor['incidents'][$day] ?? 'bg-emerald-400/80' }}"></span>
                                        @endforeach
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <p class="relative text-sm text-zinc-500">Trusted to watch over every deploy, day and night.</p>
            </aside>
        </div>
    </body>
</html>
