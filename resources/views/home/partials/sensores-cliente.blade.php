<section id="mis-sensores" class="reveal"
         data-monitoring-poll-url="{{ route('cliente.monitoreo.inicio') }}"
         data-monitoring-poll-interval="15000">
    @if ($sensoresAsignados->isNotEmpty())
        <div class="mb-6">
            <p class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-[#7a965f]">Dispositivos</p>
            <h2 class="text-2xl font-bold tracking-tight text-[#0f3c2b] sm:text-3xl">Tus sensores asignados</h2>
            <p class="mt-2 text-base text-[#718071]">Consulta la lectura más reciente y las alertas activas de cada planta.</p>
        </div>

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            @foreach ($sensoresAsignados as $plantaVendida)
                <div class="rounded-3xl border border-[#e5ebdf] bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg sm:p-7">
                    @php
                        $ultimaLectura = $plantaVendida->lecturasSensores->first();
                        $alertasActivas = $plantaVendida->alertas->whereNull('resuelta_en');
                    @endphp
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#eff6e8]">
                                <img class="h-6 w-6" src="{{ asset('images/sensor.png') }}" alt="">
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-[#82907e]">Sensor asignado</p>
                                <h3 class="truncate font-bold text-[#0f3c2b]">
                                    #{{ $plantaVendida->sensor->identificador_fisico }}
                                </h3>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1.5 text-xs font-bold {{ $plantaVendida->sensor->estado !== 'activo' ? 'bg-[#f8d7da] text-[#721c24]' : ($alertasActivas->isNotEmpty() ? 'bg-[#fff1dc] text-[#8a5700]' : 'bg-[#eff6e8] text-[#52752d]') }}">
                            {{ $plantaVendida->sensor->estado !== 'activo' ? 'Sensor inactivo' : ($alertasActivas->isNotEmpty() ? 'Requiere atención' : ($ultimaLectura ? 'Sin alertas activas' : 'Esperando lectura')) }}
                        </span>
                    </div>

                    <div class="mb-5 flex flex-col gap-2 rounded-2xl bg-[#f7f9f5] px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                        <span class="font-medium text-[#718071]">Planta asociada</span>
                        <span class="font-semibold text-[#34533b]">{{ $plantaVendida->venta->planta->nombre ?? 'Planta no disponible' }}</span>
                    </div>
                    @if ($ultimaLectura)
                        <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                            @foreach ([
                                ['label' => 'Humedad del suelo', 'value' => $ultimaLectura->humedad_suelo, 'unit' => '%'],
                                ['label' => 'Temperatura', 'value' => $ultimaLectura->temperatura, 'unit' => '°C'],
                                ['label' => 'Humedad ambiental', 'value' => $ultimaLectura->humedad_ambiental, 'unit' => '%'],
                                ['label' => 'Luz', 'value' => $ultimaLectura->luz, 'unit' => 'lux'],
                            ] as $medicion)
                                <div class="rounded-2xl bg-[#f7f9f5] p-3">
                                    <p class="text-xs font-medium text-[#718071]">{{ $medicion['label'] }}</p>
                                    <p class="mt-1 text-lg font-bold text-[#0f3c2b]">{{ rtrim(rtrim(number_format((float) $medicion['value'], 2), '0'), '.') }} <span class="text-sm font-semibold">{{ $medicion['unit'] }}</span></p>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-[#82907e]">
                            Última lectura: {{ $ultimaLectura->fecha_hora->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                        </p>
                    @else
                        <div class="rounded-2xl border border-dashed border-[#dce9d0] bg-[#f7f9f5] p-4 text-sm text-[#718071]">
                            Aún no hay lecturas disponibles para este sensor.
                        </div>
                    @endif

                    @if ($alertasActivas->isNotEmpty())
                        <div class="mt-5 space-y-3">
                            <h4 class="font-bold text-[#8a5700]">Alertas activas</h4>
                            @foreach ($alertasActivas as $alerta)
                                <article class="rounded-2xl border border-[#f1d7a9] bg-[#fff9ed] p-4">
                                    <p class="text-sm font-semibold text-[#684b1b]">
                                        {{ $alerta->mensaje ?? 'Esta planta requiere atención (' . str_replace('_', ' ', $alerta->tipo) . ').' }}
                                    </p>
                                    <p class="mt-1 text-xs text-[#8c7650]">
                                        {{ $alerta->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center gap-5 rounded-3xl border border-[#e4ebdb] bg-white p-7 text-center shadow-sm sm:p-10">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[#eff6e8]">
                <img class="h-9 w-9" src="{{ asset('images/plantas.png') }}" alt="">
            </div>
            <div>
                <h3 class="text-xl font-bold text-[#0f3c2b]">Aún no tienes sensores asignados</h3>
                <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-[#718071]">
                    Cuando se asigne un sensor a una de tus plantas, aparecerá aquí junto con sus lecturas disponibles.
                </p>
            </div>
            <a href="{{ route('catalogo.plantas.index') }}"
               class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#7cb22b] px-6 py-3 font-bold text-white shadow-md shadow-[#7cb22b]/20 transition hover:-translate-y-0.5 hover:bg-[#6eab26]">
                Explorar plantas disponibles
            </a>
        </div>
    @endif
</section>
