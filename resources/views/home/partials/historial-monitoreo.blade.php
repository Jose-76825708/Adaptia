<div id="historial-monitoreo"
     data-monitoring-poll-url="{{ route('cliente.monitoreo.historial') }}"
     data-monitoring-poll-interval="30000">
    @if ($sensoresAsignados->isEmpty())
        <section class="rounded-3xl border border-[#e4ebdb] bg-white p-8 text-center shadow-sm">
            <h2 class="text-xl font-bold text-[#0f3c2b]">Aún no tienes sensores asignados</h2>
            <p class="mt-2 text-sm text-[#718071]">Cuando se asigne un sensor a una de tus plantas, aquí aparecerán sus lecturas y alertas.</p>
        </section>
    @else
        @foreach ($sensoresAsignados as $plantaVendida)
            @php
                $planta = $plantaVendida->venta->planta;
                $lecturas = $plantaVendida->lecturasSensores->sortBy('fecha_hora')->values();
                $alertas = $plantaVendida->alertas;
            @endphp
            <section class="space-y-6">
                <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">#{{ $plantaVendida->sensor->identificador_fisico }}</p>
                        <h2 class="mt-1 text-2xl font-bold tracking-tight text-[#0f3c2b]">{{ $planta->nombre ?? 'Planta no disponible' }}</h2>
                    </div>
                    <a href="{{ route('home') }}#mis-sensores" class="font-semibold text-[#608d2e] hover:underline">Ver sensor en inicio</a>
                </div>

                @if ($lecturas->isEmpty())
                    <div class="rounded-2xl border border-dashed border-[#dce9d0] bg-white p-5 text-sm text-[#718071]">
                        Aún no hay lecturas disponibles para este sensor.
                    </div>
                @else
                    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
                        @foreach ([
                            ['key' => 'humedad_suelo', 'label' => 'Humedad del suelo', 'unit' => '%', 'min' => 'humedad_suelo_min', 'max' => 'humedad_suelo_max'],
                            ['key' => 'temperatura', 'label' => 'Temperatura', 'unit' => '°C', 'min' => 'temperatura_min', 'max' => 'temperatura_max'],
                            ['key' => 'humedad_ambiental', 'label' => 'Humedad ambiental', 'unit' => '%', 'min' => 'humedad_ambiental_min', 'max' => 'humedad_ambiental_max'],
                            ['key' => 'luz', 'label' => 'Iluminación', 'unit' => 'lux', 'min' => 'luz_min', 'max' => 'luz_max'],
                        ] as $grafico)
                            @php
                                $valores = $lecturas->map(fn ($lectura) => (float) $lectura->{$grafico['key']});
                                $minimoValor = $valores->min();
                                $maximoValor = $valores->max();
                                $amplitud = max($maximoValor - $minimoValor, 1);
                                $puntos = $lecturas->map(function ($lectura, $indice) use ($lecturas, $grafico, $minimoValor, $amplitud) {
                                    $x = $lecturas->count() === 1 ? 320 : 30 + ($indice * 580 / ($lecturas->count() - 1));
                                    $y = 170 - (((float) $lectura->{$grafico['key']} - $minimoValor) / $amplitud * 130);

                                    return ['x' => round($x, 2), 'y' => round($y, 2), 'valor' => (float) $lectura->{$grafico['key']}, 'fecha' => $lectura->fecha_hora];
                                });
                                $polilinea = $puntos->map(fn ($punto) => $punto['x'] . ',' . $punto['y'])->implode(' ');
                                $rangoMinimo = $planta?->{$grafico['min']};
                                $rangoMaximo = $planta?->{$grafico['max']};
                            @endphp
                            <article class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="font-bold text-[#0f3c2b]">{{ $grafico['label'] }}</h3>
                                        <p class="mt-1 text-xs text-[#718071]">
                                            @if ($rangoMinimo !== null && $rangoMaximo !== null)
                                                Rango de referencia: {{ rtrim(rtrim(number_format((float) $rangoMinimo, 2), '0'), '.') }}–{{ rtrim(rtrim(number_format((float) $rangoMaximo, 2), '0'), '.') }} {{ $grafico['unit'] }}
                                            @else
                                                Rango de referencia no configurado
                                            @endif
                                        </p>
                                    </div>
                                    <span class="rounded-full bg-[#eff6e8] px-3 py-1 text-xs font-bold text-[#52752d]">{{ $lecturas->count() }} lecturas</span>
                                </div>
                                <svg class="mt-5 h-48 w-full" viewBox="0 0 640 200" role="img" aria-label="Tendencia histórica de {{ strtolower($grafico['label']) }}">
                                    <line x1="30" y1="40" x2="610" y2="40" stroke="#e8eee3" stroke-width="1" />
                                    <line x1="30" y1="105" x2="610" y2="105" stroke="#e8eee3" stroke-width="1" />
                                    <line x1="30" y1="170" x2="610" y2="170" stroke="#e8eee3" stroke-width="1" />
                                    @if ($puntos->count() > 1)
                                        <polyline points="{{ $polilinea }}" fill="none" stroke="#629f22" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                                    @endif
                                    @foreach ($puntos as $punto)
                                        <circle cx="{{ $punto['x'] }}" cy="{{ $punto['y'] }}" r="5" fill="#629f22" stroke="white" stroke-width="2">
                                            <title>{{ $punto['fecha']->timezone(config('app.timezone'))->format('d/m/Y H:i') }}: {{ $punto['valor'] }} {{ $grafico['unit'] }}</title>
                                        </circle>
                                    @endforeach
                                </svg>
                                <div class="flex justify-between text-xs text-[#82907e]">
                                    <span>{{ $lecturas->first()->fecha_hora->timezone(config('app.timezone'))->format('d/m H:i') }}</span>
                                    <span>{{ $lecturas->last()->fecha_hora->timezone(config('app.timezone'))->format('d/m H:i') }}</span>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                <div class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#0f3c2b]">Alertas de {{ $planta->nombre ?? 'esta planta' }}</h3>
                            <p class="mt-1 text-sm text-[#718071]">Avisos activos y episodios ya resueltos.</p>
                        </div>
                        <img class="h-7 w-7" src="{{ asset('images/alerta.png') }}" alt="">
                    </div>
                    @if ($alertas->isEmpty())
                        <p class="rounded-2xl bg-[#f7f9f5] p-4 text-sm text-[#718071]">Todavía no hay alertas para esta planta.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($alertas as $alerta)
                                <article class="rounded-2xl border p-4 {{ $alerta->resuelta_en ? 'border-[#e5ebdf] bg-[#f7f9f5]' : 'border-[#f1d7a9] bg-[#fff9ed]' }}">
                                    <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start">
                                        <div>
                                            <p class="text-sm font-semibold {{ $alerta->resuelta_en ? 'text-[#536252]' : 'text-[#684b1b]' }}">
                                                {{ $alerta->mensaje ?? 'Alerta de tipo ' . str_replace('_', ' ', $alerta->tipo) }}
                                            </p>
                                            <p class="mt-1 text-xs text-[#82907e]">
                                                {{ $alerta->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                @if ($alerta->resuelta_en)
                                                    · Resuelta {{ $alerta->resuelta_en->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                                @endif
                                            </p>
                                        </div>
                                        <span class="w-fit rounded-full px-3 py-1 text-xs font-bold {{ $alerta->resuelta_en ? 'bg-[#e6eee1] text-[#536252]' : 'bg-[#ffedcc] text-[#8a5700]' }}">
                                            {{ $alerta->resuelta_en ? 'Resuelta' : 'Activa' }}
                                        </span>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endforeach
    @endif
</div>
