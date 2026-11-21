
# DOCUMENTO DE REQUERIMIENTOS FUNCIONALES (RF) - PROYECTO ADAPTIA

**Proyecto:** Adaptia — Sistema Inteligente de Recomendación y Telemetría IoT para Plantas
**Arquitectura Base:** Backend en Laravel (MVC) + Capa IoT Embebida (ESP32)
**Entorno de Referencia Comercial:** Catálogo GardenLand

## 1. INTRODUCCIÓN Y ALCANCE DEL SISTEMA

El sistema Adaptia integra dos módulos principales desacoplados:

- **Módulo Recomendador Botánico:** Algoritmo de filtrado y puntuación ponderada basado en las preferencias reales del consumidor y condiciones ambientales de la vivienda12.
- **Módulo de Telemetría IoT en Tiempo Real:** Red de monitoreo con microcontroladores ESP32 y sensores ambientales/hídricos que evalúan continuamente la salud de la planta en el hogar34.

## 2. MÓDULO 1: RECOMENDADOR BOTÁNICO Y SELECCIÓN INTELIGENTE (BACKEND LARAVEL)

### RF-01: Captura de Perfil del Usuario y Espacio Doméstico

**Descripción:** El sistema debe proporcionar un formulario web interactivo que permita al usuario ingresar los parámetros ambientales y de espacio de su hogar: nivel de iluminación (baja, media, alta), espacio disponible, propósito (decoración, purificación de aire), presencia de niños o mascotas, y nivel de experiencia en jardinería2more_horiz.

**Entradas:** Selección mediante listas desplegables y casillas de verificación.

**Criterio de Aceptación:** Los datos capturados deben guardarse en el modelo PerfilUsuario de Laravel para su procesamiento en el motor recomendador78.

### RF-02: Filtro Eliminatorio de Seguridad (Toxicidad e Incompatibilidad Lumínica)

**Descripción:** Antes de calcular el score de coincidencia, el sistema debe aplicar filtros excluyentes estrictos sobre el catálogo de GardenLand:

- Si se registra la presencia de niños o mascotas, el sistema excluye automáticamente las especies clasificadas como tóxicas26.
- Si la luz disponible en la habitación del usuario es menor al requerimiento mínimo de la planta, la especie es descartada26.

**Sustento Científico:** Excluir especies no viables o peligrosas reduce la mortandad vegetal y responde a que el 60% de los cuidadores sufre ansiedad por no saber si su planta recibe suficiente luz9.

### RF-03: Ranking y Scoring Ponderado Multicriterio

**Descripción:** Para las plantas que superan los filtros eliminatorios, el sistema debe calcular una puntuación de compatibilidad (de 0% a 100%) ordenando los resultados de mayor a menor coincidencia mediante la siguiente función de utilidad ponderada:

$$\text{Score} = (w_1 \cdot \text{Precio}) + (w_2 \cdot \text{Cuidado}) + (w_3 \cdot \text{Color}) + (w_4 \cdot \text{Tamaño})$$

Donde los pesos son: Precio ($w_1 = 40.55\%$), Dificultad de Cuidado ($w_2 = 24.99\%$), Color ($w_3 = 20.43\%$) y Tamaño ($w_4 = 9.62\%$)1more_horiz.

**Sustento Científico:** Basado en los coeficientes de importancia relativa obtenidos mediante Análisis Conjunto en SPSS, donde un precio accesible (< Rp. 100,000) y un mantenimiento fácil aportan las mayores utilidades al comprador (+0.547 y +0.419 respectivamente)1more_horiz.

### RF-04: Recomendación Basada en 4 Variables Ambientales Básicas (Sin NPK)

**Descripción:** El motor recomendador en Laravel debe realizar el cálculo de coincidencias requiriendo únicamente 4 entradas ambientales de entorno (temperatura, humedad relativa, pH e iluminación/lluvia), prescindiendo intencionalmente de lecturas de macronutrientes del suelo (Nitrógeno, Fósforo y Potasio)14more_horiz.

**Sustento Científico:** Se demostró formalmente que omitir las variables N, P y K del dataset de entrenamiento no afecta la capacidad predictiva, alcanzando una exactitud de validación del 93.64% y un F1-Score del 94.30% mediante redes neuronales convolucionales 1D con optimizador Adagrad14more_horiz.

### RF-05: Emparejamiento por Nivel de Experiencia ("Plant Parents")

**Descripción:** Cuando el usuario se registre como "principiante" o declare haber tenido fracasos previos con plantas, el sistema debe filtrar el catálogo asignando la máxima prioridad a especímenes de alta resistencia (Easy Care) y adjuntar guías básicas de supervivencia1318.

**Sustento Científico:** Responde al hallazgo de que el 70% de los jóvenes se considera "plant parent", pero el 67% admite que el cuidado es un reto mayor al esperado y un 22% siente temor de comprar plantas por haber matado alguna en el pasado (7 plantas muertas en promedio por persona)1819.

## 3. MÓDULO 2: TELEMETRÍA IOT Y MONITOREO EN TIEMPO REAL (ESP32)

### RF-06: Recepción y Registro de Telemetría Multisensor

**Descripción:** La API REST de Laravel recibe lecturas JSON mediante `POST /api/lecturas`. El sensor se autentica con el encabezado `X-Sensor-Token`; el token se valida contra el hash almacenado y el sensor debe estar activo. El payload implementado contiene `planta_vendida_id`, `humedad_suelo`, `temperatura`, `humedad_ambiental`, `luz` y `fecha_hora`. La unidad indicada debe corresponder al sensor autenticado.

**Criterio de Aceptación:** Cada lectura válida se guarda en `lecturas_sensores`, asociada a una unidad de planta vendida (`planta_vendida_id`) y su sensor asignado. La implementación actual y sus pruebas están en `routes/api.php`, `StoreLecturaSensorRequest` y `LecturaSensorApiTest`.

### RF-07: Evaluación del umbral hídrico y generación de alerta de riego

**Descripción:** El backend compara la humedad del suelo recibida con los límites mínimo y máximo configurados para la especie. Una lectura por debajo del mínimo genera una alerta informativa de tipo `riego`, visible en el historial/dashboard del cliente. El sistema no ejecuta riego ni cambia un estado físico de la planta.

**Sustento Científico:** Elimina la incertidumbre del agua, resolviendo el factor de ansiedad por riego presente en el 56% de los consumidores49.

### RF-08: Evaluación de rangos de microclima y humedad ambiental

**Descripción:** El backend compara temperatura, humedad ambiental e iluminación con los rangos mínimo y máximo configurados para la especie. Cuando un valor está fuera de rango, genera una alerta informativa `cuidado_planta` con la variable, medición, rango esperado y dirección de la desviación. La presentación muestra alertas activas; no se encontró un indicador persistido de estado "Todo Bien" ni una alerta específica de reubicación.

**Sustento Científico:** Basado en la arquitectura multisensor del Smart Plant Assistant (ESP32 con DHT22 y BH1750)420 y el sistema IoT con interfaz Blynk324.

### RF-09: Generación de Alertas Informativas de Monitoreo

**Descripción:** Adaptia debe generar alertas informativas cuando una lectura de la planta se encuentre fuera de los rangos de referencia configurados para su especie. Una humedad del suelo por debajo del mínimo genera una alerta de tipo "riego"; las demás condiciones fuera de rango generan una alerta de tipo "cuidado_planta". Cada alerta debe identificar la variable afectada, el valor medido, el rango de referencia y si la medición está por debajo o por encima del rango. No se incluye el control de relés, bombas, electroválvulas ni la ejecución de riego automático.

**Criterio de aceptación:** Para cada unidad y variable solo debe existir una alerta abierta por episodio fuera de rango. Las lecturas que mantengan la condición no duplican la alerta; una lectura de recuperación la resuelve y, si la variable vuelve a salir del rango, se genera un nuevo episodio.

**Alcance excluido:** El sistema no activa actuadores ni controla físicamente el riego.

## 4. MÓDULO 3: GESTIÓN DE CATÁLOGO Y TRAZABILIDAD RELACIONAL (GARDENLAND)

### RF-10: Vista de historial/alertas del cliente

**Descripción:** El sistema debe proporcionar una vista accesible para el cliente autenticado donde pueda consultar el historial de alertas generadas por el monitoreo IoT de sus plantas. Cada alerta debe incluir su categoría (riego, cuidado_planta o stock_bajo), la variable afectada, la medición y el rango de referencia, fecha y hora, y su estado (leída/no leída y activa/resuelta). Las alertas son informativas y no activan actuadores físicos.

**Entradas:** N/A (vista de solo lectura).

**Criterio de Aceptación:** La vista debe cargar correctamente para clientes autenticados y mostrar las alertas asociadas a su usuario mediante el relacionamiento con las tablas de alertas y notificaciones.

### RF-11: Mapeo por Identificador Único de Planta (planta_id)

**Descripción:** El backend relaciona cada lectura con una unidad de planta vendida (`planta_vendida_id`) y el sensor autenticado, vinculando la planta, su propietario y el historial de telemetría. La asociación lógica está implementada y probada; la identificación física de la maceta mediante etiqueta/código y su operación desde firmware ESP32 no están verificadas.

**Sustento Científico:** Sigue los principios de trazabilidad individual por código o etiqueta probados en inventariado agrícola automatizado2628.

### RF-12: Alerta de stock bajo por especie

**Descripción:** El sistema debe generar una alerta cuando el stock actual de una especie en el inventario sea menor al stock mínimo definido para esa especie. La alerta debe ser persistente y notificada al administrador mediante la vista correspondiente, y debe poder marcarse como leída.

**Entradas:** Datos de movimientos de inventario que afecten el stock_actual de una planta.

**Criterio de Aceptación:** El sistema debe verificar automáticamente el stock tras cada movimiento de inventario (entrada/salida) y disparar una alerta visual y/o log cuando stock_actual < stock_minimo.

### RF-13: Validaciones API Form Request y Código HTTP 422

**Descripción:** La API `POST /api/lecturas` valida la estructura del JSON entrante mediante `StoreLecturaSensorRequest`. Si falta un campo obligatorio, la fecha no cumple el formato admitido o una medición queda fuera del rango físico, Laravel rechaza la petición con HTTP 422 Unprocessable Entity. Las credenciales de sensor ausentes o inválidas se rechazan con HTTP 401 antes de validar el payload.

**Sustento Científico:** Previene la contaminación de la base de datos ante ruidos o interferencias en las transmisiones inalámbricas de campo28.

### RF-14: Detección de Nodos Offline (>24 Horas)

**Descripción:** Un proceso programado en segundo plano (Cron Job) debe verificar periódicamente las marcas de tiempo de la telemetría. Si un nodo ESP32 no transmite datos durante más de 24 horas continuas, la maceta se marca como "Offline" y se notifica al usuario2628.

**Sustento Científico:** Garantiza la integridad del monitoreo detectando fallas de batería, desconexiones Wi-Fi o fallas en el sensor2628.

### RF-15: Normalización de Solicitudes de Mantenimiento

**Descripción:** El sistema debe normalizar toda solicitud de mantenimiento recibida en formato libre o semi estructurado para convertirla en un contrato estándar de entrada. La normalización aplica limpieza textual, filtrado de claves no permitidas, manejo de fechas y texto de observaciones, sin depender de autenticación, base de datos ni fecha actual del sistema. La salida debe ser determinista, idempotente y reutilizable por el siguiente bolt del proceso de mantenimiento.

**Entradas:** Arreglo asociativo con área verde, descripción de la solicitud, fecha deseada y observaciones (texto). El solicitante no forma parte de la entrada: lo aporta la sesión autenticada al abrir el caso. Se ignoran claves ajenas al contrato: user_id, estado, id, created_at, updated_at, etc.

**Criterio de Aceptación:** El servicio devuelve siempre un arreglo con exactamente las claves permitidas: `area_verde`, `descripcion`, `fecha_deseada` y `observaciones`. Los textos se limpian con trim y colapso de espacios; los vacíos se convierten en `null`; las fechas se aceptan en formato `Y-m-d` y `d/m/Y` y se devuelven en `Y-m-d` cuando son válidas; si la fecha no es interpretada, se conserva como texto limpio sin convertir; las observaciones son un único texto (máximo 500 caracteres, regla que aplica la validación); si se recibe algo que no es texto, queda en null; las claves no permitidas se descartan; la función es idempotente y sin efectos secundarios.

**Criterio de Exposición al Cliente (Bolt 005):** El cliente autenticado debe poder abrir una solicitud de mantenimiento desde la web y consultar únicamente sus propias solicitudes. La ruta de creación permite registrar `area_verde`, `descripcion`, `fecha_deseada` y `observaciones`; la validación del backend se conserva y cualquier error devuelve el formulario con los datos antiguos. El listado del cliente muestra sus casos ordenados por fecha de creación más reciente, sin mezclar información de otros usuarios. La ruta no expone detalle administrativo ni lógica del backend fuera del flujo del cliente.

**Criterio de Gestión Administrativa (Bolt 006):** El administrador autenticado debe poder consultar la bandeja de casos de mantenimiento de los clientes, filtrarlos por estado, urgencia, tipo y búsqueda libre, y abrir el detalle del caso sin cambiar su estado ni su clasificación. La bandeja conserva el orden estable por fecha de creación más reciente y por id como desempate, con paginación y filtros persistidos. El detalle muestra los datos del caso y del solicitante, y maneja correctamente los valores nulos de observaciones o fecha deseada, sin renderizar `[]` ni vacíos no esperados. Todo texto de cliente se renderiza escapado por Blade y no se admite HTML incrustado.

### RF-18: Ingesta y compila evidencia por caso de mantenimiento

**Descripción:** El sistema debe registrar y consolidar la evidencia asociada a cada caso de mantenimiento para soportar la evaluación técnica posterior. La evidencia puede ser de tipo nota o foto y debe quedar vinculada al caso y al autor que la registra. Además, el sistema debe compilar un paquete de contexto con la información base del caso, la evidencia registrada y las lecturas del monitoreo ya existentes para una ventana de 7 días, sin evaluar ni diagnosticar rangos ni cambiar el estado del caso.

**Entradas:** Un caso de mantenimiento existente, un autor autenticado, una nota de texto o una foto válida, y la fecha de referencia opcional para la ventana de lecturas.

**Criterio de Aceptación:** El servicio debe permitir registrar notas con longitud entre 3 y 1000 caracteres y guardar valores nulos para contenido opcional. Debe aceptar fotos jpg, jpeg, png y webp de hasta 5 MB y máximo 10 por caso, guardándolas en el disco público bajo `evidencias/{caso_id}/` con nombre hash generado por Laravel. Si el caso está cerrado, la evidencia se rechaza con un mensaje en español y no se guarda ni archivo ni fila. El paquete compilado debe incluir siempre `caso`, `evidencias` y `sensores`, con la evidencia ordenada por fecha y la sección de sensores compuesta por lecturas ya existentes dentro de la ventana de 7 días. La compilación no evalúa rangos, no genera alertas ni cambia el estado del caso, y la lectura de sensores sigue siendo una consulta de lectura sin duplicación ni copia de datos.

**Criterio de Diagnóstico (Bolt 002):** El evaluador puro debe comparar cada variable con los rangos de la planta asociada y omitir las variables que no tengan ambos límites. Un promedio fuera de rango genera severidad alta 3; mínimo o máximo fuera genera media 2; última lectura fuera con promedio dentro genera tendencia media 2; 2 variables altas de una misma planta generan riesgo alto 3; 2 o 3 alertas iguales generan reincidencia media y 4 o más alta 3.

**Criterio de Diagnóstico (Bolt 003):** El resultado debe ser estructurado y contener severidad global, hallazgos, variables afectadas y justificación. Las palabras críticas urgente, peligro, riesgo, se muere, caído y desplomado cuentan las ocurrencias en notas y solicitud: 1 o 2 generan hallazgo medio y 3 o más alto. Sin hallazgos se genera un hallazgo texto baja con "Sin hallazgos con la evidencia disponible". La severidad global suma los puntos de hallazgo: alta desde 6, media de 3 a 5 y baja hasta 2.

**Criterio de Persistencia (Bolt 004):** El diagnóstico se guarda como una versión nueva y sus hallazgos como filas hijas. Solo el estado abierto puede avanzar a diagnosticado mediante `CasoMantenimientoService::cambiarEstado`; reevaluar un caso diagnosticado crea una versión nueva sin cambiar el estado. Los estados cerrado y planificado posterior rechazan reevaluación y todo cambio queda dentro de una transacción.

**Criterio de Interfaz (Bolt 005):** El panel diagnóstico se incluye dentro del detalle del caso sin modificar su vista contenedora. Debe mostrar formularios accesibles para nota y foto, el botón de evaluación, el diagnóstico vigente y la evidencia registrada; toda salida textual se escapa y las fotos usan texto alternativo.

### RF-19: Plan de mantenimiento derivado del diagnóstico

**Descripción:** El sistema debe derivar tareas de mantenimiento desde el diagnóstico vigente, ordenarlas de forma determinista, generar checklist y persistir una versión de plan asociada al caso. El plan puede regenerarse mientras el caso esté diagnosticado o planificado y no cambia el estado hasta el primer cierre de generación aprobado.

**Entradas:** Caso existente con diagnóstico vigente, tipo de mantenimiento, severidad, variables afectadas y catálogo de recursos disponibles.

**Criterios por Bolt:**
- **Bolt 001:** Las plantillas de tareas son constantes por tipo de mantenimiento y una severidad alta eleva todas las prioridades un nivel; las variables afectadas agregan al final una tarea de seguimiento sin recursos.
- **Bolt 002:** Las tareas se ordenan respetando dependencias y luego por prioridad, criticidad y código estable.
- **Bolt 003:** Cada tarea tiene entre dos y tres ítems de checklist; el seguimiento crea un ítem por variable única.
- **Bolt 004:** El plan se persiste en versión nueva junto a tareas, dependencias, recursos y checklist; el caso pasa de diagnosticado a planificado solo mediante `CasoMantenimientoService::cambiarEstado`. Un código de recurso ausente falla sin guardar.
- **Bolt 005:** El panel plan muestra las tareas vigentes y sus datos, sin checklist, usando ids con prefijo `plan-` y error bag propio.

### RF-20: Disponibilidad de recursos y veredicto de ejecución

**Descripción:** El sistema debe consultar herramientas y materiales del catálogo, agregarlos según las tareas del plan y emitir un veredicto sin persistirlo.

**Criterios por Bolt:**
- **Bolt 001:** Una herramienta está disponible si su stock actual alcanza el máximo requerido por las tareas.
- **Bolt 002:** Los materiales suman cantidad requerida por código y muestran faltante como máximo requerido menos disponible.
- **Bolt 003:** El estado es bloqueado si falta cualquier herramienta, faltan si solo faltan materiales y ejecutable si todo alcanza.
- **Bolt 004:** El panel recursos muestra requerido, disponible, faltantes y el veredicto vigente sin una ruta POST de verificar.

### RF-21: Abastecimiento y preparación del caso

**Descripción:** El sistema debe generar requisiciones de materiales faltantes, recomendar compras por prioridad, registrar recepción parcial y llevar el caso a preparado solo con disponibilidad ejecutable.

**Criterios por Bolt:**
- **Bolt 001:** Solo puede existir una requisición no recibida por caso y no se genera para veredictos bloqueados.
- **Bolt 002:** Las recomendaciones se ordenan por prioridad de tarea y código estable.
- **Bolt 003:** La recepción suma stock, registra movimiento entrada con trazabilidad y permite estados pendiente, parcial o recibida.
- **Bolt 004:** El caso pasa de planificado a preparado únicamente si el veredicto vigente vuelve a ser ejecutable.
- **Bolt 005:** El panel muestra requisición, recomendaciones, recepción por ítem y acción de preparado.

### RF-22: Ejecución y seguimiento del plan

**Descripción:** El sistema debe registrar eventos y avances, comparar la ejecución real con el plan, detectar desviaciones y mantener estados de tarea desde el checklist.

**Criterios por Bolt:**
- **Bolt 001:** El primer evento debe ser inicio desde preparado y crea fechas estimadas acumuladas.
- **Bolt 002:** La comparación separa completadas, pendientes y retrasadas.
- **Bolt 003:** Las incidencias abiertas y retrasos generan riesgos y recomendaciones.
- **Bolt 004:** Checklist completo marca la tarea completada; desmarcar devuelve en_curso.
- **Bolt 005:** El panel permite eventos, checklist e incidencias con prefijo ejec- y error bag propio.

### RF-23: Cierre y reapertura del caso

**Descripción:** El sistema debe reevaluar el resultado final, decidir cierre o reapertura, generar informe plano y preparar una notificación sin enviarla.

**Criterios por Bolt:**
- **Bolt 001:** La reevaluación usa el motor de estado desde el evento de inicio y omite sensores sin lecturas posteriores.
- **Bolt 002:** Se puede cerrar con severidad baja/media, cero tareas pendientes y cero incidencias abiertas; reapertura exige motivo.
- **Bolt 003:** El informe final contiene contenido plano, resumen, autor, destinatario y versión vigente.
- **Bolt 004:** El cierre cambia en_ejecucion a cerrado dentro de una transacción y marca notificacion_pendiente.
- **Bolt 005:** El panel cierre expone evaluar, cerrar y reapertura con prefijo cierre-.
- **Bolt 006:** El informe es accesible solo a administrador o dueño y muestra 404 sin informe.

## 5. TABLA RESUMEN DE REQUERIMIENTOS FUNCIONALES

| Código RF | Nombre del Requerimiento Funcional | Módulo | Fuente de Sustento | Estado |
|---|---|---|---|---|
| RF-01 | Captura de Perfil y Espacio | Recomendador | Art. 5 (Green Oasis: entradas de entorno)26 | Completo (perfil del cliente implementado) |
| RF-02 | Filtros Excluyentes (Toxicidad/Luz) | Recomendador | Art. 52 y Art. 6 (60% preocupación por luz)9 | Completo |
| RF-03 | Ranking y Score Ponderado | Recomendador | Art. 2 (Fauzia et al., 2023: SPSS Conjoint Analysis)1more_horiz | Implementado; pendiente alinear pesos/variables especificados con el servicio vigente |
| RF-04 | Entradas Básicas Sin NPK | Recomendador | Art. 1 (Aradea et al., 2023: CNN 1D + Adagrad, 93.64%)14more_horiz | Implementado; pendiente confirmar alcance de entradas frente al servicio vigente |
| RF-05 | Emparejamiento "Plant Parents" | Recomendador | Art. 6 (OnePoll: 70% plant parents, 67% reto)1819 | Completo |
| RF-06 | Recepción y registro de lecturas JSON autenticadas | Telemetría IoT | Art. 7 (ESP32 y sensores multisensor) | Backend completo y probado; integración con firmware físico pendiente |
| RF-07 | Evaluación de umbrales de medición | Telemetría IoT | Art. 7 y Art. 6 (necesidad de riego) | Completo en backend y probado |
| RF-08 | Evaluación de rangos de microclima | Telemetría IoT | Art. 7 (DHT22/BH1750) y Art. 8 | Completo en backend y probado; la visualización refleja alertas activas |
| RF-09 | Alertas informativas por mediciones fuera de rango | Telemetría IoT | Rangos de referencia configurados por especie | Completo en backend y probado |
| RF-10 | Vista de historial/alertas del cliente | Base de Datos / Cliente | Art. 326 y Art. 4 (Trazabilidad por id)28 | Completo y probado; firmware físico no requerido para las pruebas |
| RF-11 | Asociación de planta vendida, cliente y sensor | Base de Datos | Art. 3 (NMIS: gestión relacional unificada)2627 | Completo en backend y probado |
| RF-12 | Alerta de stock bajo por especie | Inventario | Art. 3 y Art. 4 (Stock mínimo y actual) | Completo |
| RF-13 | Validaciones API y respuestas HTTP 401/422 | API / Calidad | Art. 4 (Manejo de interferencias en datos)28 | Completo en backend y probado |
| RF-14 | Detección de Sensores Offline | API / Calidad | Art. 326 y Art. 4 (Control de pérdida de paquetes)28 | Pendiente (Fase 4) |
| RF-15 | Normalización de solicitudes de mantenimiento | Mantenimiento | Proceso de normalización del caso de mantenimiento | En implementación (Bolt 001) |
| RF-18 | Ingesta y compila evidencia por caso de mantenimiento | Mantenimiento | Evidencia asociada al caso y lecturas de monitoreo existentes | En implementación (INT-019-BOLT-001) |
| RF-19 | Plan de mantenimiento derivado del diagnóstico | Mantenimiento | Diagnóstico vigente y plantillas por tipo | En implementación (INT-020-BOLT-004) |
| RF-20 | Disponibilidad de recursos y veredicto de ejecución | Mantenimiento | Catálogo y plan vigente | En implementación (INT-021-BOLT-004) |
| RF-21 | Abastecimiento y preparación del caso | Mantenimiento | Requisición y recepción de materiales | En implementación (INT-022-BOLT-005) |
| RF-22 | Ejecución y seguimiento del plan | Mantenimiento | Eventos, checklist y comparación | En implementación (INT-023-BOLT-005) |
| RF-23 | Cierre y reapertura del caso | Mantenimiento | Reevaluación e informe final | En implementación (INT-024-BOLT-006) |
| RF-24 | Gestión del ciclo de planeación comercial | Planeación comercial | Campañas, productos y estados del macroproceso | Etapas A y B completas |
| RF-25 | Demanda y evaluación comercial | Planeación comercial | Ventas, catálogo y periodo de campaña | Etapas A y B completas |

## 6. MÓDULO 4: PLANEACIÓN COMERCIAL

### RF-24: Gestión del ciclo de planeación comercial

**Descripción:** El sistema debe registrar campañas comerciales con objetivos, presupuesto, fechas, plantas ofertadas y metas cuantitativas. Debe permitir avanzar por las actividades de análisis de demanda, identificación de productos, revisión y gestión de disponibilidad, planificación de oferta, promoción, evaluación y documentación de ajustes para el siguiente ciclo.

**Criterios de aceptación:** La campaña recibe un código único `CAM-AAAA-00001`. El vendedor ejecuta las actividades comerciales; el administrador ejecuta disponibilidad y evaluación. Cada transición valida estado y rol en el Service. No se planifica una oferta cuyo stock actual no cubra las cantidades meta; la disponibilidad no reserva inventario. Si la reposición no se logra, el vendedor puede reducir o descartar productos y volver a intentar la planificación solo con disponibilidad suficiente.

### RF-25: Demanda y evaluación comercial

**Descripción:** El sistema debe consultar ventas por planta dentro de un periodo, ordenar el ranking de unidades vendidas, identificar plantas bajo el stock mínimo y plantas sin ventas. Al finalizar una campaña debe comparar las ventas de sus plantas en sus fechas con las cantidades meta, calcular porcentaje de cumplimiento e ingresos aproximados.

**Criterios de aceptación:** La evaluación se habilita después de la fecha final y de marcar la promoción como realizada. Los resultados se guardan como una fotografía de la evaluación. Como las ventas no identifican campaña ni precio cobrado, se consideran todas las ventas de esas plantas durante el periodo y se estima el ingreso con el precio actual de catálogo; el descuento planificado no se asume aplicado. No se generan métricas de recomendaciones.