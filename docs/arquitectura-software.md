# 🏛️ Especificación de la Arquitectura de Software del Proyecto Adaptia

**Proyecto:** Adaptia — Sistema Inteligente de Recomendación, Monitoreo IoT y Gestión de Plantas Ornamentales
**Organización cliente:** Garden Land Huancayo
**Modelo arquitectónico:** Modelo de 4+1 Vistas de Kruchten y Arquitectura Híbrida IoT-Fog-Cloud de 4 Capas
**Patrón backend:** Modelo-Vista-Controlador (MVC) desacoplado mediante Capa de Servicios (`RecomendacionService`)
**Estrategia de calidad y pruebas:** Modelo en V de Ingeniería de Software y estándares ISO/IEC 25010 / ISO/IEC/IEEE 29119

---

## 1. Visión general y fundamentación de la arquitectura de software

La arquitectura de software de Adaptia se concibe como una solución distribuida e híbrida que desacopla la captura analógica/digital de telemetría física del procesamiento de la lógica de negocio y las interfaces de usuario. Mientras que la arquitectura tecnológica aborda la infraestructura de red y componentes de hardware, la arquitectura de software organiza la estructura lógica de los componentes de código, las abstracciones de datos, los patrones de diseño y las interacciones dinámicas entre módulos.

Como señalan Ilyas et al. (2022, p. 8) al fundamentar el diseño de sistemas distribuidos críticos e IoT, la representación mediante vistas múltiples es indispensable:

> "Kruchten developed the 4 + 1 model to explain the architecture of software systems: Logical View, Development View, Process View, Physical View, Use Cases."
>
> *(Kruchten desarrolló el modelo 4 + 1 para explicar la arquitectura de los sistemas de software: Vista Lógica, Vista de Desarrollo, Vista de Proceso, Vista Física y Casos de Uso).*

En el proyecto Adaptia, el proceso amplía su alcance operativo a un modelo de cuatro carriles (Cliente, Sistema Adaptia - Backend/Laravel, Vendedor, y Sensor IoT - ESP32/Vivero), estructurado y articulado mediante el Modelo de 4+1 Vistas de Kruchten.

---

## 2. El modelo de 4+1 vistas de Kruchten aplicado a Adaptia

```text
                     ┌───────────────────────────────┐
                     │     CASOS DE USO / ESCENARIOS │
                     │          (El "+1")            │
                     └───────────────┬───────────────┘
                                     │
           ┌─────────────────────────┼─────────────────────────┐
           │                         │                         │
┌──────────▼──────────┐   ┌──────────▼──────────┐   ┌──────────▼──────────┐
│    VISTA LÓGICA     │   │ VISTA DE DESARROLLO │   │   VISTA DE PROCESO  │
│ • Patrón MVC        │   │ • Estructura Laravel│   │ • Ingesta de Datos  │
│ • Capa de Servicios │   │ • Componentes Blade │   │ • Evaluador Umbrales│
│ • ORM Eloquent      │   │ • Firmware C++ ESP32│   │ • Tareas Background │
└──────────┬──────────┘   └──────────┬──────────┘   └──────────┬──────────┘
           │                         │                         │
           └─────────────────────────┼─────────────────────────┘
                                     │
                         ┌───────────▼───────────┐
                         │      VISTA FÍSICA     │
                         │ • Nodos ESP32         │
                         │ • Contenedores Docker │
                         └───────────────────────┘
```

### 2.1. Vista Lógica (Logical View)

**Definición teórica:** En la sección "Abstract models of the proposed architecture" de Ilyas et al. (2022, p. 8), se especifica que "the system's features are provided to users as a part of the logical perspective. Diagrams of classes and steps are used to illustrate the logical view".

**Estructura en Adaptia:**

- **Capa de Modelo (entidades y persistencia):** mapeo relacional administrado por Eloquent ORM mediante las clases `Planta` (catálogo maestro de Garden Land), `PerfilUsuario` (restricciones de luz, espacio y mascotas/niños), `PlantaMonitoreada` (asociación relacional usuario-maceta-dispositivo) y `LecturaTelemetria` (serie temporal de lecturas).
- **Capa de Servicio (lógica pura de negocio):** la clase `RecomendacionService` encapsula los filtros eliminatorios de toxicidad (basados en Angel et al., 2025) y el algoritmo de puntuación multicriterio sobre 4 variables ambientales básicas sin NPK (basado en Aradea et al., 2023).
- **Capa de Controlador (orquestación HTTP):** controladores delgados (`RecomendadorController`, `TelemetriaApiController`, `DashboardMonitoreoController`) encargados de recibir la petición HTTP y retornar respuestas estructuradas.
- **Capa de Vista (presentación):** componentes reactivos construidos en Blade con Livewire que actualizan la interfaz web sin recargar la página.

### 2.2. Vista de Desarrollo (Development View)

**Definición teórica:** Ilyas et al. (2022, p. 8) la definen como la organización de componentes y paquetes dentro del entorno de programación utilizado por los desarrolladores.

**Estructura de directorios y firmware en Adaptia:**

- `app/Services/RecomendacionService.php`: contiene la lógica pura de filtrado, scoring y evaluación de umbrales.
- `app/Http/Controllers/`: alberga los controladores que orquestan el tráfico web y los endpoints de la API REST.
- `app/Http/Requests/TelemetriaFormRequest.php`: clase de validación que sanitiza el payload JSON recibido del sensor IoT antes de procesarlo.
- `app/Models/`: modelos de clases Eloquent para la manipulación relacional de MySQL/SQLite.
- `firmware/esp32_sensor.ino`: proyecto en C++ (Arduino IDE) para el microcontrolador ESP32 que gestiona la lectura de los sensores (GPIO D34, GPIO D4, BH1750) y la emisión de solicitudes HTTP POST con el payload JSON.
- `docker-compose.yml`: archivo de orquestación para levantar los contenedores aislados de la aplicación web y la base de datos.

### 2.3. Vista de Proceso (Process View)

**Definición teórica:** Ilyas et al. (2022, p. 10) señalan que "a process perspective talks about the system's dynamic elements and explains its processes and how they interact. Concurrency, distribution, performance, and scalability are all covered by the process view".

**Flujos dinámicos en Adaptia:**

1. **Ingesta y sanitización de telemetría (API REST):** el ESP32 emite un paquete JSON hacia el endpoint `/api/telemetria`. En la capa Fog/Middleware, la petición es filtrada por un `FormRequest`. Si los datos presentan corrupción por ruido inalámbrico (desafío documentado por Quino et al., 2021 y Ferreira et al., 2023), el backend rechaza la transacción retornando un estado `HTTP 422 Unprocessable Entity`.
2. **Evaluación en tiempo real y diagnóstico:** al recibir lecturas válidas, el backend invoca a `RecomendacionService` para comparar los datos contra los rangos de la especie. Si `humedad_suelo < humedad_minima`, el sistema genera en tiempo real la alerta "Riega hoy"; si se encuentra en rango, asigna "Todo bien".
3. **Tareas programadas en segundo plano (cron jobs):** un proceso asíncrono escanea periódicamente los registros telemétricos. Si un dispositivo no reporta datos en más de 24 horas, actualiza automáticamente el estado del nodo a "Offline".

### 2.4. Vista Física / Despliegue (Physical View)

**Definición teórica:** Ilyas et al. (2022, p. 10) indican que "this point of view focuses on the physical interactions and organizational structure of the software elements in the physical layer".

**Topología de red y dispositivos en Adaptia:**

- **Capa de dispositivos (Device Layer):** nodos ESP32 ubicados en los hogares de los clientes, equipados con la sonda capacitiva de humedad (GPIO D34), sensor climático DHT22 (GPIO D4) y sensor de luz BH1750 por I2C (GPIO D22/D21). El nodo mide y transmite datos; el control de relés, bombas, electroválvulas y riego automático queda fuera del alcance.
- **Servidor web y backend (Cloud Layer):** contenedor Docker con Laravel (PHP 8.2) expuesto en el puerto HTTP 8000.
- **Servidor de persistencia:** contenedor Docker con MySQL 8.0 en el puerto 3306 para el entorno de producción.
- **Canal de interconexión:** peticiones HTTP/REST con transmisión de mensajes codificados en formato JSON.

### 2.5. Casos de Uso / Escenarios (el "+1")

**Definición teórica:** Ilyas et al. (2022, p. 10) afirman que "in this viewpoint, the architecture is described using a variety of examples or situations. The links between the items and processes are outlined in these scenarios".

**Módulos de casos de uso en Adaptia (diagrama UML):**

- **Módulo de Recomendación:** registrar perfil del cliente (RF-01), filtrar plantas tóxicas (RF-02), calcular score de compatibilidad (RF-03) y generar ranking de recomendaciones (RF-04).
- **Módulo de Monitoreo IoT:** recibir lecturas de los sensores (RF-06), evaluar humedad del suelo y microclima contra los rangos de referencia (RF-07 y RF-08), generar alertas informativas específicas sin actuar sobre hardware (RF-09) y consultar el historial (RF-10).
- **Módulo de Ventas e Inventario:** autenticación de personal (RF-13), gestionar inventario del vivero (RF-11), registrar venta y descontar stock (RF-05), asignar sensor a la planta (RF-06) y notificar stock bajo (RF-12).

---

## 3. Patrón backend: MVC desacoplado con capa de servicios

Para cumplir con el Requerimiento No Funcional RNF-09 (Mantenibilidad) y las directrices de la norma ISO/IEC 25010, la arquitectura de software prohíbe explícitamente incluir lógica de filtrado botánico o evaluación telemétrica dentro de las rutas o controladores.

```text
[ Petición HTTP ] ──► [ Controller ] ──► [ RecomendacionService ] ──► [ Eloquent Models / DB ]
                           │                      │
                           ▼                      ▼
                     [ Livewire View ]     [ Filtros & Scoring ]
```

- **Controladores delgados (Thin Controllers):** `RecomendadorController` y `TelemetriaApiController` actúan únicamente como intermediarios que reciben las peticiones HTTP, delegan la lógica a la capa de servicio y retornan la respuesta JSON o la vista renderizada.
- **Servicios pesados (Fat Services):** la clase `RecomendacionService` encapsula los algoritmos puros. Esto permite ejecutar pruebas unitarias automatizadas sobre las reglas de negocio sin necesidad de levantar el servidor web ni acceder a la base de datos real.

---

## 4. Arquitectura de datos e integración de inventario unificado (NMIS)

Inspirado en la arquitectura del Nursery Management Information System (NMIS) del USDA Forest Service (Davis, 2003), Adaptia implementa un modelo relacional unificado que gestiona el catálogo comercial de Garden Land, las ventas y las macetas monitoreadas dentro de un único esquema relacional.

```text
┌─────────────────┐       ┌────────────────────────┐       ┌───────────────────────┐
│     plantas     │1     N│  plantas_monitoreadas  │1     N│  lecturas_telemetria  │
│ (Catálogo Master)├──────┤   (Macetas / Sensor)   ├───────┤   (Serie Temporal)    │
└─────────────────┘       └───────────┬────────────┘       └───────────────────────┘
                                      │ N
                                      │
                                      │ 1
                          ┌───────────▼────────────┐
                          │    perfiles_usuario    │
                          └────────────────────────┘
```

- **Unificación de subsistemas:** siguiendo la filosofía de NMIS de eliminar formularios separados para distintas modalidades de cultivo, Adaptia administra plantas de interior, terrazas y jardines en una sola estructura unificada.
- **Identificación única por planta y token de privacidad:** inspirado en el rastreo de inventarios por códigos únicos (Quino et al., 2021), cada planta vendida se asocia a un token aleatorio unívoco, no predecible ni secuencial, protegiendo la privacidad del usuario frente a accesos públicos no autorizados.

---

## 5. Arquitectura de calidad y pruebas (Modelo en V)

Alineado con el Modelo en V de la Ingeniería de Software aplicado a sistemas IoT (Ferreira et al., 2023) y bajo la norma ISO/IEC/IEEE 29119, la arquitectura de software de Adaptia relaciona directamente cada nivel de diseño con su correspondiente fase de verificación automatizada:

```text
Análisis de Requerimientos ─────────► Pruebas de Aceptación / E2E (Cypress)
           │                                          ▲
           ▼                                          │
    Diseño del Sistema ─────────────► Pruebas de Sistema / Humo (Pest TDD)
           │                                          ▲
           ▼                                          │
   Diseño Arquitectónico ───────────► Pruebas de Integración (Pest + SQLite)
           │                                          ▲
           ▼                                          │
   Diseño de Componentes ───────────► Pruebas Unitarias (Pest TDD)
           │                                          ▲
           └──────────────► CODIFICACIÓN ─────────────┘
```

| Fase de diseño de software | Nivel de prueba asociado | Herramienta en Adaptia | Objeto de verificación |
|---|---|---|---|
| Diseño de Componentes (Module Design) | Pruebas Unitarias (Unit Tests) | Pest TDD | Funciones puras en `RecomendacionService` (scoring, descarte de toxicidad) sin tocar la BD |
| Diseño Arquitectónico (Architectural Design) | Pruebas de Integración (Feature Tests) | Pest TDD + SQLite in-memory | Endpoints de la API (`POST /api/telemetria`), interacción con Eloquent ORM y respuestas HTTP |
| Diseño del Sistema (System Design) | Pruebas de Sistema / Humo (Smoke Tests) | Pest TDD | Disponibilidad de rutas web, salud de la base de datos y respuestas HTTP sanitizadas (HTTP 422) |
| Análisis de Requerimientos (Requirements Analysis) | Pruebas de Aceptación / End-to-End | Cypress | Flujo completo del usuario desde el navegador web (Perfil → Recomendación → Venta → Alerta) |

---

## 📊 Matriz de trazabilidad científica de la arquitectura de software

| Componente de software | Vista de Kruchten | Patrón / Tecnología | Fuente científica de sustento |
|---|---|---|---|
| Filtros y scoring botánico | Vista Lógica | `RecomendacionService` (Laravel) | Aradea et al. (2023) / Angel et al. (2025) / Univ. Continental (2026) |
| Estructura del proyecto | Vista de Desarrollo | Laravel MVC + Livewire / C++ Arduino | Ilyas et al. (2022, p. 8) / Univ. Continental (2026, p. 27, 31) |
| Ingesta y diagnóstico IoT | Vista de Proceso | FormRequests / Evaluador de umbrales | Méndez-Guzmán et al. (2022, p. 10) / Ilyas et al. (2022, p. 10) |
| Nodos y contenedores | Vista Física | Nodos ESP32 + Docker Compose | Soibam & Vignesh (2025) / Ilyas et al. (2022) / Univ. Continental |
| Módulos del sistema | Escenarios / Casos de Uso | Diagrama UML (PlantUML) | Davis (2003 - NMIS) / Univ. Continental (2026, p. 26) |

---

## 📚 Referencias bibliográficas de la especificación

- Ilyas, A., et al. (2022). *Software architecture for pervasive critical health monitoring system using fog computing.* Journal of Cloud Computing, 11(84), pp. 1–14.
- Méndez-Guzmán, H. A., et al. (2022). *IoT-Based Monitoring System Applied to Aeroponics Greenhouse.* Sensors, 22(15), 5646, pp. 1–28.
- Ferreira, V. G., et al. (2023). *Software Testing Applied to the Development of IoT Systems: preliminary results.* In Proceedings of SAST 2023, ACM, pp. 1–10.
- Davis, D. B. (2003). *The Nursery Management Information System (NMIS) at J. Herbert Stone Nursery using MS Access®.* USDA Forest Service Proceedings RMRS-P-28, pp. 130–132.
- Soibam, S., & Vignesh, B. (2025). *Design and Development of a Smart Plant Assistant.* IJRASET, 13(7), pp. 2025–2028.
- Universidad Continental (2026). *Estructura de proyecto final: ADAPTIA – Sistema inteligente de recomendación, monitoreo IoT y gestión de plantas ornamentales.* Facultad de Ingeniería, Huancayo, Perú, pp. 1–43.
