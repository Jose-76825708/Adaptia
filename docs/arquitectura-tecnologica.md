# 📄 METODOLOGÍA Y ESPECIFICACIÓN DE LA ARQUITECTURA TECNOLÓGICA DEL PROYECTO ADAPTIA

**Proyecto:** Adaptia — Sistema Inteligente de Recomendación, Monitoreo IoT y Gestión de Plantas Ornamentales[1][2]. **Organización Cliente:** Garden Land Huancayo[3]. **Marco Teórico de Referencia:** *IoT-Based Monitoring System Applied to Aeroponics Greenhouse* (Méndez-Guzmán et al., 2022)[4][5] y *Software architecture for pervasive critical health monitoring system using fog computing* (Ilyas et al., 2022)[6][7]. **Documento Académico Base:** *Estructura de proyecto final - Adaptia* (Universidad Continental, 2026)[1]. **Stack Tecnológico:** Backend en **Laravel (PHP)** con patrón MVC y capa de Servicios, **Blade + Livewire**, **MySQL / SQLite**, **Docker Compose** y Firmware en **C++ para ESP32**[8].

---

## 1\. DESCRIPCIÓN GENERAL Y MODELO CONCEPTUAL HÍBRIDO

El sistema **Adaptia** adopta un modelo arquitectónico distribuido e híbrido que articula una red de hardware embebido IoT en el entorno físico con una plataforma de software web distribuida[2][12]. Esta estructura se organiza en **4 capas tecnológicas** (*Device, Fog, Cloud y Application Layer*), fundamentadas en la arquitectura de Méndez-Guzmán et al. (2022)[5][13] y respaldadas por las **4+1 Vistas de Kruchten** adaptadas por Ilyas et al. (2022)[7]:

```
   ┌────────────────────────────────────────────────────────────────┐
   │ 4. CAPA DE APLICACIÓN (Application Layer)                     │
   │    • Dashboard Web/Móvil en Blade + Livewire                    │
   └───────────────────────────────┬────────────────────────────────┘
                                   │ HTTP/REST (JSON)
   ┌───────────────────────────────▼────────────────────────────────┐
   │ 3. CAPA NUBE Y PERSISTENCIA (Cloud Layer)                      │
   │    • Backend Laravel (Services & ORM Eloquent)                 │
   │    • Base de Datos MySQL (Producción) / SQLite (Pruebas)       │
   │    • Orquestación de Contenedores Docker Compose              │
   └───────────────────────────────▲────────────────────────────────┘
                                   │ HTTPS / Validaciones API
   ┌───────────────────────────────┴────────────────────────────────┐
   │ 2. CAPA FOG / MIDDLEWARE (Fog Layer)                           │
   │    • Ingesta de Telemetría y Form Requests en Laravel          │
   │    • Evaluador de Umbrales ("Riega hoy" / "Todo bien")         │
   └───────────────────────────────▲────────────────────────────────┘
                                   │ HTTP POST (Payload JSON)
   ┌───────────────────────────────┴────────────────────────────────┐
   │ 1. CAPA DE DISPOSITIVOS / HARDWARE (Device Layer)              │
   │    • Microcontrolador ESP32 + Wi-Fi                             │
   │    • Sensor Capacitivo de Suelo (GPIO D34)                      │
   │    • Sensor Climático DHT22 (GPIO D4)                           │
   │    • Sensor de Luz Digital BH1750 (I2C GPIO D22/D21)            │
   │    • Sin actuadores de riego (fuera del alcance)                │
   └────────────────────────────────────────────────────────────────┘

```

### **Fundamentación Teórica del Modelo 4+1 Vistas de Kruchten:**

En la **Sección "Abstract models of the proposed architecture"** de Ilyas et al. (2022, p. 8), se sustenta la necesidad de modelar arquitecturas IoT distribuidas citando textualmente:

> *"Kruchten developed the 4 + 1 model to explain the architecture of software systems: Logical View, Development View, Process View, Physical View, Use Cases."*[7] *(Kruchten desarrolló el modelo 4 + 1 para explicar la arquitectura de los sistemas de software: Vista Lógica, Vista de Desarrollo, Vista de Proceso, Vista Física y Casos de Uso)*.

Asimismo, en el **Capítulo 3, Sección 3.4** del documento base de la Universidad Continental (2026, p. 17), se explicita cómo la operación de Adaptia reestructura la arquitectura de procesos del negocio:

> *"El proceso amplía su alcance a un modelo de cuatro carriles (Cliente, Sistema Adaptia - Backend/Laravel, Vendedor, y Sensor IoT - ESP32/Vivero)."*[12]

---

## 2\. CAPA 1: DISPOSITIVOS Y HARDWARE EMBEBIDO (*DEVICE LAYER*)

La **Capa de Dispositivos** constituye el punto de captura analógica y digital del entorno físico de la planta[5].

### **Cita Textual y Ubicación de Sustento:**

En la **Sección 3.2 "Monitoring System Proposal"** de Méndez-Guzmán et al. (2022, p. 10), se define formalmente la función de esta capa:

> *"The proposed monitoring system is based on a four-layer IoT architecture: device layer, fog layer, cloud layer and application layer... The device layer integrates the sensors used for the measurement of temperature and luminosity, as well as leaf temperature and humidity sensors."*[5][13] *(El sistema de monitoreo propuesto se basa en una arquitectura IoT de cuatro capas: capa de dispositivos, capa fog, capa nube y capa de aplicación... La capa de dispositivos integra los sensores utilizados para la medición de temperatura y luminosidad, así como sensores de temperatura y humedad de la hoja)*.

Por su parte, en la **Sección "Software Implementation"** de Soibam & Vignesh (2025, p. 2028), se detalla la integración del hardware embebido:

> *"All sensors are connected to the ESP32 through specific GPIO pins... The ESP32 sends live data to the connected mobile application via Wi-Fi, and triggers irrigation when soil moisture is below threshold."*[15][16] *(Todos los sensores están conectados al ESP32 a través de pines GPIO específicos... El ESP32 envía datos en vivo a la aplicación conectada a través de Wi-Fi y activa el riego cuando la humedad del suelo está por debajo del umbral)*.

### **Configuración Técnica del Nodo ESP32 en Adaptia:**

* **Microcontrolador Base:** ESP32 programado en C++ (Arduino IDE) con conectividad Wi-Fi integrada[8].
* **Sensor Capacitivo de Humedad del Suelo (** **GPIO D34** **):** Mide el porcentaje hídrico del sustrato mediante variación capacitiva, evitando la corrosión galvánica[17].
* **Sensor de Temperatura y Humedad Ambientales DHT22 (** **GPIO D4** **):** Captura el microclima del aire circundante con alta precisión digital[17].
* **Sensor de Luz Digital BH1750 (** **GPIO D22/SDA** **y** **GPIO D21/SCL** **):** Mide la intensidad lumínica ambiental en luxes vía bus I2C[17].
* **Actuación física:** No se incluye módulo relé, bomba, electroválvula ni control de riego automático. El ESP32 solo captura y transmite mediciones para que Adaptia genere alertas informativas.

### **Estructura del Payload JSON Transmitido vía HTTP POST:**

```
{
  "planta_id": "84e4b4be-b260-4357-9e36-3c7c0bb0c6e9",
  "humedad_suelo": 32.5,
  "temperatura": 21.8,
  "humedad_aire": 55.0,
  "iluminacion_lux": 420.0,
  "fecha_hora": "2026-09-29 15:00:00"
}

```

*(Transmisión de lecturas telemétricas enviadas desde el ESP32 hacia el endpoint API de Laravel)*[8].

---

## 3\. CAPA 2: MIDDLEWARE Y PROCESAMIENTO INTERMEDIO (*FOG LAYER*)

La **Capa Fog** actúa como un filtro inteligente entre la red física y la base de datos distribuida, reduciendo la latencia y previniendo la contaminación de la base de datos[5].

### **Cita Textual y Ubicación de Sustento:**

En la **Sección 3.2 "Monitoring System Proposal"** de Méndez-Guzmán et al. (2022, p. 10), se especifica la responsabilidad del Fog Layer:

> *"The fog layer is made up of a set of microservices in charge of managing the information locally or remotely through one of several servers in the cloud... sensor service, control service and alert service microservices manage the exchange of information."*[5][21] *(La capa fog está formada por un conjunto de microservicios encargados de gestionar la información local o remotamente a través de uno de los varios servidores en la nube... los microservicios de servicio de sensores, servicio de control y servicio de alertas gestionan el intercambio de información)*.

En la **Sección "Proposed architecture - Tier 2"** de Ilyas et al. (2022, p. 4), se reafirma la ventaja de procesar en el borde:

> *"Fog nodes are positioned closer to the IoT devices at the network's edge to guarantee a quick reaction in a real-time setting."*[20][22] *(Los nodos Fog están posicionados más cerca de los dispositivos IoT en el borde de la red para garantizar una reacción rápida en un entorno en tiempo real)*.

### **Aterrizaje en la Capa Fog de Adaptia:**

1. **Validación Sanitizada de Entradas (** **Form Requests** **):** En el **Capítulo 5, Sección 5.5** del documento base de la Universidad Continental (2026, p. 29), se exige: *"Todo dato recibido del sensor IoT (humedad, temperatura, planta\_id) se valida en formato y rango antes de almacenarse, rechazando payloads malformados"*[19]. Si el paquete llega corrupto, el servidor retorna un estado **HTTP 422 Unprocessable Entity**[23].
2. **Evaluador de Umbrales Hídricos:** Compara la humedad del suelo recibida contra los límites de la especie[24]. Si `humedad_suelo < humedad_minima`, genera de inmediato la alerta **"Riega hoy"**[14][24].

---

## 4\. CAPA 3: SERVIDOR Y PERSISTENCIA EN LA NUBE (*CLOUD LAYER*)

La **Capa Cloud** administra la lógica de negocio profunda, las transacciones comerciales de Garden Land Huancayo y el almacenamiento de series temporales telemétricas[5].

### **Cita Textual y Ubicación de Sustento:**

En la **Sección 3.2 "Monitoring System Proposal"** de Méndez-Guzmán et al. (2022, p. 11), se describe el rol del servidor en la nube:

> *"The cloud layer is made up of data storage and analytics services... used as a database with a time-series format, as well as for data analysis and visualization of results."*[5][25] *(La capa nube está compuesta por servicios de almacenamiento de datos y analítica... utilizada como base de datos con formato de series temporales, así como para el análisis de datos y la visualización de resultados)*.

En el **Capítulo 7, Secciones 7.2 y 7.3** del documento base de Adaptia (p. 31-32), se especifica el software servidor:

> *"Backend: Laravel (PHP)... Controladores, modelos, servicios. Base de datos del sistema: MySQL."*[10]

### **Componentes y Persistencia Dual en Adaptia:**

* **Framework Backend:** **Laravel (PHP)** implementando el patrón MVC con la lógica de negocio desacoplada en la clase de servicio `RecomendacionService`[8].
* **Persistencia Dual de Datos:**
  * **MySQL:** Motor relacional en entorno de producción para gestionar usuarios, ventas, inventario y lecturas[8][10].
  * **SQLite en Memoria:** Utilizado durante la ejecución de pruebas automatizadas en **Pest TDD** para acelerar la verificación sin contaminar MySQL[27].
* **Alineamiento de Seguridad OWASP:** Uso de **Eloquent ORM** para prevenir ataques de inyección SQL, *Rate Limiting* en endpoints de telemetría y protección de accesos individuales mediante **tokens aleatorios unívocos no predecibles**[28].

---

## 5\. CAPA 4: INTERFAZ Y APLICACIÓN (*APPLICATION LAYER*)

La **Capa de Aplicación** proporciona las interfaces gráficas orientadas a los diferentes actores del sistema (**Cliente**, **Vendedor** y **Administrador**)[5].

### **Cita Textual y Ubicación de Sustento:**

En la **Sección 3.2** de Méndez-Guzmán et al. (2022, p. 11), se señala:

> *"Finally, the application layer is an app which, through HTTP requests, makes requests to the server for the generation of comparative or analytical reports... historical data by variable, access to configuration."*[5][30] *(Finalmente, la capa de aplicación es una aplicación que, a través de solicitudes HTTP, realiza peticiones al servidor para la generación de reportes comparativos o analíticos... datos históricos por variable, acceso a la configuración)*.

En el **Capítulo 5, Sección 5.2** del documento base de Adaptia (p. 27), se define la tecnología del frontend:

> *"Frontend: Blade con Livewire (interactividad sin recargar página, ej. actualización del estado de la planta)."*[8]

---

## 6\. INFRAESTRUCTURA DE DESPLIEGUE Y CONTENEDORES (DOCKER)

Para garantizar la portabilidad y replicabilidad del entorno entre desarrollo, testing y producción, Adaptia empaqueta todos sus servicios mediante **Docker y Docker Compose**[8].

### **Cita Textual y Ubicación de Sustento:**

En el **Capítulo 10, Sección 10.4 "Orquestación con Docker Compose"** (p. 35) del documento base de la Universidad Continental (2026), se establece:

> *"Se describe la configuración del archivo docker-compose para ejecutar todos los servicios del sistema: servicio frontend, servicio backend, servicio base de datos."*[11]

### **Especificación de Archivo** **docker-compose.yml** **:**

```
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: adaptia_backend_laravel
    restart: unless-stopped
    ports:
      - "8000:8000"
    environment:
      DB_HOST: db
      DB_DATABASE: adaptia_db
    depends_on:
      - db

  db:
    image: mysql:8.0
    container_name: adaptia_mysql_db
    restart: always
    environment:
      MYSQL_DATABASE: adaptia_db
      MYSQL_ROOT_PASSWORD: secret_password
    ports:
      - "3306:3306"

```

*(Configuración orquestada para desplegar el servidor backend y la base de datos MySQL en contenedores aislados)*[11].

---

## 📊 MATRIZ DE TRAZABILIDAD DE LA ARQUITECTURA TECNOLÓGICA

| Capa Tecnológica     | Componentes y Tecnologías                                                                  | Función Principal en Adaptia                                      | Fuente Científica y Ubicación                                                                |
| -------------------- | ------------------------------------------------------------------------------------------ | ----------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| **1\. Device Layer** | ESP32, Sonda Capacitiva (`GPIO D34`), DHT22 (`GPIO D4`), BH1750 (`I2C`) | Captura y transmisión de parámetros ambientales; sin actuación hídrica. | Méndez-Guzmán et al. (2022, p. 10)[5][13] / Soibam & Vignesh (2025, p. 2028)[15][17] |
| **2\. Fog Layer**    | Endpoint API Laravel, `FormRequests`, Evaluador de Umbrales                                | Ingesta, sanitización JSON y respuesta `HTTP 422` ante errores.   | Méndez-Guzmán et al. (2022, p. 10)[5][21] / Ilyas et al. (2022, p. 4)[20][22]        |
| **3\. Cloud Layer**  | Backend Laravel MVC, MySQL, SQLite, Eloquent ORM, Docker Compose                           | Lógica de negocio, recomendación botánica, ORM y contenedores.    | Ilyas et al. (2022, p. 8)[7] / Univ. Continental (2026, p. 31)[10][11]                 |
| **4\. App Layer**    | Laravel Blade + Livewire, Dashboards interactivos                                          | Visualización reactiva en tiempo real ("Riega hoy") sin recargar. | Méndez-Guzmán et al. (2022, p. 11)[5][30] / Univ. Continental (2026, p. 27)[8]         |

---

### 📚 REFERENCIAS BIBLIOGRÁFICAS CITADAS

1. **Méndez-Guzmán, H. A., et al. (2022).** *IoT-Based Monitoring System Applied to Aeroponics Greenhouse*. Sensors, 22(15), 5646, pp. 1–28[4][5].
2. **Ilyas, A., et al. (2022).** *Software architecture for pervasive critical health monitoring system using fog computing*. Journal of Cloud Computing, 11(84), pp. 1–14[6][7].
3. **Soibam, S., & Vignesh, B. (2025).** *Design and Development of a Smart Plant Assistant*. International Journal for Research in Applied Science and Engineering Technology (IJRASET), 13(7), pp. 2025–2028[15].
4. **Universidad Continental (2026).** *Estructura de proyecto final: ADAPTIA – Sistema inteligente de recomendación, monitoreo IoT y gestión de plantas ornamentales*. Facultad de Ingeniería de Sistemas e Informática, Huancayo, Perú, pp. 1–43[1].