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
| humedad_suelo_min | DECIMAL(8,2) | No | - | Umbral mínimo de humedad del sustrato (%) antes de activar alerta de riego; pendiente de configurar por especie. | Art. 7 y Art. 8 |
| humedad_suelo_max | DECIMAL(8,2) | No | - | Límite superior de humedad en el sustrato (%); pendiente de configurar por especie. | Art. 7 y Art. 8 |
| temperatura_min | DECIMAL(8,2) | No | - | Temperatura ambiental mínima tolerada (°C), pendiente de configurar por especie. | Art. 1 y Art. 7 |
| temperatura_max | DECIMAL(8,2) | No | - | Temperatura ambiental máxima tolerada (°C), pendiente de configurar por especie. | Art. 1 y Art. 7 |
| humedad_ambiental_min | DECIMAL(8,2) | No | - | Umbral mínimo de humedad relativa ambiental (%), para comparar con las lecturas del DHT22. | Fase 4 IoT |
| humedad_ambiental_max | DECIMAL(8,2) | No | - | Umbral máximo de humedad relativa ambiental (%), para comparar con las lecturas del DHT22. | Fase 4 IoT |
| luz_min | DECIMAL(8,2) | No | - | Iluminación mínima de referencia en lux para la especie, medida por el BH1750. | Fase 4 IoT |
| luz_max | DECIMAL(8,2) | No | - | Iluminación máxima de referencia en lux para la especie, medida por el BH1750. | Fase 4 IoT |
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

## 3. TABLA: plantas_vendidas (Unidades individuales del cliente)

Cada fila representa una unidad física vendida. El sensor asignado permite asociar de forma segura sus lecturas con esa unidad, no solamente con la especie del catálogo.

| Campo | Tipo de Dato | Requerido | Clave | Descripción |
|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador de la unidad vendida; se envía como `planta_vendida_id` en las lecturas. |
| venta_id | BIGINT (Unsigned) | Sí | FK | Venta que originó esta unidad. |
| user_id | BIGINT (Unsigned) | Sí | FK | Cliente propietario de la unidad. |
| sensor_id | BIGINT (Unsigned) | No | FK | Sensor/ESP32 asignado; una unidad sin sensor no puede enviar lecturas. |

## 4. TABLA: sensores (Dispositivos ESP32)

| Campo | Tipo de Dato | Requerido | Clave | Descripción |
|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador interno del sensor. |
| identificador_fisico | VARCHAR | Sí | UNIQUE | Identificador visible del dispositivo. |
| token_hash | VARCHAR(64) | No | UNIQUE | Hash SHA-256 de la credencial. El token original no se almacena en la base de datos. |
| estado | ENUM | Sí | - | Estado del sensor: `activo` o `inactivo`. Solo sensores activos con token válido pueden enviar lecturas. |

## 5. TABLA: lecturas_sensores (Historial de mediciones IoT)

El ESP32 envía un `POST /api/lecturas` con `Content-Type: application/json` y la credencial en `X-Sensor-Token`. La unidad indicada por `planta_vendida_id` debe tener asignado ese sensor.

```json
{
  "planta_vendida_id": 1,
  "humedad_suelo": 24.5,
  "temperatura": 22.3,
  "humedad_ambiental": 55.0,
  "luz": 320,
  "fecha_hora": "2026-09-11T17:30:00"
}
```

Una solicitud aceptada responde `201 Created`; credenciales ausentes/inválidas responden `401`, la unidad no asignada a ese dispositivo responde `403` y un payload inválido responde `422`.
La respuesta también incluye `alertas`, con la categoría (`riego` o `cuidado_planta`), la variable afectada, dirección, medición, rango esperado y mensaje específico para el nombre de la especie. En este paso son resultados de evaluación; su persistencia como episodios sin duplicados se implementa en el siguiente paso.

| Campo | Tipo de Dato | Requerido | Clave | Descripción y dominio |
|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador único de la lectura. |
| planta_vendida_id | BIGINT (Unsigned) | Sí | FK | Unidad individual monitoreada (`plantas_vendidas.id`). |
| humedad_suelo | DECIMAL(8,2) | Sí | - | Humedad del sustrato en porcentaje (0–100). |
| temperatura | DECIMAL(8,2) | Sí | - | Temperatura medida por DHT22 en °C (-40–80). |
| humedad_ambiental | DECIMAL(8,2) | Sí | - | Humedad relativa medida por DHT22 (0–100%). |
| luz | DECIMAL(10,2) | Sí | - | Iluminación medida por BH1750 en lux (0–65535). |
| fecha_hora | TIMESTAMP | Sí | - | Fecha/hora ISO 8601 reportada por el dispositivo. Si no incluye zona horaria, se interpreta con la zona configurada en la aplicación. |

## 6. TABLA: alertas (Alertas informativas)

Las alertas de monitoreo conservan los tipos existentes `riego` y `stock_bajo`, y agregan `cuidado_planta` para las demás condiciones ambientales. Se mantiene como máximo un episodio abierto por unidad y variable; las lecturas posteriores actualizan su medición/mensaje, y una lectura dentro del rango lo resuelve. Los campos específicos de medición son opcionales para preservar alertas existentes y alertas no relacionadas con sensores.

| Campo | Tipo de Dato | Requerido | Clave | Descripción y dominio |
|---|---|---|---|---|
| id | BIGINT (Unsigned) | Sí | PK | Identificador de la alerta. |
| planta_vendida_id | BIGINT (Unsigned) | Sí | FK | Unidad individual a la que corresponde la alerta. |
| tipo | ENUM | Sí | - | Categoría: `riego`, `abono`, `stock_bajo` o `cuidado_planta`. |
| variable | VARCHAR | No | - | Medición afectada: `humedad_suelo`, `temperatura`, `humedad_ambiental` o `luz`. |
| valor_medido | DECIMAL(10,2) | No | - | Valor más reciente recibido mientras el episodio permanece activo. |
| limite | DECIMAL(10,2) | No | - | Mínimo o máximo que incumplió la medición. |
| rango_minimo | DECIMAL(10,2) | No | - | Mínimo del rango de especie guardado como referencia del episodio. |
| rango_maximo | DECIMAL(10,2) | No | - | Máximo del rango de especie guardado como referencia del episodio. |
| direccion | ENUM | No | - | `bajo` si está por debajo del mínimo; `alto` si supera el máximo. |
| mensaje | TEXT | No | - | Mensaje informativo específico guardado para este episodio. |
| leida | BOOLEAN | Sí | - | Indica si el cliente ya consultó la alerta. |
| resuelta_en | TIMESTAMP | No | - | Fecha/hora en que una medición volvió al rango; nulo mientras el episodio siga activo. |

## 🔗 DIAGRAMA DE CARDINALIDAD Y RELACIONES DE BASE DE DATOS

- **users ─── (1 : N) ─── perfiles_usuario**: Un usuario registrado posee un perfil con sus preferencias lumínicas, de espacio y presencia de niños/mascotas.
- **plantas ─── (1 : N) ─── plantas_monitoreadas**: Una especie del catálogo de GardenLand puede estar instanciada en múltiples macetas inteligentes de distintos usuarios.
- **users ─── (1 : N) ─── plantas_monitoreadas**: Un usuario puede tener asociadas varias macetas o plantas en su hogar.
- **plantas_monitoreadas ─── (1 : N) ─── lecturas_telemetria**: Una maceta física recopila cientos de lecturas históricas enviadas periódicamente por el ESP32.