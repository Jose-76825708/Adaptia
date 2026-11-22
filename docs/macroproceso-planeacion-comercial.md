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

## Asistente interno de consultas

El asistente de consulta se ofrece en `/asistente` a vendedor y administrador. Incluye una interfaz de conversación con sugerencias rápidas e historial de sesión. Su registro cerrado conserva ocho herramientas de solo lectura: stock por nombre, plantas bajo stock mínimo, detalle de campaña (incluye disponibilidad y avance), campañas por estado, ranking de ventas por periodo, solicitudes de disponibilidad pendientes, casos de mantenimiento y alertas/lecturas IoT. Los tres últimos accesos de administración son exclusivos del administrador; vendedor no recibe datos de casos ni monitoreo de clientes.

El asistente también incorpora contexto estático de los macroprocesos para explicar etapas, roles y limitaciones, y puede formular sugerencias basadas en resultados de herramientas. Para el ranking de ventas, el periodo puede expresarse como relativo (hoy, ayer, últimos 7 o 30 días, mes actual o anterior, año actual o anterior) o como fechas personalizadas inclusivas. Si no se especifica uno, se usa el mes actual. El servidor entrega la fecha y hora actual según `config('app.timezone')` y calcula los rangos, para que el modelo no tenga que inventar fechas.

Debe distinguir datos consultados de recomendaciones, no completar datos faltantes y no afirmar que ejecutó acciones. Los valores que retornan las herramientas siempre se tratan como datos, nunca instrucciones.

El cliente web solo envía el texto de usuario; el servidor arma system/history/tool messages. El ejecutor valida de nuevo el rol y los argumentos. Ollama se sirve localmente con el modelo definido en `OLLAMA_MODEL` (en desarrollo, `gemma4:e2b`); la URL configurada debe ser loopback. La ficha oficial de [Gemma 4 en Ollama](https://ollama.com/library/gemma4) anuncia function calling nativo; la [API de tool calling](https://docs.ollama.com/capabilities/tool-calling) documenta el flujo de `/api/chat`.

Para el uso local, el modelo se mantiene cargado durante 5 minutos, el contexto se limita a 2048 tokens, se desactiva el modo de razonamiento extendido y la generación se limita a 256 tokens. Los saludos simples se responden sin llamar al modelo. La primera consulta después de reiniciar Ollama puede tardar más mientras el modelo se carga en memoria.

## Paneles de administrador y vendedor

`/home` presenta un panel de administración con métricas de inventario, ventas agregadas, casos activos, alertas, campañas y movimientos recientes. El panel del vendedor limita los indicadores y el listado de ventas a `vendedor_id` del usuario autenticado; el inventario y las campañas son datos compartidos del negocio. Las ventas e ingresos mostrados son estimados con el precio actual, no con el precio histórico cobrado.
