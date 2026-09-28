# Informe Técnico de Arquitectura: Proyecto Adaptia (Sistema de Recomendación y Monitoreo IoT)

## 1. ARQUITECTURA GENERAL DEL PROYECTO ADAPTIA

### 1.1. Visión del Enfoque Híbrido

El Proyecto Adaptia se estructura sobre una arquitectura robusta de tres capas que integra hardware embebido de baja potencia con un ecosistema de software escalable. El núcleo del sistema es un **Backend desarrollado en Laravel**, implementado bajo el patrón de diseño **MVC (Modelo-Vista-Controlador)**. Esta elección garantiza una separación clara de responsabilidades y facilita la extensibilidad del sistema. La comunicación de datos se gestiona mediante una **interfaz API RESTful**, que sirve de puente entre la **Capa de Hardware Embebida** (basada en el microcontrolador ESP32) y las interfaces de usuario, permitiendo una sincronización asíncrona y eficiente de la telemetría.

### 1.2. Flujo de Datos y Ciclo de Vida de la Información

El flujo de información se inicia en el entorno físico mediante la captura de variables ambientales. El procesamiento sigue un ciclo de vida crítico: adquisición, transmisión, persistencia y visualización. Según **Aradea et al. (2023)**, la precisión en la identificación de parámetros específicos de la ubicación es vital para mitigar pérdidas bajo condiciones climáticas desfavorables. Los datos crudos son transformados en el Backend para generar recomendaciones inteligentes, cerrando el ciclo con la entrega de información accionable al cliente final a través de un Dashboard dinámico.

## 2. CAPA FÍSICA E IOT (DISPOSITIVO EMBEBIDO ESP32)

### 2.1. Especificaciones de Hardware y Sensores

La selección de componentes responde a la necesidad de capturar las dimensiones críticas del ecosistema botánico con alta fidelidad técnica:

- **Microcontrolador**: ESP32 (System-on-Chip) con conectividad Wi-Fi dual-core para gestión simultánea de sensores y protocolos de red.
- **Humedad del Suelo**: Sensor capacitivo de alta resistencia a la corrosión, conectado al **GPIO D34**.
- **Temperatura y Humedad Ambiental**: Sensor DHT22 integrado en el **GPIO D4**, seleccionado por su estabilidad térmica en entornos variables.
- **Luz Ambiental**: Sensor BH1750 operando bajo el protocolo **I2C (SCL en GPIO D22 y SDA en GPIO D21)** para una medición precisa de luxes.
- **Actuador**: Relé de estado sólido en el **GPIO D14** para el control del sistema hídrico automatizado.

### 2.2. Mecanismo de Telemetría

Para la ingesta de datos, el ESP32 implementa peticiones **HTTP POST** dirigidas a los endpoints de la API. El payload se estructura en formato **JSON** para garantizar la interoperabilidad. Como arquitecto senior, he definido un esquema estricto de fecha para facilitar la validación mediante los *Form Requests* de Laravel:

```json
{
  "planta_id": "integer",
  "humedad_suelo": "float",
  "temperatura": "float",
  "fecha_hora": "YYYY-MM-DD HH:mm:ss"
}
```

*Nota: El uso del formato* `Y-m-d H:i:s` *permite una integración nativa con la biblioteca Carbon de Laravel en el backend.*

## 3. CAPA MODELO (BASE DE DATOS RELACIONAL Y ELOQUENT ORM)

### 3.1. Modelado y Persistencia

Se emplea **Eloquent ORM** para abstraer la lógica de persistencia. La arquitectura utiliza relaciones HasMany para gestionar las series temporales de telemetría, optimizando las consultas de historial. Siguiendo la investigación de **Quino et al. (2021)** sobre las ineficiencias en los inventarios botánicos (que generan pérdidas de hasta 31 millones de dólares anuales debido a errores humanos), este sistema implementa una identificación única obligatoria por ejemplar. Este modelado está diseñado para ser compatible con futuras integraciones de **RFID y drones (sUAS)**, facilitando un inventario bajo demanda automatizado como sugiere la literatura técnica.

### 3.2. Definición de Modelos Principales

| Modelo | Descripción Técnica |
|---|---|
| Planta / Especie | Catálogo centralizado con parámetros botánicos (GardenLand Huancayo). |
| PerfilUsuario | Almacén de criterios de preferencia (espacio, luz, seguridad para niños/mascotas). |
| PlantaMonitoreada | Asociación lógica (belongsTo) entre el usuario, la maceta física y el sensor. |
| LecturaTelemetria | Tabla de series temporales para el registro histórico de métricas ambientales. |

## 4. CAPA CONTROLADOR (LÓGICA DE NEGOCIO Y ENDPOINTS API)

### 4.1. RecomendadorController (Motor de Inferencia)

Este controlador actúa como un wrapper para el motor de inferencia basado en el enfoque de "Green Oasis". Implementa un modelo de clasificación inspirado en **Redes Neuronales Convolucionales (CNN)** con un optimizador **Adagrad**, el cual permite una convergencia más rápida mediante la división de la tasa de aprendizaje.

- **Precisión**: El algoritmo alcanza un **90% de exactitud** operativa.
- **Variables de Entrada**: Se utilizan exclusivamente 4 variables críticas: **Temperatura, Humedad, pH y Pluviometría (Rainfall)**. Según **Aradea et al. (2023)**, este conjunto limitado es suficiente para una recomendación robusta de hasta 22 especies, eliminando la necesidad de sensores NPK costosos y simplificando el hardware.

### 4.2. TelemetriaApiController (Gestión de Datos Inbound)

Gestiona la recepción de datos mediante *Form Requests*, asegurando que solo los payloads con el formato de fecha y tipos de datos correctos sean procesados. Implementa un motor de reglas que evalúa las lecturas en tiempo real para retornar estados inmediatos (ej. "Riega hoy" o "Condición Óptima").

### 4.3. DashboardMonitoreoController

Encargado de la lógica de agregación. Transforma los datos de LecturaTelemetria en conjuntos de datos formateados para gráficas de tendencias, utilizando las capacidades de filtrado de Eloquent para agrupar métricas por intervalos de tiempo.

## 5. CAPA VISTA (FRONTEND)

### 5.1. Interfaz del Recomendador

El diseño de la UI/UX prioriza la captura del perfil del usuario. Siguiendo los hallazgos de **Fauzia et al. (2023)**, donde el **Precio** es el atributo de preferencia número uno (Importancia: 40.547), seguido por el **Nivel de Cuidado** (24.989), la interfaz resalta visualmente el costo y la facilidad de mantenimiento. El sistema recomienda preferentemente plantas de color verde, tamaño mediano y fácil cuidado, alineándose con la demanda actual del mercado ornamental.

### 5.2. Panel de Control en Tiempo Real

Visualización técnica de métricas capturadas por el ESP32. Incluye telemetría en vivo y gráficas históricas de humedad del suelo y temperatura ambiental, permitiendo una supervisión intuitiva del ecosistema.

## 6. CAPA DE CALIDAD DE SOFTWARE Y PRUEBAS (PEST TDD)

### 6.1. Estrategia de Pruebas

Se implementa **PEST** para un Desarrollo Guiado por Pruebas (TDD) ágil. Se prioriza la legibilidad de los tests para asegurar que la lógica de recomendación y la ingesta de la API sean infalibles.

### 6.2. Casos de Prueba Críticos

- **Pruebas Unitarias**: Verificación del algoritmo de clasificación del RecomendadorController bajo las 4 variables de Aradea et al.
- **Pruebas de Integración API**: Simulación de transmisiones del ESP32 con datos malformados para validar la resiliencia del backend.
- **Validación de Inventario**: Basado en **Quino et al. (2021)**, se han diseñado pruebas específicas para asegurar que el sistema de Identificación Única (UID) elimine el error humano en la trazabilidad, garantizando la integridad de los datos de inventario incluso ante interrupciones de red.