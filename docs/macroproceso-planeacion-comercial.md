# Macroproceso: Planeación comercial

## Alcance y carriles

El proceso registra campañas para GardenLand y conserva el flujo del BPMN desde la definición de objetivos hasta la documentación de ajustes para el ciclo siguiente.

| Carril BPMN | Rol Adaptia |
|---|---|
| Responsable Comercial | `vendedor` |
| Producción | `administrador` |
| Administración | `administrador` |

El mapeo se define en `PlaneacionComercialService::ROLES_POR_CARRIL`; no se crean roles nuevos.

## Flujo BPMN y estados

1. **Inicio → Definir objetivos, metas y presupuesto comercial.** El vendedor crea la campaña (`objetivos_definidos`), con objetivos, presupuesto, fechas y metas por planta.
2. **Analizar demanda y oportunidades comerciales.** El vendedor consulta el panel por periodo: ranking de ventas por planta, plantas con stock bajo y plantas sin ventas (`demanda_analizada`).
3. **Identificar productos y servicios a ofrecer.** El vendedor conserva las plantas y cantidades meta en la campaña (`productos_identificados`).
4. **Revisar disponibilidad de productos.** Administración consulta `stock_actual` contra cantidad meta. Si alcanza, el estado pasa a `oferta_disponible`; si no, a `gestion_disponibilidad`.
5. **Gestionar disponibilidad de productos.** Administración registra cada solicitud como `atendida` o `no_lograda`. Para marcar atendida, el stock real debe cubrir la meta.
6. **¿Se logró asegurar la disponibilidad?** Si las solicitudes activas fueron atendidas y el stock sigue siendo suficiente, el proceso vuelve a revisar disponibilidad (`productos_identificados`). Si hay solicitudes no logradas, pasa a `oferta_por_ajustar`.
7. **Ajustar oferta (reducir o descartar productos).** El vendedor modifica metas o quita filas; los productos descartados permanecen registrados con `incluida = false`.
8. **Planificar oferta comercial.** El vendedor solo puede planificar si el nuevo veredicto de disponibilidad es suficiente (`oferta_planificada`). Si no, el estado permanece en ajuste para permitir otra modificación.
9. **Preparar actividades de promoción → Participar en ferias o realizar promoción.** El vendedor registra ambas actividades (`promocion_preparada`, `promocion_realizada`).
10. **Evaluar resultados comerciales vs. metas.** Tras la promoción y una vez pasada `fecha_fin`, Administración calcula y congela unidades vendidas, porcentaje de cumplimiento e ingresos estimados (`resultados_evaluados`).
11. **Documentar ajustes para el siguiente ciclo → Fin.** Administración documenta `ajustes_siguiente_ciclo` y cierra la campaña (`cerrada`). Los resultados quedan disponibles para orientar el siguiente análisis de demanda.

## Actividad → pantalla / servicio / rol

| Actividad | Pantalla | Servicio | Rol |
|---|---|---|---|
| Crear objetivos, metas y presupuesto | `planeacion-comercial.campanas.create` | `PlaneacionComercialService::crear` | Vendedor |
| Analizar demanda y oportunidades | `planeacion-comercial.demanda` | `DemandaComercialService::consultar` | Vendedor |
| Identificar productos | Detalle de campaña | `PlaneacionComercialService::cambiarEstado` | Vendedor |
| Revisar disponibilidad | Panel de productos y disponibilidad | `DisponibilidadCampanaService::verificar` y `PlaneacionComercialService::cambiarEstado` | Administrador |
| Gestionar disponibilidad | Panel de solicitudes | `PlaneacionComercialService::gestionarSolicitud` | Administrador |
| Decidir retorno o ajuste | Panel de solicitudes | `PlaneacionComercialService::cambiarEstado` | Administrador |
| Reducir o descartar oferta | Formulario de oferta en detalle | `PlaneacionComercialService::ajustarOferta` | Vendedor |
| Planificar oferta y preparar promoción | Acciones de etapa en detalle | `PlaneacionComercialService::cambiarEstado` | Vendedor |
| Marcar feria/promoción realizada | Acciones de etapa en detalle | `PlaneacionComercialService::cambiarEstado` | Vendedor |
| Evaluar resultados | Tabla de evaluación en detalle | `EvaluacionCampanaService::evaluar` | Administrador |
| Documentar ajustes y cerrar | Formulario de cierre en detalle | `PlaneacionComercialService::cambiarEstado` | Administrador |

Las pantallas están bajo `/planeacion-comercial`; las acciones de cada estado se ocultan a roles no responsables, y las rutas además protegen el acceso con `auth` y `role:administrador,vendedor`. Los Services vuelven a validar rol y estado.

## Datos y limitaciones

- `campanas_comerciales` guarda código, objetivos, presupuesto, fechas, estado, creador, evaluación y ajustes del siguiente ciclo.
- `productos_campana` relaciona plantas, cantidad meta, descuento planeado y resultado congelado por producto. `incluida` permite descartar sin borrar el rastro.
- `solicitudes_disponibilidad` conserva el faltante, resultado y notas de la gestión. Las entradas de inventario se registran mediante el flujo existente.
- La disponibilidad es una consulta del stock actual: no se reserva stock y puede cambiar si hay ventas o movimientos posteriores.
- La tabla `ventas` no guarda una campaña ni el precio cobrado. La evaluación incluye todas las ventas de las plantas de la campaña en el periodo `[fecha_inicio, fecha_fin]`, por lo que no prueba atribución a la promoción.
- Los ingresos se aproximan como cantidad vendida por el precio actual de la planta. No se puede verificar el precio histórico ni si se aplicó el descuento de campaña.
- La demanda no incluye conteos de especies recomendadas; el recomendador del cliente no se modifica.
