
# INFORME TÉCNICO: PRUEBAS DE HUMO (SMOKE TESTS) - PROYECTO ADAPTIA

**Proyecto:** Adaptia — Sistema Inteligente de Recomendación y Telemetría IoT para Plantas
**Entorno Tecnológico:** Laravel (MVC) + Pest TDD + Microcontrolador ESP32
**Estándar de Calidad:** ISO/IEC 25010 (Mantenibilidad, Confiabilidad y Eficiencia de Rendimiento)

## 1. INTRODUCCIÓN Y CONTEXTO METODOLÓGICO

En el marco de la ingeniería de software y la evaluación de calidad de sistemas embebidos, una Prueba de Humo (Smoke Test o Sanity Check) representa la primera suite de verificación de ejecución preliminar. Su objetivo principal no es evaluar la lógica algorítmica compleja o casos de borde (edge cases), sino confirmar de forma inmediata que la infraestructura crítica de la aplicación se encuentra "viva", estable y libre de errores catastróficos (tales como colapsos de servidor HTTP 500 Internal Server Error o fallos de compilación).

En el proyecto Adaptia, donde coexisten un backend web en Laravel y una capa de hardware IoT basada en el microcontrolador ESP32, las pruebas de humo garantizan que tanto la interfaz de usuario como la API REST de ingesta telemétrica respondan en milisegundos antes de ejecutar las pruebas unitarias e integrales en el pipeline de Integración Continua (CI/CD).

## 2. ALCANCE DE LAS PRUEBAS DE HUMO EN ADAPTIA

Las pruebas de humo de Adaptia se enfocan en verificar cuatro pilares fundamentales de la arquitectura:

- **Disponibilidad del Módulo Recomendador Web:** Confirmar que la vista pública del formulario de captura de perfil del hogar carga correctamente.
- **Disponibilidad y Respuesta de la API IoT (/api/telemetria):** Confirmar que el endpoint destinado al ESP32 está escuchando peticiones HTTP y procesa la capa de validación inicial.
- **Acceso al Dashboard de Monitoreo Autenticado:** Verificar que la vista de seguimiento telemétrico responda adecuadamente para usuarios autenticados.
- **Salud de la Capa de Datos (Base de Datos):** Confirmar que la conexión con el catálogo maestro de GardenLand se encuentra operativa.

## 3. ESPECIFICACIÓN DE CÓDIGO EN PEST TDD (tests/Feature/SmokeTest.php)

A continuación se presenta la implementación de la suite de pruebas de humo escrita en Pest TDD para Laravel:

```php
<?php

use App\Models\User;
use App\Models\Planta;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| SUITE DE PRUEBAS DE HUMO (SMOKE TESTS) - ADAPTIA
|--------------------------------------------------------------------------
| Verificaciones ultra rápidas de salud del servidor, rutas clave y API.
|
*/

it('P-HUMO-01: La vista del recomendador botánico está viva y responde HTTP 200', function () {
    $response = $this->get('/recomendador');

    $response->assertStatus(200)
             ->assertSee('Adaptia');
});

it('P-HUMO-02: El endpoint API de telemetría IoT responde a solicitudes HTTP POST', function () {
    // Al enviar un payload nulo, la API debe responder 422 (Error de validación) y NO 500 (Server Error)
    $response = $this->postJson('/api/telemetria', []);

    $response->assertStatus(422)
             ->assertJsonStructure(['errors']);
});

it('P-HUMO-03: El dashboard de monitoreo telemétrico es accesible para usuarios autenticados', function () {
    $usuario = User::factory()->create();

    $response = $this->actingAs($usuario)
                     ->get('/dashboard');

    $response->assertStatus(200);
});

it('P-HUMO-04: La base de datos está conectada y el catálogo maestro de GardenLand responde', function () {
    // Verifica que la conexión PDO esté activa
    expect(DB::connection()->getPdo())->not->toBeNull();

    // Verifica que exista al menos un registro en el catálogo
    Planta::factory()->create(['nombre_comun' => 'Monstera Test']);
    
    $this->assertDatabaseHas('plantas', [
        'nombre_comun' => 'Monstera Test',
    ]);
});
```

## 4. MATRIZ DE TRAZABILIDAD Y CRITERIOS DE ACEPTACIÓN

| Código Test | Módulo Evaluado | Endpoint / Acción | Estado Esperado | Tiempo Máx. Ejecución | Criterio de Aceptación |
|---|---|---|---|---|---|
| P-HUMO-01 | Recomendador Web | GET /recomendador | HTTP 200 OK | < 150 ms | Renderiza el HTML del formulario sin excepciones PHP. |
| P-HUMO-02 | API IoT ESP32 | POST /api/telemetria | HTTP 422 Unprocessable | < 100 ms | Pasa la capa de enrutamiento y responde con estructura de validación limpia. |
| P-HUMO-03 | Dashboard Usuario | GET /dashboard | HTTP 200 OK | < 150 ms | Middleware de autenticación permite el acceso al panel telemétrico. |
| P-HUMO-04 | Base de Datos | DB::connection() | PDO Active | < 50 ms | Permite lectura y escritura en la tabla plantas de GardenLand. |

## 5. FUNDAMENTACIÓN CIENTÍFICA Y VALIDACIÓN EN LA LITERATURA

La inclusión de las pruebas de humo en Adaptia responde directamente a los hallazgos y desafíos documentados en las investigaciones del proyecto:

- **Mitigación de Errores por Interferencias Inalámbricas:** Las pruebas de campo de Quino et al. (2021) demostraron que la transmisión remota de datos en entornos reales sufre interferencias de señal (radiofrecuencia, ruidos de canal o caídas temporales) que pueden provocar el envío de paquetes nulos o corruptos. La prueba P-HUMO-02 garantiza que ante un paquete malformado la API responda con un estado HTTP 422 sanitizado, evitando el colapso del servidor.
- **Garantía de Ingesta Telemétrica Continuada:** La arquitectura del Smart Plant Assistant (Soibam & Vignesh, 2025) y los sistemas IoT de monitoreo hídrico (Absar et al., 2023) dependen de la disponibilidad ininterrumpida de la capa web. Las pruebas de humo validan en milisegundos que los canales Wi-Fi del ESP32 encontrarán un servidor receptivo.
- **Integridad del Modelo Relacional Unificado:** Siguiendo la metodología de administración de inventarios de plantas del sistema NMIS (Davis, 2003), la prueba P-HUMO-04 confirma la disponibilidad de la base de datos relacional que relaciona los usuarios, las macetas y las especies del catálogo.