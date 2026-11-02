
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

### RF-15: Trazabilidad e inmutabilidad de movimientos de inventario

**Descripción:** Los movimientos de inventario no se editan ni se eliminan. Cada movimiento registra el usuario autenticado que lo realizó, un motivo opcional y el stock de la planta antes y después del cambio. Para corregir un movimiento, un administrador lo anula indicando un motivo obligatorio: el sistema crea un contramovimiento de tipo inverso y misma cantidad, vinculado al original, y ajusta el stock. Un movimiento solo puede anularse una vez y una anulación no puede anularse.

**Entradas:** Identificador del movimiento a anular y motivo de la anulación (obligatorio, máximo 255 caracteres).

**Criterio de Aceptación:** (1) No existen rutas para editar o eliminar movimientos. (2) Anular una entrada crea una salida y anular una salida crea una entrada, con `stock_antes` y `stock_despues` del contramovimiento. (3) Anular una entrada se rechaza si el stock disponible es insuficiente. (4) Solo el rol administrador puede anular. (5) Tras anular se reevalúa la alerta de stock bajo (RF-12).

**Sustento:** Regla de negocio del proyecto (mapa de Bolts INT-015); no se asocia a un artículo de la bibliografía.

### RF-16: Consulta paginada y filtrada de movimientos de inventario

**Descripción:** El administrador y el vendedor pueden consultar el historial de movimientos de inventario en un listado paginado (15 movimientos por página) ordenado del más reciente al más antiguo. El listado puede filtrarse por planta, por tipo de movimiento (entrada o salida) y por rango de fechas (desde y hasta, ambos inclusivos y aplicados sobre el día completo). Los filtros se combinan entre sí y se conservan al cambiar de página.

**Entradas:** Parámetros opcionales `planta_id`, `tipo` (`entrada` o `salida`), `desde` y `hasta` (formato AAAA-MM-DD) y `page`. Los parámetros enviados en blanco se ignoran.

**Criterio de Aceptación:** (1) Sin filtros se listan todos los movimientos, 15 por página y del más reciente al más antiguo. (2) Cada filtro restringe el resultado y los filtros combinados se aplican con AND. (3) `desde` y `hasta` incluyen todo el día indicado. (4) Un valor inválido (tipo desconocido, planta inexistente, fecha con formato incorrecto, `hasta` anterior a `desde`) devuelve al listado con un mensaje de error y sin aplicar filtros. (5) Los enlaces de paginación conservan los filtros activos y el formulario muestra los valores aplicados. (6) Si ningún movimiento coincide se muestra un mensaje distinto según haya o no filtros. (7) El número de consultas a la base de datos no crece con la cantidad de movimientos. (8) Solo administrador y vendedor acceden; el cliente recibe 403 y el invitado es enviado al inicio de sesión.

**Sustento:** Regla de negocio del proyecto (mapa de Bolts INT-016); no se asocia a un artículo de la bibliografía.

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
| RF-15 | Trazabilidad e inmutabilidad de movimientos de inventario | Inventario | Regla de negocio del proyecto (INT-015) | Implementado (INT-015-BOLT-001 a 003) |
| RF-16 | Consulta paginada y filtrada de movimientos de inventario | Inventario | Regla de negocio del proyecto (INT-016) | Implementado (INT-016-BOLT-001); panel resumen pendiente (INT-016-BOLT-002) |