# Reporte consolidado de issues de SonarQube

**Fecha de revisión:** 19 de octubre de 2026  
**Alcance:** archivos JSON de SonarQube/SonarCloud presentes en la raíz del repositorio.  
**Criterio:** se contaron como un mismo issue las entradas con la misma regla, ruta relativa y línea, aunque aparecieran en más de una exportación o tuvieran distinto identificador. Los proyectos se normalizaron por ruta para comparar exportaciones con distintas claves de proyecto.

## Resumen ejecutivo

Se revisaron cuatro exportaciones de issues, dos archivos de métricas y la configuración SonarQube del repositorio. Las cuatro exportaciones contienen **187 entradas**. Al eliminar duplicados entre exportaciones y repeticiones dentro de ellas, quedan **122 ubicaciones únicas**, correspondientes a **19 reglas**.

| Severidad reportada | Ubicaciones únicas | Observación |
|---|---:|---|
| BLOCKER | 57 | Todas corresponden a una regla de PHP en vistas Blade compiladas bajo `storage/framework/views/`. |
| CRITICAL | 3 | Dos incidencias por literales duplicados en configuración y una regla de JavaScript. |
| MAJOR | 35 | Mantenibilidad, promesas, expresiones y accesibilidad web. |
| MINOR | 27 | Principalmente nuevas líneas al final de archivos y observaciones menores de estilo. |
| **Total** | **122** | Todas las entradas de issues revisadas aparecen como abiertas en sus exportaciones. |

Los archivos revisados no registran issues clasificados como `VULNERABILITY` ni elementos `SECURITY_HOTSPOT`. Esto solo describe los JSON disponibles; no equivale a una auditoría de seguridad ni confirma el estado actual del proyecto en SonarQube.

## Evidencia posterior: último análisis visible en SonarCloud

El 31 de octubre de 2026, el responsable compartió una captura del resumen de SonarCloud para la rama `main`, commit `8804d33`. La interfaz indica que el último análisis se había ejecutado 13 días antes y muestra **Quality Gate: Passed**, **0 issues abiertos** en Security, Reliability y Maintainability, **0 issues aceptados**, **99.4 % de cobertura**, **0.0 % de duplicación** y **0 security hotspots**. Esta captura es evidencia posterior y más pertinente para describir el estado mostrado por Sonar que las exportaciones JSON enumeradas abajo.

Las 122 ubicaciones únicas de este informe son un consolidado histórico de exportaciones locales anteriores; no representan el estado de issues abiertos en ese análisis de `main`. El resultado visible acredita el análisis del commit indicado, no una corrida aislada por Bolt ni un análisis de cambios locales o commits posteriores. Tampoco sustituye revisiones de seguridad adicionales al alcance de Sonar.

## Exportaciones revisadas y deduplicación

| Archivo | Entradas de issues |
|---|---:|
| [`sonar-issues.json`](../sonar-issues.json) | 98 |
| [`sonarqube-adaptia-issues.json`](../sonarqube-adaptia-issues.json) | 62 |
| [`sonarqube-adaptia-issues-after.json`](../sonarqube-adaptia-issues-after.json) | 20 |
| [`sonarqube-ultimo-issues.json`](../sonarqube-ultimo-issues.json) | 7 |
| **Total antes de deduplicar** | **187** |

Las exportaciones identifican el proyecto con claves distintas (`Jose-76825708_Adaptia` y `adaptia`) y no todas incluyen fecha de exportación. Por esto, los 122 issues únicos representan el conjunto consolidado de las capturas disponibles, **no un conteo certificado del análisis más reciente**. Se eliminaron 65 entradas repetidas según regla, ruta y línea.

## Hallazgos únicos

Las cantidades de las tablas son ubicaciones únicas, no conteos sumados de las distintas exportaciones. Las rutas y líneas reflejan los valores guardados en los JSON; no se verificó si cada finding sigue presente en el código actual.

### BLOCKER — 57 ubicaciones

| Regla | Hallazgo | Ubicaciones |
|---|---|---|
| `php:S1599` | SonarQube señala el uso de la variable variable `$$__key`. | 57 incidencias en 19 archivos PHP compilados de vistas Blade bajo `storage/framework/views/`, en las líneas 8, 15, 20, 26, 34 y 40. |

**Nota de interpretación:** son artefactos generados/cacheados, no las plantillas Blade fuente. La configuración del proyecto declara `storage/**` en `sonar.exclusions` ([`sonar-project.properties`](../sonar-project.properties)). Conviene verificar que esa configuración se aplique en el análisis que produjo `sonar-issues.json` y corregir la exclusión/origen del análisis antes de intentar editar esos archivos compilados. Aunque el export los marque como `BLOCKER`, son issues de tipo `CODE_SMELL`, no vulnerabilidades.

### CRITICAL — 3 ubicaciones

| Regla | Hallazgo | Ubicación |
|---|---|---|
| `javascript:S3735` | Sonar recomienda quitar el operador `void`. | `resources/js/wizard-perfil.js:182` |
| `php:S1192` | Literal `127.0.0.1` repetido; Sonar recomienda extraer una constante. | `config/database.php:50` |
| `php:S1192` | Literal `logs/laravel.log` repetido; Sonar recomienda extraer una constante. | `config/logging.php:63` |

### MAJOR — 35 ubicaciones

| Regla | Cantidad | Hallazgo y ubicaciones |
|---|---:|---|
| `Web:S6819` | 7 | Usar elementos HTML semánticos para estados y barras de progreso: `resources/views/perfiles/edit.blade.php:110`; `resources/views/personal/create.blade.php:27`; `resources/views/plantas-vendidas/index.blade.php:25`; `resources/views/sensores/index.blade.php:32`; `resources/views/ventas/create.blade.php:38,44`; `resources/views/ventas/index.blade.php:30`. |
| `Web:S6848` | 1 | Elemento interactivo no nativo sin la semántica y soporte de interacción recomendados: `resources/views/partials/sidebar.blade.php:77`. |
| `Web:S6851` | 12 | Texto redundante (“image”/“photo”) en el atributo `alt`: `resources/views/home.blade.php:20,38,61,85,114,138,162,186,225,237,249,261`. Variantes de redacción del mensaje para la línea 138 se consolidaron como una sola ubicación. |
| `Web:S7927` | 1 | El nombre accesible debe formar parte de la etiqueta visible: `resources/views/plantas-vendidas/index.blade.php:76`. |
| `javascript:S8786` | 1 | Expresión regular con posible retroceso superlineal; revisar el patrón: `tests/js/fake-dom.js:121`. |
| `javascript:S9383` | 2 | Promesas sin `await`, manejo de rechazo ni descarte explícito: `resources/js/monitoreo-polling.js:63`; `resources/js/wizard-perfil.js:204`. |
| `php:S112` | 4 | Se lanzan excepciones genéricas; Sonar recomienda excepciones específicas: `app/Services/MovimientoInventarioService.php:54,118,167`; `database/migrations/2026_10_04_170000_expand_alertas_for_iot_episodes.php:25`. |
| `php:S1142` | 3 | Métodos con más sentencias `return` que el umbral de la regla: `app/Http/Controllers/AuthController.php:24`; `app/Http/Controllers/HomeController.php:20`; `app/Services/RecomendacionService.php:37`. |
| `php:S125` | 2 | Código comentado que Sonar recomienda retirar: `app/Models/User.php:5`; `database/seeders/DatabaseSeeder.php:18`. |
| `php:S3358` | 2 | Expresiones ternarias anidadas: `config/database.php:63,83`. |

### MINOR — 27 ubicaciones

| Regla | Cantidad | Hallazgo y ubicaciones |
|---|---:|---|
| `Web:MouseEventWithoutKeyboardEquivalentCheck` | 1 | Falta equivalencia de interacción por teclado en el elemento señalado: `resources/views/partials/sidebar.blade.php:77`. Comparte ubicación con otro hallazgo de accesibilidad, pero es una regla distinta. |
| `javascript:S7786` | 1 | Usar `TypeError` en vez de `Error` para la comprobación de tipo: `resources/js/monitoreo-polling.js:44`. |
| `php:S1125` | 1 | Literal booleano `true` redundante: `app/Services/RecomendacionService.php:144`. |
| `php:S113` | 20 | Falta una nueva línea al final de cada uno de estos archivos: `app/Http/Controllers/AuthController.php`; `app/Http/Controllers/MovimientoInventarioController.php`; `app/Http/Controllers/SensorController.php`; `app/Models/MovimientoInventario.php`; `app/Models/Sensor.php`; `app/Models/User.php`; `app/Services/MovimientoInventarioService.php`; `app/Services/SensorService.php`; `database/migrations/2026_09_09_000001_update_perfiles_cliente_table.php`; `resources/views/home.blade.php`; `resources/views/home/client.blade.php`; `resources/views/layouts/app.blade.php`; `resources/views/movimientos-inventario/create.blade.php`; `resources/views/movimientos-inventario/edit.blade.php`; `resources/views/movimientos-inventario/index.blade.php`; `resources/views/partials/sidebar.blade.php`; `resources/views/sensores/create.blade.php`; `resources/views/sensores/edit.blade.php`; `resources/views/sensores/index.blade.php`; `routes/web.php`. |
| `php:S1131` | 3 | Espacios finales sobrantes: `app/Services/RecomendacionService.php:98,103,137`. |
| `php:S1488` | 1 | Retornar directamente la expresión en vez de asignarla a `$plantas_ordenadas`: `app/Services/RecomendacionService.php:154`. |

## Métricas y errores auxiliares

Los archivos de métricas no aportan más issues, pero se revisaron y se registran por separado para no confundirlos con findings:

- [`sonarqube-database-metricas.json`](../sonarqube-database-metricas.json) contiene métricas para `config/database.php`: 53 líneas duplicadas y densidad de duplicación de 27 %. También incluye valores de período de 9 líneas duplicadas nuevas y 40,90909 % de densidad nueva; son métricas reportadas, no issues adicionales.
- [`sonarqube-ultimo-metricas.json`](../sonarqube-ultimo-metricas.json) contiene el error `The following metric keys are not found: new_issues`. No entrega un valor para esa métrica y, por tanto, no permite inferir el número de issues nuevos.

## Recomendaciones de seguimiento

1. Repetir el análisis con la clave de proyecto y configuración vigentes; conservar fecha, branch y `projectKey` para identificar claramente la captura.
2. Confirmar que `storage/**` esté excluido en el análisis que se ejecuta en CI; no corregir manualmente el PHP compilado generado desde Blade.
3. Priorizar la revisión de las 3 observaciones `CRITICAL`, las promesas sin manejo explícito y los hallazgos de accesibilidad de controles interactivos.
4. Aplicar las correcciones simples de formato (nueva línea final y espacios sobrantes) si siguen presentes en los archivos fuente.
5. Volver a exportar issues y métricas tras ejecutar el análisis para distinguir hallazgos resueltos, nuevos y todavía abiertos.

## Archivos de configuración relacionados

- [`sonar-project.properties`](../sonar-project.properties) define fuentes, exclusiones y rutas de cobertura.
- [Flujo de SonarQube Cloud](../.github/workflows/sonarcloud.yml) ejecuta las pruebas y el análisis en CI.
