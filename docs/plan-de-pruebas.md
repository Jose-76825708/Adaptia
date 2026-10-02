# Plan de pruebas de software

**ADAPTIA – Sistema inteligente de recomendación, monitoreo IoT y gestión de plantas ornamentales**

**Fecha:** 27/09/2026

## Tabla de contenido

- Historial de versiones — 4
- Información del proyecto — 4
- Aprobaciones — 5
- Resumen ejecutivo — 5
- Alcance de las pruebas — 6
  - Elementos de pruebas — 6
  - Nuevas funcionalidades a probar — 7
  - Pruebas de regresión — 7
  - Funcionalidades a no probar — 8
- Enfoque de pruebas (estrategia) — 9
- Criterios de aceptación o rechazo — 10
  - Criterios de aceptación o rechazo — 10
  - Criterios de suspensión — 10
  - Criterios de reanudación — 11
- Entregables — 11
- Recursos — 11
  - Requerimientos de entornos – Hardware — 11
  - Requerimientos de entornos – Software — 12
  - Herramientas de pruebas requeridas — 12
  - Personal — 12
  - Entrenamiento — 13
- Planificación y organización — 13
  - Procedimientos para las pruebas — 13
  - Matriz de responsabilidades — 14
  - Cronograma — 14
- Premisas — 15
- Dependencias y Riesgos — 15
- Referencias — 17
- Glosario — 17

## Historial de versiones

El presente historial registra las versiones emitidas del plan de pruebas de software de Adaptia, con su fecha de emisión, autoría, organización y la descripción de los cambios realizados.

| Fecha | Versión | Autores | Organización | Descripción |
|---|---|---|---|---|
| 27/09/2026 | 1.0 | Marcelo Valer, Alessandro Percy<br>Osorio Blancas, Jose Carlos<br>Piñas Solis, Victor Sebastián | Universidad continental | Primera versión del plan de pruebas, enfocada en el módulo de recomendación de Adaptia |
| 28/09/2026 | 1.1 | Marcelo Valer, Alessandro Percy<br>Osorio Blancas, Jose Carlos<br>Piñas Solis, Victor Sebastián | Universidad Continental | Versión con respaldo y citas extraídas de dos artículos científicos. |

## Información del proyecto

Adaptia es un sistema web de recomendación inteligente, monitoreo IoT y gestión de plantas ornamentales desarrollado para la tienda GardenLand ubicada en Huancayo, Junín, como proyecto académico de la Universidad Continental. La información general de identificación del proyecto se resume en la siguiente tabla:

| Campo | Detalle |
|---|---|
| Empresa / Organización | Universidad Continental - Curso de Pruebas y Calidad de Software |
| Proyecto | Adaptia - Sistema inteligente de recomendación, monitoreo IoT y gestión de plantas ornamentales |
| Fecha de preparación | 27/09/2026 |
| Cliente | Garden Land  - Tienda de plantas ornamentales (Huancayo, Junín) |
| Patrocinador principal | Mg. Maglioni Arana Caparachin |
| Gerente / Líder de proyecto | Osorio Blancas, Jose Carlos |
| Gerente / Líder de pruebas de software | Marcelo Valer, Alessandro Percy |

## Aprobaciones

Personas responsables de la revisión y de la aprobación formal del presente plan de pruebas de software.

| Nombre y Apellido | Cargo | Departamento u organización | Fecha | 
|---|---|---|---|
| Osorio Blancas, Jose Carlos | Líder de proyecto / Líder de pruebas / Desarrollador / Tester | Universidad Continental | 27/09/2026 | |
| Marcelo Valer, Alessandro Percy | Líder de pruebas de Software / Desarrollador / Tester | Universidad Continental | 27/09/2026 | |
| Piñas Solis, Víctor Sebastián | Analista de Pruebas / Desarrollador / Tester | Universidad Continental | 27/09/2026 | |

## Resumen ejecutivo

Este documento es el plan de pruebas de software de Adaptia. Su propósito es definir el alcance, la estrategia, los criterios, los recursos y la organización del esfuerzo de pruebas del sistema, de modo que los resultados sean verificables y repetibles. Se trata de un plan de nivel detallado: además de la estrategia general, especifica los módulos a probar, los tipos de prueba, los criterios de aceptación, suspensión y reanudación, y las pruebas automatizadas ya implementadas en el repositorio.

El alcance del esfuerzo de pruebas corresponde a las fases 1 y 2 del proyecto, que son las implementadas hasta la fecha: administración de catálogo e inventario (tipos de planta, plantas, movimientos de inventario y sensores), autenticación con roles, perfil e historial del cliente, y el motor de recomendación multicriterio. Las fases 3 (ventas y vendedor), 4 (monitoreo IoT) y 5 (notificaciones) aún no están desarrolladas, por lo que sus pruebas quedan fuera de esta versión del plan y algunas serán incorporadas en la versión 2.0.

El esfuerzo se concentra en el módulo de recomendación, que cuenta al momento de emitir este documento con 11 pruebas automatizadas en verde (9 unitarias y 2 de integración con base de datos), más la prueba base de ejemplo del proyecto, para un total de 12 pruebas que se ejecutan con el comando composer test (php artisan test) sobre una base de datos SQLite en memoria.

Restricciones: el proyecto se ejecuta sin presupuesto y sin servidores dedicados de pruebas; todo el esfuerzo se realiza en los equipos locales de los integrantes y dentro del calendario del ciclo académico. El equipo de pruebas está conformado por tres integrantes que, además, cubren el desarrollo del software.

## Alcance de las pruebas

### Elementos de pruebas

Al ser un plan de nivel detallado, se listan tanto las áreas funcionales como los componentes de código que serán objeto de prueba:

- Autenticación y usuarios: registro, inicio y cierre de sesión, y asignación de rol (cliente, vendedor, administrador) mediante AuthController con las rutas /login, /register y /logout (routes/web.php).
- Motor de recomendación: app/Services/RecomendacionService.php con los métodos limpiarPerfil, compatibilidadOrdinal, compatibilidadCategoria, calculaScore, plantasNoToxicas y generarRecomendaciones.
- Catálogo administrativo: CRUD de tipos de planta (TipoPlantaController y TipoPlantaService) y CRUD de plantas (PlantaController y PlantaService), incluyendo imagen, validaciones, precios y stock.
- Inventario: CRUD de movimientos de inventario de entrada y salida (MovimientoInventarioController y MovimientoInventarioService) y alerta de stock bajo por especie (RF-12).
- Sensores: CRUD de sensores disponibles (SensorController y SensorService), previo a su asignación a plantas vendidas.
- Perfil e historial del cliente: edición de perfiles_cliente (PerfilController), vista home con el ranking de recomendaciones (HomeController) e historial de alertas (RF-10).
- Capa de presentación: vistas Blade y layout compartido resources/views/layouts/app.blade.php, que concentra el header y el footer de todas las pantallas.
- Base de datos: esquema descrito en docs/modelo-bd.md (users, perfiles_cliente, tipos_planta, plantas, ventas, plantas_vendidas, sensores, lecturas_sensores, alertas, notificaciones y movimientos_inventario) junto con sus migraciones.

### Nuevas funcionalidades a probar

Desde el punto de vista del usuario, las funcionalidades nuevas o modificadas que se someterán a prueba son:

- El cliente puede registrarse, iniciar sesión y trabajar sin ver el panel administrativo, usando el home como centro principal de navegación.
- El cliente responde el cuestionario tipo wizard animado para crear y editar su perfil de preferencias, y ve el resumen de su perfil en el header.
- A partir del perfil, el sistema muestra en el home el ranking de las 10 plantas más compatibles, aplicando el filtro eliminatorio de toxicidad cuando el usuario reporta mascotas o niños.
- El administrador registra, edita y elimina tipos de planta y plantas, con imagen, precios, enums de cuidado y niveles de stock.
- El administrador registra entradas y salidas de inventario y consulta la alerta de stock bajo por especie (RF-12).
- El administrador registra los sensores disponibles antes de asignarlos.
- El cliente consulta su historial de alertas (RF-10), funcionalidad que puede presentarse vacía hasta la fase de monitoreo IoT.

### Pruebas de regresión

Funcionalidades que no son el foco del cambio actual pero que dependen de componentes afectados y deben volver a verificarse:

- El ranking del recomendador tras cualquier cambio en el modelo de plantas, en los enums de atributos o en la lógica del perfil de cliente.
- Los CRUD de tipos de planta y de plantas tras cambios en migraciones, relaciones, validaciones o en la carga de imágenes.
- Login, registro y control de acceso por roles tras cambios en rutas, middlewares o en la tabla users.
- La vista home, el header y el footer tras modificaciones en el layout compartido, ya que afectan a la landing page y a todas las pantallas de la aplicación.
- La alerta de stock bajo (RF-12) tras cambios en los movimientos de inventario o en el cálculo de stock_actual.

En Adaptia la regresión del módulo de recomendación es automatizada: la suite de Pest (tests/Unit y tests/Feature) se ejecuta con composer test después de cada corrección de defecto o modificación del modelo de datos. Esto evita el error más frecuente en el seguimiento de incidencias: cerrar un defecto sin verificar que las funcionalidades vecinas continúan funcionando adecuadamente. Este planteamiento coincide con lo señalado por Shah et al. (2025): ejecutar de forma repetida y frecuente las pruebas previas hace más efectiva la regresión y permite a los equipos contener aquellos casos en que una actualización o una corrección introduce nuevos problemas (p. 3). Las áreas sin automatizar (CRUDs por interfaz) se cubren con un recorrido manual de regresión reducido, priorizando las rutas críticas de administrador y de cliente.

### Funcionalidades a no probar

- Monitoreo IoT: firmware del ESP32 y endpoint POST /api/lecturas (fase 4 no iniciada). Además de que la fase aún no se inicia, la literatura explica por qué esta validación exige un tratamiento diferenciado: probar sistemas IoT difiere de probar software convencional y requiere enfoques y herramientas específicos (Ferreira et al., 2023, p. 113); además, los dispositivos suelen contar con menos recursos de procesamiento, memoria, almacenamiento y energía, lo que impide emplear recursos de prueba más sofisticados, y el acoplamiento más estrecho entre software y hardware vuelve las pruebas más complejas y costosas (Ferreira et al., 2023, p. 114). Riesgo asumido: al desarrollarse la fase, la integración entre el dispositivo y el backend podría exigir ajustar este plan.
- Notificaciones push y por correo (fase 5 no iniciada). Riesgo asumido: no se valida la entrega de alertas al cliente hasta que la fase esté desarrollada.
- Módulo de ventas del vendedor y asignación de sensores a plantas vendidas (fase 3 en progreso). Riesgo asumido: el descuento automático de stock por venta no estará verificado en esta versión del plan.
- Pruebas de carga, estrés y seguridad ofensiva: no se cuenta con entorno desplegado ni servidores, y el alcance es académico. Riesgo asumido: no se detectarían problemas de desempeño ni vulnerabilidades en un entorno productivo.
- Compatibilidad con navegadores antiguos o equipos de bajo rendimiento: se validan únicamente las versiones actuales de Google Chrome y Mozilla Firefox.

## Enfoque de pruebas (estrategia)

La estrategia es mixta y se apoya en una pirámide de pruebas: se automatiza la mayor parte en la base (pruebas unitarias y de integración), se complementa con pruebas funcionales sobre la interfaz y se deja la prueba extremo a extremo para la fase de cierre. Los tipos de prueba definidos son:

- Unitarias (Pest): evalúan las funciones puras del motor de recomendación sin acceder a base de datos (compatibilidadOrdinal, compatibilidadCategoria y calculaScore). Se ubican en tests/Unit. Su lugar en la base de la estrategia se apoya en que este tipo de pruebas busca asegurar que cada unidad funcione correctamente y detectar errores en las etapas iniciales del desarrollo (Shah et al., 2025, p. 5), momento en el que corregirlos resulta menos costoso (Shah et al., 2025, p. 1).
- De integración (Pest con RefreshDatabase): verifican la interacción con Eloquent y la base de datos usando factories (plantasNoToxicas y generarRecomendaciones). Se ubican en tests/Feature. Conceptualmente, este nivel se ocupa de las interfaces de comunicación entre las partes del sistema, a diferencia del nivel unitario, que busca errores en unidades menores como módulos, funciones o métodos (Ferreira et al., 2023, p. 114).
- Funcionales manuales: recorrido de cada CRUD por la interfaz con datos de prueba, verificando validaciones, mensajes, redirecciones y permisos por rol. La coexistencia de pruebas automatizadas y manuales corresponde a una estrategia híbrida como la que recomiendan Shah et al. (2025) cuando los recursos son limitados: automatizar las pruebas largas y repetitivas, como las de regresión, y reservar la prueba manual para lo exploratorio y complejo, de modo que se equilibren costo y eficiencia (p. 10).
- De regresión: ejecución de la suite completa de Pest y del recorrido manual crítico después de cada cambio.
- De aceptación: validación por fase con el responsable del proyecto, contra los requerimientos funcionales (por ejemplo RF-10 e RF-12). Este nivel busca confirmar que la solución cumple los requisitos de los usuarios finales y los objetivos comerciales, y es realizado por los propios usuarios o clientes (Shah et al., 2025, p. 6).
- No funcionales: revisión básica de usabilidad y diseño responsive de la vista home; las pruebas extremo a extremo con Cypress quedan previstas para la fase 6 de cierre.

Configuración del entorno de pruebas (phpunit.xml): APP_ENV=testing, base de datos SQLite en memoria, caché y sesiones en array, correo simulado y colas sincronizadas, de modo que cada corrida sea aislada y rápida. Los datos de prueba se generan con las factories PlantaFactory, TipoPlantaFactory y UserFactory.

## Criterios de aceptación o rechazo

### Criterios de aceptación o rechazo

- 100 % de las pruebas automatizadas en verde: 12 pruebas (11 del módulo de recomendación y 1 de ejemplo) al momento de emitir este plan, sin tendencia a decrecer. Este criterio toma la suite automatizada como evidencia objetiva de calidad. En el estudio de caso de una plataforma de comercio electrónico reportado por Shah et al. (2025), la adopción de herramientas automatizadas amplió el alcance de la regresión de 60 % a 95 % y redujo el tiempo de ejecución de la suite de unas 10 horas a 3 (p. 8); estos valores corresponden a otro contexto y no constituyen un umbral de referencia para Adaptia, por lo que el 100 % exigido es una decisión propia del equipo.
- Cobertura de todas las funciones públicas del motor de recomendación (compatibilidadOrdinal, compatibilidadCategoria, calculaScore, plantasNoToxicas y generarRecomendaciones).
- 100 % de los casos de prueba de la fase ejecutados y aprobados antes de declarar la fase cerrada.
- 0 defectos críticos o altos abiertos; los defectos menores deben tener responsable y fecha de corrección.
- Verificación de los requerimientos funcionales de la fase (RF-10 y RF-12 en las fases 1 y 2).
- Aprobación formal del plan y de los resultados de prueba por el gerente de proyecto.
- Rechazo: la fase se considera no aceptada si incumple cualquiera de los criterios anteriores, y el plan de pruebas no se da por completado hasta su cumplimiento.

### Criterios de suspensión

- 20 % o más de los casos de una corrida terminan en fallo.
- Un defecto crítico impide continuar, por ejemplo la imposibilidad de iniciar sesión o la indisponibilidad de la base de datos.
- El entorno de pruebas (Apache, MySQL o PHP) permanece inoperativo por más de 4 horas.
- Se detectan cambios no controlados en el código durante la ejecución de la suite.
- Falta de disponibilidad del responsable de las pruebas por un periodo superior a dos días.

### Criterios de reanudación

- Los defectos críticos que originaron la suspensión están corregidos y verificados.
- La suite automatizada completa vuelve al 100 % de pruebas en verde.
- Los casos de humo (inicio de sesión, carga del home y creación de una planta) se ejecutan con éxito.
- El entorno está restablecido y el código sincronizado con la versión sobre la cual se planificó la corrida.
- El gerente de pruebas autoriza formalmente la reanudación.

## Entregables

- Plan de pruebas de software de Adaptia: Este documento, en su versión 1.0.
- Casos de prueba: pruebas automatizadas en tests/Unit y tests/Feature, y casos de prueba manuales por funcionalidad.
- Resultados de ejecución: reportes emitidos por Pest y PHPUnit a partir de composer test.
- Registro y seguimiento de defectos con pasos de reproducción, severidad y estado.
- Evidencias de las pruebas manuales: capturas de pantalla de cada pantalla y flujo probado.
- Reporte de resultados y de cobertura por fase.
- Documentación técnica de apoyo: CLAUDE.md y docs/modelo-bd.md del repositorio.

## Recursos

### Requerimientos de entornos – Hardware

- Estaciones de trabajo: PC con Windows 10 o 11, procesador i5 o superior y 8 GB de RAM, una por tester.
- Servidor local: el mismo equipo de desarrollo ejecuta XAMPP (Apache y MySQL 8) para la aplicación, y SQLite para las corridas de pruebas.
- Red: conexión a internet para el repositorio y la documentación; no se requiere alta disponibilidad ni servidores remotos.
- Periféricos: una tarjeta ESP32 con sensores para la fase 4 de monitoreo IoT, no requerida en esta versión del plan.
- Dispositivos móviles opcionales para la verificación responsive de la vista home.

### Requerimientos de entornos – Software

- Sistema operativo Windows 10 o 11, con entorno de desarrollo en XAMPP.
- PHP 8.2 o superior, Composer 2 y Laravel 12.
- MySQL 8 para el entorno de aplicación y SQLite para el entorno de pruebas.
- Node.js con Vite 7 y Tailwind CSS 4 para la compilación de los recursos front-end.
- Pest 3.8 con PHPUnit, incluido en las dependencias de desarrollo del proyecto.
- Editor de código (VS Code o PhpStorm) y cliente Git.
- Navegadores Google Chrome y Mozilla Firefox en sus versiones actuales.

### Herramientas de pruebas requeridas

Las herramientas seleccionadas se orientan a la automatización mediante scripts elaborados a partir de casos de prueba diseñados previamente; según Shah et al. (2025), esta práctica permite obtener scripts que pueden reutilizarse, mantenerse y escalarse (p. 7).

- Pest 3 con PHPUnit: ejecución de pruebas unitarias y de integración mediante composer test (php artisan test).
- Archivo phpunit.xml: configuración del entorno de pruebas (SQLite en memoria, sesiones y correos simulados).
- Laravel Testing: trait RefreshDatabase, Model Factories y Faker para la creación de datos de prueba.
- Laravel Pint: verificación automática del formato del código.
- Cypress: automatización de pruebas extremo a extremo, previsto en la fase 6 de cierre.
- PlantUML: representación del modelo de datos en docs/modelo-bd.md.
- Capturas de pantalla y una plantilla de registro de defectos para las evidencias de las pruebas manuales.

### Personal

El equipo está conformado por tres integrantes de la Universidad Continental que comparten las actividades de desarrollo y de pruebas:

- Osorio Blancas José Carlos, líder del proyecto: define alcance y prioridades, aprueba el plan y los resultados de prueba.
- Marcelo Valer Alessandro Percy, líder de pruebas de software: diseña los casos de prueba, automatiza con Pest, ejecuta la suite y reporta resultados.
- Piñas Solis Víctor Sebastián, analista de pruebas: ejecuta las pruebas manuales, registra defectos y conserva las evidencias.

### Entrenamiento

La capacitación responde a que la compatibilidad y la curva de aprendizaje de las herramientas figuran entre las dificultades habituales al implementar pruebas automatizadas (Shah et al., 2025, p. 4), y a que la familiarización del personal con dichas herramientas forma parte de los costos iniciales de la automatización (Shah et al., 2025, p. 9).

- Sesión introductoria sobre Pest y la sintaxis de expect() para quienes no conozcan el framework.
- Práctica con las herramientas de testing de Laravel: factories, RefreshDatabase y base de datos SQLite en memoria.
- Recorrido funcional del sistema por rol (administrador y cliente) para que los testers conozcan los flujos antes de probarlos.
- Uso del flujo de trabajo con Git (ramas, merge y resolución de conflictos) y del comando composer test.
- Capacitación sobre el formato de la plantilla de casos de prueba y sobre cómo registrar un defecto reproducible.

## Planificación y organización

### Procedimientos para las pruebas

Se aplica una metodología de pruebas alineada con el orden de fases del proyecto (Administrador, Cliente, Vendedor, IoT y Notificaciones), con los siguientes pasos para cada funcionalidad:

1. Revisar el requerimiento y su alcance en la documentación del proyecto (CLAUDE.md y docs/modelo-bd.md).
2. Diseñar los casos de prueba: datos de entrada, pasos, resultado esperado y condiciones de salida.
3. Implementar la prueba automatizada en tests/Unit (lógica pura) o tests/Feature (con base de datos), siguiendo el patrón ya existente del recomendador.
4. Ejecutar composer test y verificar que el 100 % de la suite quede en verde.
5. Ejecutar el recorrido manual de la funcionalidad por la interfaz con datos de prueba.
6. Registrar los defectos encontrados con pasos de reproducción, severidad y módulo afectado.
7. Corregir el defecto en la capa de negocio (Services) y re-ejecutar la suite completa como regresión.
8. Documentar las evidencias y reportar los resultados al gerente de proyecto para la aprobación de la fase.

### Matriz de responsabilidades

Matriz RACI del equipo de calidad, donde R significa responsable, A aprobador, C consultado e Informado:

| Actividad | Osorio Blancas José Carlos | Marcelo Valer Alessandro Percy | Piñas Solis Víctor Sebastián |
|---|---|---|---|
| Elaborar y mantener el plan de pruebas | A | R | C |
| Diseñar los casos de prueba | I | A | R |
| Automatizar las pruebas con Pest | I | R | C |
| Ejecutar pruebas y registrar resultados | I | A | R |
| Registrar y dar seguimiento a los defectos | C | A | R |
| Reportar y aprobar los resultados de la fase | A | R | I |

### Cronograma

Cronograma preliminar de actividades de pruebas, alineado con las fases del proyecto y con el calendario del ciclo:

| Semana | Hito o actividad de pruebas | Dependencia |
|---|---|---|
| Semana 7 (27/09/2026) | Emisión y aprobación del plan de pruebas de software de Adaptia, versión 1.0 | Documentación de requerimientos y alcance de las fases 1 y 2 |
| Semana 8 | Casos de prueba y automatización del motor de recomendación; reporte de resultados | Aprobación del plan de pruebas |
| Semana 9 | Pruebas funcionales y de regresión de la fase 1: catálogo, inventario y sensores | Cierre de la fase 1 (Administrador) |
| Semana 10 | Pruebas de la fase 2: login, perfil, home e historial; regresión del recomendador | Cierre de la fase 2 (Cliente) |
| Semana 11 | Pruebas de la fase 3 (ventas) y del punto de entrada de la fase 4 (IoT) | Desarrollo de las fases 3 y 4 |
| Semana 12 | Pruebas extremo a extremo con Cypress, regresión total, cierre y entrega de evidencias | Fases 3 a 6 completadas |

## Premisas

- El equipo cuenta con disponibilidad semanal y con acceso permanente al repositorio del proyecto.
- El entorno local (XAMPP con Apache, MySQL y PHP 8.2) se mantiene operativo durante la ejecución de las pruebas.
- Se respeta el orden de fases establecido (Administrador, Cliente, Vendedor, IoT y Notificaciones); no se prueban funcionalidades que aún no existen.
- El módulo de recomendación está completo y no se modifica sin aviso previo, salvo corrección de defectos.
- Las pruebas se ejecutan con datos sintéticos generados por factores, sin usar información personal real de clientes.
- No se dispone de servidores dedicados ni de presupuesto: todo el esfuerzo es local y académico.
- El equipo domina Laravel y puede aplicar la capacitación mínima descrita en la sección de entrenamiento.

## Dependencias y Riesgos

Dependencias principales del proceso de pruebas de software:

- Dependencias con el desarrollo: cada fase debe estar implementada antes de poder probarse, por lo que el avance del código es la restricción dominante.
- Dependencias con otros trabajos del ciclo: entregas académicas simultáneas que reducen la disponibilidad del equipo.
- Disponibilidad de recursos: un único equipo local por integrante, sin servidores de pruebas ni licencias. Shah et al. (2025) advierten que los costos iniciales de la automatización (herramientas, marcos de prueba y capacitación) pueden ser un obstáculo, en especial para organizaciones pequeñas o con presupuestos limitados, y proponen combinar pruebas manuales y automatizadas para optimizar los recursos (pp. 9-10).
- Restricciones de tiempo: el cronograma está atado al calendario del ciclo académico.
- Premisas que pueden no cumplirse: cambios de alcance o modificaciones no notificadas en módulos ya cerrados. Este riesgo es relevante porque el mantenimiento de los scripts automatizados exige un esfuerzo considerable cuando la aplicación cambia con frecuencia (Shah et al., 2025, p. 10).

Los riesgos se clasifican según su probabilidad e impacto; cada uno de ellos cuenta con un plan de mitigación o de contingencia:

| Riesgo | Probabilidad | Impacto | Plan de mitigación o contingencia |
|---|---|---|---|
| Retraso en el desarrollo de las fases 3 a 5 | Media | Alto | Mantener la versión 1.0 del plan acotada a las fases 1 y 2 e actualizarla por cada fase cerrada |
| Cambios no notificados en el recomendador rompen las pruebas | Alta | Medio | Congelar el módulo completado y ejecutar la suite completa después de cada cambio |
| Fallo del entorno local (Apache, MySQL o PHP) | Media | Alto | Ejecutar las pruebas sobre SQLite en memoria y documentar la restitución del entorno |
| Cobertura insuficiente de pruebas manuales por falta de tiempo | Media | Medio | Priorizar las rutas críticas: login, CRUD de plantas y recomendaciones |
| Ausencia de datos reales de sensores y del ESP32 | Alta | Medio | Simular lecturas con datos de prueba en la fase 4 antes de conectar el hardware (Ferreira et al., 2023, pp. 114-115) |

Respecto al riesgo de ausencia de datos reales de sensores y del ESP32, la mitigación propuesta se apoya en la literatura de pruebas IoT: Ferreira et al. (2023) plantean que este contexto demanda etapas previas de prueba en simuladores o emuladores que mitiguen el riesgo de mal funcionamiento del sistema (p. 114), y organizan la validación de modo que las fases iniciales reduzcan costos y esfuerzos, dejando para las finales los entornos más cercanos al de producción (p. 115). Asimismo, la mayor parte de los trabajos que revisan se concentran en la etapa de validación, principalmente por emulación o simulación y por testbed (pp. 117-118).

## Referencias

Referencias consultadas para la elaboración de este plan:

- CLAUDE.md del repositorio: contexto, alcance por fases y convenciones del proyecto Adaptia.
- docs/modelo-bd.md: modelo de datos completo en PlantUML, referencia obligatoria antes de modificar migraciones.
- Plantilla de Plan de Pruebas de Software, material de la unidad de Pruebas y Calidad de Software de la Universidad Continental.
- Formato 06 Requerimientos funcionales (RF-08, RF-09, RF-10 y RF-12).
- Código fuente del repositorio: app/Services, app/Http/Controllers, routes/web.php y tests/.
- Documentación oficial de Laravel 12 y de Pest, referencias técnicas de la estrategia de pruebas.
- Ferreira, V. G., Herrera, C. G., Souza, S. R. S., Santos, R. R. dos, & Souza, P. S. L. de. (2023). Software testing applied to the development of IoT systems: Preliminary results. En Proceedings of the 8th Brazilian Symposium on Systematic and Automated Software Testing (SAST 2023) (pp. 113-122). ACM. https://doi.org/10.1145/3624032.3624049
- Shah, K. N., Katru, C. R., & Gami, S. J. (2025). Redefining software quality assurance: A deep dive into automated testing strategies. The Review of Contemporary Scientific and Academic Studies, 5(3). https://doi.org/10.55454/rcsas.5.03.2025.002

## Glosario

- **Adaptia:** sistema web de recomendación, monitoreo IoT y gestión de plantas ornamentales desarrollado para GardenLand.
- **Prueba unitaria:** verifica una unidad de código en aislamiento, sin base de datos ni interfaz.
- **Prueba de integración (feature):** verifica la interacción entre el código y la base de datos dentro del ciclo de trabajo de Laravel.
- **Pest:** framework de pruebas basado en PHPUnit que se utiliza con la sintaxis expect().
- **Suite:** conjunto de pruebas automatizadas que se ejecutan con un solo comando (composer test).
- **Regresión:** reejecución de pruebas ya aprobadas para comprobar que un cambio no rompió lo existente.
- **Caso de prueba:** descripción de la entrada, los pasos y el resultado esperado de una prueba.
- **Defecto o bug:** discrepancia entre el resultado esperado y el resultado obtenido.
- **RF:** requisito funcional; por ejemplo RF-10 (historial de alertas del cliente) y RF-12 (alerta de stock bajo).
- **Score de compatibilidad:** valor entre 0 y 1 que indica qué tan adecuada es una planta para el perfil del cliente.
- **Filtro eliminatorio:** condición dura que descarta plantas antes de puntuar, por ejemplo la toxicidad cuando hay mascotas o niños.
- **Factory:** clase de Laravel que genera datos de prueba sintéticos.
- **RefreshDatabase:** trait de Laravel que restaura la base de datos en cada prueba; en este proyecto corre sobre SQLite en memoria.
- **RACI:** matriz de responsabilidades (responsable, aprobador, consultado e informado).
- **E2E (extremo a extremo):** prueba que simula el recorrido completo del usuario, prevista con Cypress.
- **ESP32:** microcontrolador con Wi-Fi que enviará las lecturas de los sensores en la fase de monitoreo IoT.
- **Simulación y emulación (IoT):** en la literatura de pruebas IoT, la simulación es una actividad apoyada en una herramienta que imita el comportamiento de un sistema o dispositivo real para probar su funcionalidad y desempeño en un entorno controlado; la emulación replica la funcionalidad de un sistema o dispositivo real usando sus mismos protocolos e interfaces, y sirve para probar la interoperabilidad y la compatibilidad (Ferreira et al., 2023, p. 117).