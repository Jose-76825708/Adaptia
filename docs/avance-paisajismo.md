# Avance de Paisajismo

## 1. Reglas no negociables
- Todos los cambios de estado pasan por ProyectoPaisajismoService::cambiarEstado.
- No se modifica el flujo de mantenimiento, CRUD de plantas, inventario de plantas ni monitoreo IoT.
- Ausente = NULL; sin casts array/json; 2 decimales; $fillable explícito.
- Ningún dato sensible se toma del request; solicitante, autor y estado son controlados por el backend.
- Migraciones nuevas con fecha posterior a la última y FK explícitas.
- No se ejecutan migrate, migrate:fresh, db:seed ni storage:link.
- Tests nuevos: TDD, sin fechas literales fuera de $hoy, sin GD ni rutas con backslash.
- Vistas en resources/views/paisajismo con prefijos dis-, ins- y ent-.

## 2. Plan maestro resumido
- INT-025: solicitud y registro del proyecto.
- INT-026: diseño y cotización.
- INT-027: instalación y avances.
- INT-028: entrega, satisfacción y mantenimiento posterior.

## 3. Bitácora
| Bolt | Estado | Archivos | Tests | Resultado |
|---|---|---|---|---|
| INT-025-BOLT-001 | verde | app/Services/ProyectoPaisajismoService.php | 2 pruebas / 11 assertions | Normalización y validación puras. |
| INT-025-BOLT-002 | verde | migración proyectos_paisajismo; modelo; factory; Service abrir/estado | 3 pruebas / 11 assertions | Proyecto creado solicitado y matriz de estados probada. |
| INT-025-BOLT-003 | verde | controller cliente; vistas create/index; rutas; enlace home; test feature | 3 pruebas / 12 assertions | Solicitud del cliente, aislamiento, paginación y validación. |
| INT-025-BOLT-004 | verde | controller admin; vistas index/show; rutas; sidebar; test feature | 3 pruebas / 7 assertions | Filtros, detalle, nulos y acceso administrativo. |
| INT-026-B001 | verde | app/Services/DisenoProyectoService.php | 3 pruebas / 7 assertions | Líneas, duplicados y cotización puros. |
| INT-026-B002 | verde | cotización integrada en el Service | cubierto por B001 | Tarifa, costos y aviso de presupuesto. |
| INT-026-B003 | verde | migraciones propuestas_diseno/propuesta_plantas; modelos/factories; guardarPropuesta | 2 pruebas / 5 assertions | Versión, snapshot y transacción. |
| INT-026-B004 | verde | registrarDecision en DisenoProyectoService | 1 prueba / 2 assertions | Aprobar/rechazar y transición. |
| INT-026-B005 | verde | composer diseno; controller; rutas; panel; vista propuesta | 2 pruebas / 4 assertions | Panel, 10 filas, decisión y vista cliente. |
| INT-027-B001 | verde | app/Services/InstalacionProyectoService.php | 2 pruebas / 5 assertions | Disponibilidad suficiente/insuficiente. |
| INT-027-B002 | verde | iniciarInstalacion y MovimientoInventarioService | 2 pruebas / 4 assertions | Estado y stock transaccionales. |
| INT-027-B003 | verde | migración/modelo/factory avances; registrarAvance | 3 pruebas / 11 assertions | Porcentajes crecientes y persistencia. |
| INT-027-B004 | verde | composer; controller; rutas; panel instalacion | 2 pruebas / 5 assertions | Disponibilidad, iniciar y avances. |
| INT-028-B001 | verde | migración/modelo/factory EntregaProyecto; registrarEntrega | 2 pruebas / 7 assertions | Entrega en 100 y estado entregado. |
| INT-028-B002 | verde | registrarSatisfaccion | 2 pruebas / 7 assertions | Calificación una sola vez. |
| INT-028-B003 | verde | compilarResumen en EntregaProyectoService | cubierto por persistencia | Contrato completo. |
| INT-028-B004 | verde | panel entrega; composer; controller; rutas; resumen cliente | 2 pruebas / 9 assertions | Panel, resumen y satisfacción. |
| INT-028-B005 | verde | mantenimiento posterior desde proyecto entregado | test feature | Abre CasoMantenimiento por solicitante. |

## Correcciones tras verificación manual
| Punto | Estado | Archivos | Tests |
|---|---|---|---|
| A Cliente detalle/enlaces | verde | PaisajismoSolicitudController; solicitudes/index/show/propuesta; rutas | CorreccionesPaisajismoClienteTest |
| B old en paneles | verde | paneles diseno/instalacion/entrega y controllers | CorreccionesPaisajismoOldTest |
| C historial propuestas | verde | DisenoProyectoComposer; panel diseno | CorreccionesPaisajismoHistorialTest |
| D entrega solo a 100 | verde | EntregaProyectoComposer; panel entrega | CorreccionesPaisajismoEntregaAvanceTest |
| Dashboard administrador | verde | DashboardOperativoService; dashboard/administrador; DashboardPaisajismoTest | Resumen solicitado/propuesto/aprobado/en instalación/entregado |
