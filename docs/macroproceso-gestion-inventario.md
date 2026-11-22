# Macroproceso: Gestión de inventario

## Alcance y carriles

El proceso controla las existencias de **plantas** (`plantas.stock_actual`) y de **insumos** (`recursos_catalogo` de tipo `material`). Parte de una revisión de existencias frente a una cantidad requerida y termina con el movimiento de inventario registrado y las existencias actualizadas. No se crean roles nuevos.

| Carril BPMN | Rol Adaptia | Actividades |
|---|---|---|
| Almacén | `vendedor` (y `administrador` como supervisor) | Revisar existencias, verificar estado y cantidades, recibir, verificar recepción, registrar ingreso, almacenar, registrar movimiento |
| Compras y administración | `administrador` | Gestionar reposición, realizar compra o abastecimiento, gestionar diferencia o incidencia |

El mapeo está en `GestionInventarioService::ROLES_POR_CARRIL` y `CARRIL_POR_ESTADO`. Las rutas se protegen con `auth` y `role:administrador,vendedor`, y el Service vuelve a validar rol y estado.

## Flujo BPMN y estados

1. **Inicio → Revisar existencias de plantas o insumos.** Se crea la gestión con el producto y la cantidad requerida (`existencias_revisadas`).
2. **Verificar estado y cantidades.** Se registra el conteo físico y lo que está en buen estado.
3. **¿Hay stock suficiente?** El sistema decide: disponible = el menor entre lo que está en buen estado y el stock del sistema.
   - **Sí (`stock_suficiente`) → Registrar movimiento de inventario:** salida por la cantidad requerida → `cerrada` (resultado `stock_suficiente`).
   - **No (`stock_insuficiente`) → Gestionar reposición** (`reposicion_gestionada`): cantidad a reponer (al menos el faltante), proveedor y costo estimado.
4. **Realizar compra o abastecimiento** (`compra_realizada`): orden de compra, fecha y costo total.
5. **Recibir plantas o insumos** (`material_recibido`): cada llegada queda como una recepción.
6. **Verificar recepción → ¿Recepción conforme?**
   - **Sí (`recepcion_conforme`)** si lo aceptado hasta ahora más lo recibido coincide con lo solicitado.
   - **No (`en_incidencia`)**: se registra una incidencia (faltante, exceso, dañado o producto incorrecto) y cuántas unidades se aceptan de esa recepción.
7. **Gestionar diferencia o incidencia** (`incidencia_gestionada`) y **volver a recibir** (paso 5). El ciclo se repite hasta que la recepción sea conforme.
8. **Registrar ingreso** (`ingreso_registrado`): total aceptado y documento de ingreso opcional.
9. **Almacenar u organizar** (`almacenado`): ubicación.
10. **Registrar movimiento y actualizar existencias → Fin:** entrada por lo ingresado → `cerrada` (resultado `reposicion_completada`).

## Actividad → pantalla / servicio / rol

| Actividad | Ruta | Servicio | Rol |
|---|---|---|---|
| Revisar existencias | `gestion-inventario.create` / `store` | `GestionInventarioService::crear` | Vendedor o administrador |
| Verificar estado y cantidades | `gestion-inventario.verificar` | `verificarEstado` (usa `SuficienciaInventarioService::evaluar`) | Vendedor o administrador |
| Gestionar reposición | `gestion-inventario.reposicion` | `gestionarReposicion` | Administrador |
| Realizar compra o abastecimiento | `gestion-inventario.compra` | `registrarCompra` | Administrador |
| Recibir plantas o insumos | `gestion-inventario.recepcion` | `registrarRecepcion` | Vendedor o administrador |
| Verificar recepción | `gestion-inventario.recepcion.verificar` | `verificarRecepcion` (usa `evaluarRecepcion` y `clasificarIncidencia`) | Vendedor o administrador |
| Gestionar diferencia o incidencia | `gestion-inventario.incidencia` | `gestionarIncidencia` | Administrador |
| Registrar ingreso | `gestion-inventario.ingreso` | `registrarIngreso` | Vendedor o administrador |
| Almacenar u organizar | `gestion-inventario.almacenar` | `almacenar` | Vendedor o administrador |
| Registrar movimiento y actualizar existencias | `gestion-inventario.movimiento` | `registrarMovimiento` → `MovimientoGestionInventarioService::registrar` | Vendedor o administrador |

Las pantallas están bajo `/gestion-inventario` (bandeja con filtros, formulario de revisión y detalle). El detalle muestra un bloque «Estado del proceso» con un mensaje simple de en qué punto está la gestión y un bloque «Siguiente paso» con el formulario que corresponde. Los mensajes están en `GestionInventarioService::MENSAJES_ESTADO`. El menú lateral incluye «Gestión de inventario» e «Insumos» para ambos roles.

## Catálogo de insumos

Pantalla `/insumos` (mismo estilo que Plantas): lista, crear, editar y eliminar insumos, con alerta de stock bajo. Trabaja sobre `recursos_catalogo` filtrando `tipo = material`, así que los insumos creados aquí son los mismos que se eligen en la gestión de inventario y en mantenimiento.

- El código (`M-001`, `M-002`…) se genera automáticamente al crear.
- No se puede eliminar un insumo que ya tenga requisiciones, movimientos o gestiones de inventario.
- En el bloque de stock bajo, el botón «Revisar existencias» abre la nueva revisión con el insumo ya seleccionado.
- Rutas `insumos.*`; servicio `InsumoService`; controlador `InsumoController`.

## Datos

- `gestiones_inventario`: una fila por gestión, con el estado y los datos de cada etapa (verificación, reposición, compra, ingreso, ubicación, resultado).
- `recepciones_inventario`: una fila por llegada de material, con lo recibido, lo aceptado y si fue conforme.
- `incidencias_inventario`: diferencias detectadas al verificar una recepción, con su resolución.
- `movimientos_gestion_inventario`: rastro del movimiento (stock antes y después) para plantas e insumos.

## Reglas y decisiones

- **Plantas** reutilizan `MovimientoInventarioService`: el movimiento aparece también en el módulo de Inventario y se aplican sus advertencias de stock bajo. Las cantidades deben ser enteras.
- **Insumos** actualizan `recursos_catalogo.stock_actual` directamente, porque `movimientos_material` exige un caso de mantenimiento. Aceptan hasta dos decimales.
- Una recepción solo puede marcarse conforme si lo aceptado acumulado coincide exactamente con lo solicitado; de lo contrario se debe registrar la incidencia.
- La disponibilidad no reserva stock: si el stock baja entre la verificación y el registro de la salida, el movimiento se rechaza con un mensaje y la gestión no cambia.
- El proceso con reposición termina con la entrada al inventario; no registra automáticamente la salida del requerimiento original.
- La diferencia entre el conteo físico y el stock del sistema se muestra pero no genera un ajuste de inventario.

## Pruebas

`tests/Unit/SuficienciaInventarioServiceTest.php` y `tests/Unit/GestionInventarioTransicionesTest.php` cubren los cálculos puros y la máquina de estados. `tests/Feature/GestionInventarioFlujoTest.php` cubre los flujos completos (con y sin reposición, con incidencias), carriles, validaciones y errores. `tests/Feature/GestionInventarioRutasTest.php` cubre rutas, permisos, vistas en todos los estados y el flujo por HTTP. `tests/Feature/InsumoTest.php` cubre el catálogo de insumos y su conexión con la gestión de inventario.
