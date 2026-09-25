@extends('layouts.app')

@section('title', 'Historial y Alertas - Adaptia')

@section('content')
<main>
    <!-- Encabezado de la sección -->
    <section class="mb-12 reveal">
        <div class="flex flex-col items-center p-8 bg-[#f0f9ff] rounded-2xl">
            <div class="w-24 h-24 bg-[#7cb22b] rounded-full flex items-center justify-center mb-4">
                <img class="w-12 h-12" src="{{ asset('images/historial.png') }}" alt="Icono Historial">
            </div>
            <h1 class="text-3xl font-bold text-[#0f3c2b] mb-2">
                Historial y Alertas
            </h1>
            <p class="text-lg text-[#5f6b54] mb-4">
                Monitoreo de tus plantas y sensores
            </p>
        </div>
    </section>

    <!-- Mensaje informativo sobre funcionalidad futura -->
    <section class="mb-12 reveal">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-8 text-center">
            <h3 class="text-lg font-bold text-yellow-800 mb-4">
                Funcionalidad en Desarrollo
            </h3>
            <p class="text-sm text-yellow-700 mb-6">
                Esta sección mostrará:
            </p>
            <div class="space-y-3 text-left max-w-xl mx-auto">
                <p class="flex items-start space-x-2">
                    <img class="w-4 h-4 mt-1" src="{{ asset('images/termometro.png') }}" alt="Temperatura">
                    <span>Historial de lecturas de tus sensores de temperatura, humedad, luz, etc.</span>
                </p>
                <p class="flex items-start space-x-2">
                    <img class="w-4 h-4 mt-1" src="{{ asset('images/alerta.png') }}" alt="Alerta">
                    <span>Alertas generadas cuando los valores estén fuera de rangos óptimos</span>
                </p>
                <p class="flex items-start space-x-2">
                    <img class="w-4 h-4 mt-1" src="{{ asset('images/notificacion.png') }}" alt="Notificación">
                    <span>Notificaciones sobre riego, abono y otros eventos importantes</span>
                </p>
            </div>
            <p class="mt-4 text-xs text-yellow-600 italic">
                Esta funcionalidad estará disponible cuando se implemente la Fase 4 (Monitoreo IoT).
            </p>
        </div>
    </section>

    <!-- Placeholder para futura implementación -->
    <section class="mb-12 reveal">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-[#0f3c2b] mb-4">
                Próximamente
            </h2>
            <p class="text-xl text-[#5f6b54] mb-6">
                Estamos trabajando para traerte el monitoreo en tiempo real de tus plantas
            </p>
            <div class="flex flex-col items-center space-y-4">
                <div class="w-16 h-16 bg-[#eef0e9] rounded-full flex items-center justify-center mb-4">
                    <img class="w-10 h-10" src="{{ asset('images/refresh.png') }}" alt="Actualizando">
                </div>
                <p class="text-sm text-[#5f6b54]">
                    Los datos de tus sensores aparecerán aquí una vez que la Fase 4 esté completa
                </p>
            </div>
        </div>
    </section>
</main>
@endsection

@push('scripts')
<script>
    // Script placeholder para futura funcionalidad de historial/alertas
    document.addEventListener('DOMContentLoaded', function() {
        // Aquí irá el JavaScript para:
        // - Mostrar gráficos históricos de sensores
        // - Manejar marcas de alertas como leídas/no leídas
        // - Filtrar por rango de fechas y tipo de alerta
        // - Actualizar datos en tiempo real (cuando esté disponible)
        console.log('Historial y Alertas - Funcionalidad pendiente de implementación (Fase 4)');
    });
</script>
@endpush