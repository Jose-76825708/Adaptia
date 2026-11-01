# Evidencia de verificacion inicial - Adaptia

**Proyecto:** Adaptia — recomendacion de plantas, gestion e ingesta IoT
**Backend:** Laravel / Pest
**Fecha de la corrida registrada:** 2026-10-31

## 1. Alcance y significado

Este documento consolida evidencia de regresion disponible. No existe en el repositorio una suite independiente `tests/Feature/SmokeTest.php`; por tanto, no se presenta una implementacion hipotetica como prueba ejecutable. La corrida automatizada verifica el backend y los assets, pero no sustituye pruebas manuales de interfaz, pruebas de carga ni validacion en hardware.

## 2. Evidencia automatizada registrada

| Evidencia | Area | Referencia | Resultado registrado |
|---|---|---|---|
| Pest | API de lecturas IoT | `tests/Feature/LecturaSensorApiTest.php` | Incluida en las 130 pruebas aprobadas |
| Pest | Panel e historial de sensores del cliente | `tests/Feature/ClientSensorsDashboardTest.php` | Incluida en las 130 pruebas aprobadas |
| Pest | Regresion backend | `php artisan test` | 130 pruebas y 774 aserciones aprobadas |
| JavaScript | Componentes del frontend | `npm run test:js` | 9 pruebas aprobadas |
| Build | Assets del frontend | `npm run build` | Compilacion Vite exitosa |

Estos resultados corresponden a una corrida local fechada; no representan una garantia de ejecuciones posteriores ni resultados de rendimiento.

## 3. Contrato IoT comprobado en el backend

La ruta implementada es `POST /api/lecturas`. El middleware autentica el sensor con `X-Sensor-Token`, comprueba el hash SHA-256 y requiere que el sensor este activo. El payload incluye `planta_vendida_id`, `humedad_suelo`, `temperatura`, `humedad_ambiental`, `luz` y `fecha_hora`.

Las credenciales ausentes o invalidas se rechazan con HTTP 401. Una solicitud autenticada con datos invalidos recibe HTTP 422. Las lecturas validas se persisten y se evalua si las mediciones estan fuera de los rangos configurados para la especie; las alertas son informativas y no accionan actuadores.

## 4. Limites y trabajo pendiente de verificacion

- No se encontro firmware ESP32 versionado ni evidencia de transmision fisica; la integracion requiere pruebas en dispositivo.
- La deteccion de nodos sin lecturas durante mas de 24 horas (RF-14) no esta implementada.
- No se midieron los objetivos de latencia de RNF-01 y RNF-02.
- No se encontro suite Cypress ni evidencia de pruebas extremo a extremo.
- No se verifico un despliegue Docker, staging o una base de datos de produccion.

Las pruebas de transmision IoT en campo y la disponibilidad de hardware requieren una estrategia diferenciada respecto de las pruebas convencionales (Ferreira et al., 2023). Los estudios de monitoreo de plantas con sensores IoT motivan evaluar la ingesta y la integridad relacional, pero no sustituyen la evidencia de ejecucion en el entorno de Adaptia (Soibam & Vignesh, 2025; Davis, 2003).
