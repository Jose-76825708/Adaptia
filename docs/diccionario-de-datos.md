# DICCIONARIO DE DATOS - PROYECTO ADAPTIA

## 1. TABLA: plantas (Catálogo Maestro de Especies - GardenLand)

Almacena las especificaciones biológicas, ambientales y comerciales de cada especie botánica disponible en el catálogo.

| Campo | Tipo de Dato | Requerido | Clave | Descripción y Dominio de Valores | Fuente de Sustento |
|---|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador único de la especie botánica. | Art. 3 y Art. 4 |
| nombre_comun | VARCHAR(100) | Sí | - | Nombre comercial de la planta (ej. "Monstera Deliciosa", "Peace Lily"). | Art. 5 |
| nombre_cientifico | VARCHAR(150) | Sí | - | Nomenclatura botánica binomial (ej. Spathiphyllum). | Art. 4 |
| precio | DECIMAL(8,2) | Sí | - | Precio de venta en moneda local. Categorizado por rangos de accesibilidad (< Rp. 100,000, 100k-500k, >500k). | Art. 2 |
| dificultad_cuidado | ENUM | Sí | - | Nivel de mantenimiento requerido: 'easy' (Fácil) o 'hard' (Complejo). | Art. 2 y Art. 5 |
| requerimiento_luz | ENUM | Sí | - | Tolerancia lumínica mínima: 'low' (Baja/Sombra), 'medium' (Media/Indirecta), 'high' (Alta/Sol directo). | Art. 5 y Art. 6 |
| humedad_suelo_min | FLOAT | Sí | - | Umbral mínimo de humedad del sustrato (%) antes de activar alerta de riego. | Art. 7 y Art. 8 |
| humedad_suelo_max | FLOAT | Sí | - | Límite superior óptimo de humedad en el sustrato (capacidad de campo). | Art. 7 y Art. 8 |
| temperatura_min | FLOAT | Sí | - | Temperatura ambiental mínima tolerada (°C) sin presentar estrés térmico. | Art. 1 y Art. 7 |
| temperatura_max | FLOAT | Sí | - | Temperatura ambiental máxima tolerada (°C). | Art. 1 y Art. 7 |
| es_toxica | BOOLEAN | Sí | - | Indica si la especie es nociva o venenosa para niños o mascotas (true/false). | Art. 5 |
| beneficio_principal | VARCHAR(100) | No | - | Propósito de la planta: 'purificacion_aire', 'decoracion', 'resistencia'. | Art. 5 |
| tamano | ENUM | Sí | - | Porte físico del espécimen: 'small' (Pequeña), 'medium' (Mediana), 'big' (Grande). | Art. 2 |
| color_predominante | VARCHAR(50) | No | - | Coloración principal del follaje o flor ('green', 'red', 'white', 'purple'). | Art. 2 |
| created_at / updated_at | TIMESTAMP | No | - | Marcas de tiempo de auditoría del sistema Laravel. | Convención Eloquent |

## 2. TABLA: perfiles_usuario (Preferencias y Entorno del Hogar)

Registra las características del espacio físico, restricciones del hogar y nivel de experiencia del cliente ("plant parent").

| Campo | Tipo de Dato | Requerido | Clave | Descripción y Dominio de Valores | Fuente de Sustento |
|---|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador único del perfil de usuario. | Estándar BD |
| user_id | BIGINT (Unsigned) | Sí | FK | Clave foránea referenciando a la tabla de autenticación users.id. | Relación M:1 |
| tipo_espacio | ENUM | Sí | - | Tipo de entorno disponible: 'casa', 'departamento', 'oficina', 'balcon'. | Art. 5 |
| tipo_habitacion | ENUM | Sí | - | Ubicación específica: 'sala', 'dormitorio', 'cocina', 'terraza'. | Art. 5 |
| nivel_luz_disponible | ENUM | Sí | - | Evaluación de luz natural: 'low' (Poca luz), 'medium' (Indirecta), 'high' (Abundante). | Art. 5 y Art. 6 |
| tiene_mascotas | BOOLEAN | Sí | - | Indica presencia de perros o gatos en la vivienda (true/false). | Art. 5 |
| tiene_ninos | BOOLEAN | Sí | - | Indica presencia de niños pequeños en la vivienda (true/false). | Art. 5 |
| nivel_experiencia | ENUM | Sí | - | Perfil de cuidador: 'principiante' (Plant Parent novato), 'intermedio', 'experto'. | Art. 6 |
| presupuesto_max | DECIMAL(8,2) | No | - | Límite máximo de disposición a pagar por planta o maceta inteligente. | Art. 2 |

## 3. TABLA: plantas_monitoreadas (Instancias IoT / Macetas Registradas)

Mapea la asociación relacional entre un usuario, una especie comprada de GardenLand y el nodo físico ESP32.

| Campo | Tipo de Dato | Requerido | Clave | Descripción y Dominio de Valores | Fuente de Sustento |
|---|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador único de la instancia de maceta/planta monitoreada (planta_id). | Art. 3 y Art. 4 |
| user_id | BIGINT (Unsigned) | Sí | FK | Referencia al usuario propietario (users.id). | Relación M:1 |
| planta_id | BIGINT (Unsigned) | Sí | FK | Referencia al catálogo maestro de especies (plantas.id). | Art. 3 |
| dispositivo_mac | VARCHAR(17) | Sí | UNIQUE | Dirección MAC física o API Key única del módulo microcontrolador ESP32. | Art. 7 |
| nombre_personalizado | VARCHAR(100) | No | - | Sobrenombre asignado por el usuario a su planta (ej. "Monstera Sala"). | Art. 7 |
| modo_riego_auto | BOOLEAN | Sí | - | Define si el relé de riego actúa automáticamente (true) o bajo confirmación (false). | Art. 7 y Art. 8 |
| estado_conexion | ENUM | Sí | - | Estado de conectividad del nodo: 'online', 'offline' (sin datos > 24 horas). | Art. 3 y Art. 4 |
| ubicacion_maceta | VARCHAR(100) | No | - | Descripción física del lugar de instalación en la residencia. | Art. 7 |

## 4. TABLA: lecturas_telemetria (Serie Temporal de Sensores ESP32)

Registra el historial de paquetes JSON recibidos desde el ESP32 a través del endpoint /api/telemetria.

| Campo | Tipo de Dato | Requerido | Clave | Descripción y Dominio de Valores | Fuente de Sustento |
|---|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador único del registro de telemetría. | Estándar BD |
| maceta_id | BIGINT (Unsigned) | Sí | FK | Referencia a la maceta/instancia activa (plantas_monitoreadas.id). | Art. 3 |
| humedad_suelo | FLOAT | Sí | - | Porcentaje de humedad del sustrato (%) leído por el sensor capacitivo en GPIO D34. | Art. 7 |
| temperatura_aire | FLOAT | Sí | - | Temperatura ambiental (°C) capturada por el sensor DHT22 en GPIO D4. | Art. 7 y Art. 8 |
| humedad_aire | FLOAT | Sí | - | Humedad relativa del aire (%) capturada por el sensor DHT22 en GPIO D4. | Art. 7 y Art. 8 |
| iluminacion_lux | FLOAT | No | - | Intensidad lumínica en luxes capturada vía I2C por el sensor BH1750 (GPIO D22/D21). | Art. 7 |
| estado_rele | BOOLEAN | Sí | - | Estado de activación del relé de la mini bomba en GPIO D14 (1=On, 0=Off). | Art. 7 |
| estado_diagnostico | VARCHAR(50) | Sí | - | Inferencia del servidor: 'Riega hoy', 'Todo bien', 'Estrés térmico'. | Art. 7 y Art. 8 |
| fecha_hora_lectura | TIMESTAMP | Sí | - | Marca de tiempo exacta del paquete transmitido por el ESP32. | Art. 4 y Art. 7 |

## 🔗 DIAGRAMA DE CARDINALIDAD Y RELACIONES DE BASE DE DATOS

- **users ─── (1 : N) ─── perfiles_usuario**: Un usuario registrado posee un perfil con sus preferencias lumínicas, de espacio y presencia de niños/mascotas.
- **plantas ─── (1 : N) ─── plantas_monitoreadas**: Una especie del catálogo de GardenLand puede estar instanciada en múltiples macetas inteligentes de distintos usuarios.
- **users ─── (1 : N) ─── plantas_monitoreadas**: Un usuario puede tener asociadas varias macetas o plantas en su hogar.
- **plantas_monitoreadas ─── (1 : N) ─── lecturas_telemetria**: Una maceta física recopila cientos de lecturas históricas enviadas periódicamente por el ESP32.