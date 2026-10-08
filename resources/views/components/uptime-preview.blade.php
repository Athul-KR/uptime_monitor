<div {{ $attributes->class('rounded-2xl border border-white/10 bg-white/5 p-6 text-white shadow-2xl backdrop-blur-md') }}>
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
