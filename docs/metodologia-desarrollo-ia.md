# METODOLOGÍA DE DESARROLLO DE SOFTWARE ASISTIDO POR IA GENERATIVA (SDLC-GenAI) PARA EL PROYECTO ADAPTIA

## 1. Introducción y justificación metodológica

Para el desarrollo del proyecto **Adaptia** se adopta el marco de trabajo de **Ingeniería de Software Inteligente propuesto por Singh (2026)**, denominado *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. Este marco integra la Inteligencia Artificial Generativa (GenAI) a lo largo de las diferentes etapas del ciclo de vida del desarrollo de software.

La propuesta surge como respuesta a las limitaciones de los enfoques tradicionales de desarrollo. En la **Sección 1, “Introduction”** del estudio de Singh (2026, p. a351), se señala:

> “Traditional software development processes are highly dependent on manual effort across requirement analysis, design, coding, testing, deployment, and maintenance, making them time-consuming and error-prone.” [1]

Esta afirmación plantea que los procesos tradicionales dependen considerablemente del trabajo manual durante el análisis de requisitos, diseño, codificación, pruebas, despliegue y mantenimiento, lo que puede incrementar el tiempo y la posibilidad de errores.

En el mismo apartado, Singh (2026, p. a351) introduce la Inteligencia Artificial Generativa como un nuevo paradigma para la ingeniería de software:

> “Generative Artificial Intelligence (GenAI), powered by Large Language Models (LLMs), has introduced a new paradigm in software engineering by enabling machines to generate human-like text, code, and system designs based on natural language inputs.” [1]

A partir de este enfoque, **Adaptia** emplea un flujo de desarrollo colaborativo entre el equipo de desarrollo y un agente de Inteligencia Artificial. En particular, se utiliza **Claude Code** como agente inteligente para apoyar diferentes actividades del proyecto, incluyendo el desarrollo del backend en Laravel, la interfaz reactiva mediante Blade y Livewire, el firmware en C++ para el ESP32 y la automatización de pruebas.

El marco metodológico se organiza en **siete capas**, las cuales permiten incorporar la IA generativa en diferentes actividades del ciclo de vida del software.

---

# 2. Aplicación de las 7 capas del Framework de Singh (2026) en Adaptia

## 2.1. Capa 1: Inteligencia de Requerimientos

### Requirement Intelligence Layer

### Fundamentación teórica

La primera capa corresponde a la **Inteligencia de Requerimientos**. De acuerdo con Singh (2026), esta capa permite transformar las necesidades expresadas en lenguaje natural en requisitos estructurados para el desarrollo del software.

En la **Sección 3.1, “Requirement Intelligence Layer”** (Singh, 2026, p. a352), se establece:

> “This layer processes natural language inputs such as user requirements, business logic, and system specifications using NLP techniques. It converts unstructured text into structured software requirements.” [2]

Por lo tanto, la función de esta capa consiste en procesar información proveniente de usuarios, reglas de negocio y especificaciones del sistema para convertirla en requisitos estructurados que puedan ser utilizados durante el desarrollo.

### Aplicación en Adaptia

En **Adaptia**, esta capa se aplica mediante el análisis de las necesidades identificadas en el contexto de **Garden Land Huancayo**, particularmente aquellas relacionadas con la selección de plantas y las dificultades que pueden presentarse durante su cuidado.

Mediante el diálogo técnico con **Claude Code**, estas necesidades se transforman en especificaciones funcionales y reglas de negocio para el sistema. Entre ellas se encuentran las reglas relacionadas con el **motor de recomendación botánica**, los criterios utilizados para seleccionar plantas y las condiciones necesarias para generar alertas relacionadas con el estado hídrico de las plantas.

De esta manera, la Inteligencia Artificial se utiliza como apoyo para pasar desde la descripción de una problemática del dominio hacia requisitos y reglas que posteriormente pueden implementarse en el software.

---

## 2.2. Capa 2: Gestión del Conocimiento

### Knowledge Management Layer

### Fundamentación teórica

La segunda capa corresponde a la **Gestión del Conocimiento**, cuyo propósito es mantener disponible el contexto necesario para que la Inteligencia Artificial pueda generar resultados coherentes con la arquitectura, las tecnologías y las reglas del proyecto.

En la **Sección 3.2, “Knowledge Management Layer”** (Singh, 2026, p. a352), se indica que esta capa contempla:

> “Design patterns, Coding standards, API libraries, Historical project data, Reusable modules. It improves the accuracy of AI-generated outputs.” [2]

Esto significa que la IA no debe trabajar únicamente a partir de instrucciones aisladas, sino considerando información relacionada con los patrones de diseño, estándares de programación, bibliotecas, información histórica y componentes reutilizables del proyecto. Según el estudio, disponer de este contexto contribuye a mejorar la precisión de los resultados generados por IA.

### Aplicación en Adaptia

En **Adaptia**, la gestión del conocimiento se materializa mediante el suministro de información técnica y funcional relacionada con el sistema.

El contexto proporcionado a **Claude Code** incluye el **catálogo comercial de plantas de Garden Land**, las reglas relacionadas con el dominio botánico y la estructura tecnológica del proyecto.

Asimismo, se establece el contexto de arquitectura del software, utilizando **Laravel bajo el patrón MVC** y una separación de responsabilidades mediante la capa de servicios, donde se encuentra, por ejemplo, `RecomendacionService`.

También se proporciona información correspondiente al componente IoT y a los pines de los sensores del **ESP32**: el sensor capacitivo conectado al `GPIO D34`, el sensor DHT22 al `GPIO D4` y el sensor BH1750 mediante I2C. El proyecto contempla monitoreo y alertas informativas; no incluye relé ni control automático de riego.

Finalmente, se considera la configuración de comunicación mediante **HTTPS/REST con JSON** y la utilización de **Docker Compose** para la orquestación de los servicios.

Por tanto, esta capa permite que Claude Code trabaje considerando el contexto real de Adaptia y no únicamente instrucciones genéricas de programación.

---

## 2.3. Capa 3: Desarrollo con IA Generativa

### Generative AI Development Layer

### Fundamentación teórica

La tercera capa constituye el núcleo de desarrollo asistido mediante Inteligencia Artificial Generativa. Singh (2026) plantea que los modelos de lenguaje pueden utilizarse para generar diferentes artefactos de software.

En la **Sección 3.3, “Generative AI Development Layer”** (Singh, 2026, p. a353), se establece que esta capa puede generar:

> “Source code, APIs, Database schemas, Frontend interfaces, System configurations. It supports multiple programming languages and frameworks.” [3]

Por consiguiente, esta capa contempla el uso de IA generativa para apoyar la construcción de código fuente, APIs, esquemas de bases de datos, interfaces y configuraciones del sistema.

### Aplicación en Adaptia

En **Adaptia**, esta capa se aplica mediante el uso de **Claude Code** como agente inteligente durante el desarrollo del sistema.

En el **backend**, la IA participa en la construcción de controladores como `TelemetriaApiController`, migraciones de **Eloquent** para MySQL y lógica asociada al sistema de recomendación implementada mediante `RecomendacionService`.

En el **frontend**, se utilizan **Blade y Livewire** para implementar componentes dinámicos capaces de reflejar información de telemetría sin requerir una recarga completa de la página.

En el componente **IoT**, Claude Code también participa en el desarrollo del firmware escrito en **C++ para el ESP32**, encargado de realizar las lecturas de los sensores y enviar los datos mediante solicitudes HTTP POST utilizando un *payload* en formato JSON.

Finalmente, en la infraestructura, la IA apoya la configuración de archivos como `Dockerfile` y `docker-compose.yml`, utilizados para la ejecución y aislamiento de los diferentes servicios del sistema.

---

## 2.4. Capa 4: Pruebas Inteligentes

### Intelligent Testing Layer

### Fundamentación teórica

La cuarta capa corresponde a las **Pruebas Inteligentes**, orientadas a automatizar diferentes niveles de evaluación del software generado.

En la **Sección 3.4, “Intelligent Testing Layer”** (Singh, 2026, p. a353), se establece:

> “Unit tests, Integration tests, Regression tests, Performance tests. It ensures correctness and reliability of generated software.” [3]

De acuerdo con esta propuesta, la utilización de IA dentro del proceso de pruebas permite trabajar con diferentes niveles de validación, con el objetivo de comprobar la corrección y confiabilidad del software.

Este enfoque adquiere especial importancia en sistemas IoT. Ferreira et al. (2023, p. 2), en la **Sección 2, “Challenges of Internet of Things Testing”**, señalan:

> “In IoT systems, devices usually have a stronger coupling between software and hardware, making testing more complex and costly... This context demands previous testing steps in simulators or emulators that could mitigate the risks of the system malfunctioning.” [4]

Los autores destacan así que la relación entre hardware y software incrementa la complejidad de las pruebas en sistemas IoT.

### Aplicación en Adaptia

En **Adaptia**, esta capa se implementa mediante una estrategia automatizada que combina **Pest TDD**, **SQLite en memoria** y **Cypress**.

En primer lugar, se utilizan **pruebas de humo (Smoke Tests)** mediante Pest TDD para verificar rápidamente que las rutas principales del sistema y los endpoints de la API IoT se encuentren disponibles y no generen errores `HTTP 500`.

En segundo lugar, se desarrollan **pruebas unitarias y de integración** para evaluar componentes como la lógica de recomendación y la recepción de datos provenientes del sistema de telemetría. Para acelerar la ejecución de estas pruebas se utiliza una base de datos **SQLite en memoria**.

Finalmente, se utilizan pruebas **End-to-End (E2E)** mediante **Cypress**, simulando el flujo completo de interacción de un usuario con el sistema a través del navegador.

De esta manera, la metodología de pruebas contempla tanto los componentes tradicionales del software como las particularidades asociadas al componente IoT de Adaptia.

---

## 2.5. Capa 5: Seguridad y Garantía de Calidad

### Security and Quality Assurance Layer

### Fundamentación teórica

La quinta capa corresponde a la **Seguridad y Garantía de Calidad**. Su finalidad es verificar que el software generado cumpla condiciones de seguridad, robustez y eficiencia.

En la **Sección 3.5, “Security and Quality Assurance Layer”** (Singh, 2026, p. a353), se establece:

> “Static code analysis, Vulnerability detection, Code optimization, Performance evaluation. It ensures that generated software is secure and efficient.” [5]

La capa contempla, por tanto, actividades relacionadas con el análisis estático, detección de vulnerabilidades, optimización y evaluación del rendimiento.

### Aplicación en Adaptia

En **Adaptia**, esta capa se aplica mediante prácticas de seguridad alineadas con las directrices de **OWASP**.

En primer lugar, se realiza la **validación de los datos recibidos desde el ESP32** mediante *Form Requests* de Laravel. Esto permite rechazar *payloads* JSON que no cumplan con las estructuras esperadas, utilizando respuestas **HTTP 422 Unprocessable Entity**.

En segundo lugar, se implementa un mecanismo de **acceso seguro a la información de telemetría**, utilizando tokens aleatorios y unívocos asociados a cada maceta. Esto permite evitar que el estado de las plantas quede expuesto mediante identificadores fácilmente predecibles.

Finalmente, se considera el uso de **Rate Limiting** sobre los endpoints de la API, con el objetivo de limitar solicitudes excesivas o tráfico anómalo procedente de los dispositivos IoT.

---

## 2.6. Capa 6: Generación de Documentación

### Documentation Generation Layer

### Fundamentación teórica

La sexta capa corresponde a la **Generación de Documentación**, cuyo objetivo es mantener la documentación técnica asociada al sistema conforme evoluciona el código.

En la **Sección 3.6, “Documentation Generation Layer”** (Singh, 2026, p. a353), se señala:

> “This layer automatically generates: Technical documentation, API documentation, User manuals, System design documents. Documentation remains synchronized with code changes.” [5]

La propuesta establece que la IA puede apoyar la generación de documentación técnica, documentación de APIs, manuales y documentos relacionados con el diseño del sistema, procurando que estos elementos permanezcan sincronizados con los cambios realizados en el software.

### Aplicación en Adaptia

En **Adaptia**, esta capa se aplica mediante la generación y actualización de diferentes artefactos técnicos del proyecto.

Entre estos se encuentran el **Diccionario de Datos**, los diagramas de clases y otros modelos relacionados con la estructura del sistema.

Asimismo, se emplean herramientas como **PlantUML** para la representación de diagramas y **Bizagi Modeler** para la elaboración de diagramas de procesos BPMN.

La IA se utiliza como apoyo para mantener coherencia entre la evolución del código y los artefactos de documentación, reduciendo la posibilidad de que la documentación quede desactualizada respecto de la implementación.

---

## 2.7. Capa 7: Aprendizaje Continuo

### Continuous Learning Layer

### Fundamentación teórica

La séptima capa corresponde al **Aprendizaje Continuo**, mediante el cual los resultados obtenidos durante el desarrollo, las pruebas y la ejecución del sistema son utilizados como retroalimentación para mejorar el software.

En la **Sección 3.7, “Continuous Learning Layer”** (Singh, 2026, pp. a353-a354), se explica que la mejora se produce mediante:

> “Developer corrections, Runtime logs, User feedback. It enables continuous model improvement.” [5]

De acuerdo con esta propuesta, las correcciones realizadas por los desarrolladores, los registros generados durante la ejecución y la retroalimentación de los usuarios constituyen fuentes de información para los ciclos posteriores de mejora.

### Aplicación en Adaptia

En **Adaptia**, esta capa se implementa mediante un ciclo iterativo de detección, análisis y corrección de errores.

Cuando **Pest TDD** identifica un fallo durante la ejecución de las pruebas, o cuando se presentan excepciones durante la ejecución o despliegue mediante **Docker**, estos resultados se utilizan como información de retroalimentación para **Claude Code**.

El agente puede analizar el error, proponer una corrección y apoyar la modificación del código correspondiente. Posteriormente, las pruebas se vuelven a ejecutar para comprobar si el problema ha sido solucionado.

Este ciclo se repite hasta alcanzar un resultado satisfactorio antes de integrar los cambios al repositorio de **GitHub**.

De esta forma, el desarrollo de Adaptia se plantea como un proceso iterativo en el que las pruebas y los errores encontrados se convierten en información para las siguientes iteraciones de desarrollo.

---

# 3. Principio Human-in-the-Loop

Aunque la metodología incorpora Inteligencia Artificial Generativa en diferentes actividades del ciclo de desarrollo, el framework mantiene la participación y supervisión humana como elemento fundamental.

En la **Sección 6, “Discussion”** de Singh (2026, p. a355), se afirma:

> “The integration of Generative AI into software engineering improves productivity and reduces human effort. However, human oversight remains essential to ensure correctness, security, and ethical compliance.” [6]

Esta afirmación establece que la incorporación de IA puede mejorar la productividad y reducir el esfuerzo humano, pero la supervisión de los desarrolladores continúa siendo necesaria para garantizar la corrección, seguridad y cumplimiento de criterios técnicos y éticos.

En **Adaptia**, el principio de **Human-in-the-Loop** se aplica manteniendo al equipo de desarrollo como responsable de validar las decisiones y resultados producidos con apoyo de Claude Code.

La supervisión humana se concentra principalmente en:

1. **Validar las reglas de negocio** relacionadas con el catálogo de plantas y el sistema de recomendación de Garden Land Huancayo.
2. **Verificar la configuración física del sistema IoT**, incluyendo la correcta asignación de sensores, actuadores y pines del ESP32.
3. **Revisar los resultados generados por la IA** antes de incorporarlos al proyecto.
4. **Comprobar la calidad y seguridad del software**, considerando los criterios y estándares establecidos para el proyecto.

Por lo tanto, Claude Code no sustituye la toma de decisiones del equipo de desarrollo, sino que funciona como un agente de apoyo dentro del proceso metodológico.

---

# 4. Síntesis de la aplicación metodológica

La aplicación del framework de Singh (2026) en Adaptia permite integrar la Inteligencia Artificial Generativa en diferentes etapas del desarrollo. La relación entre la fundamentación teórica y su implementación en el proyecto puede resumirse de la siguiente manera:

| Capa                                  | Fundamentación del framework                                                                  | Aplicación en Adaptia                                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| **1. Inteligencia de Requerimientos** | Convierte lenguaje natural y necesidades de negocio en requisitos estructurados.              | Claude Code apoya la transformación de las necesidades de Garden Land en reglas y especificaciones del sistema. |
| **2. Gestión del Conocimiento**       | Mantiene patrones, estándares, datos y contexto técnico para mejorar los resultados de la IA. | Se proporciona a Claude Code el contexto de Laravel, Garden Land, ESP32, Docker y las reglas del proyecto.      |
| **3. Desarrollo con IA Generativa**   | Genera código, APIs, esquemas, interfaces y configuraciones.                                  | Claude Code apoya el desarrollo de Laravel, Blade/Livewire, C++, API REST y Docker.                             |
| **4. Pruebas Inteligentes**           | Automatiza diferentes niveles de pruebas para verificar corrección y confiabilidad.           | Pest TDD, SQLite en memoria y Cypress permiten realizar pruebas de humo, unitarias, integración y E2E.          |
| **5. Seguridad y QA**                 | Busca vulnerabilidades, analiza código y evalúa seguridad y rendimiento.                      | Validación mediante Form Requests, tokens de acceso y Rate Limiting para los endpoints IoT.                     |
| **6. Generación de Documentación**    | Genera y mantiene documentación sincronizada con el código.                                   | Diccionario de datos, diagramas UML, PlantUML y BPMN mediante Bizagi Modeler.                                   |
| **7. Aprendizaje Continuo**           | Utiliza correcciones, logs y retroalimentación para mejorar el sistema.                       | Los errores de Pest TDD y Docker se utilizan como retroalimentación para Claude Code y nuevas iteraciones.      |

En conjunto, las siete capas permiten establecer una metodología de desarrollo en la que la IA Generativa interviene desde la interpretación de los requerimientos hasta las actividades de desarrollo, pruebas, seguridad, documentación y mejora continua. En el caso de Adaptia, esta metodología se materializa mediante la integración de **Claude Code, Laravel, Blade, Livewire, MySQL/SQLite, Pest TDD, Cypress, Docker y ESP32**, manteniendo siempre la supervisión del equipo de desarrollo.

---

# Referencias

**[1]** Singh, S. (2026). *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. Journal of Novel Research and Innovative Development (JNRID), 4(6), a351-a355. **Sección 1, “Introduction”, p. a351.**

**[2]** Singh, S. (2026). *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. **Secciones 3.1 y 3.2, pp. a352.**

**[3]** Singh, S. (2026). *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. **Secciones 3.3 y 3.4, p. a353.**

**[4]** Ferreira, V. G., Herrera, C. G., Souza, S. R. S., Santos, R. R., & Souza, P. S. L. (2023). *Software Testing Applied to the Development of IoT Systems: preliminary results*. Proceedings of the 8th Brazilian Symposium on Systematic and Automated Software Testing (SAST 2023), ACM, pp. 1-10. **Sección 2, “Challenges of Internet of Things Testing”, p. 2.**

**[5]** Singh, S. (2026). *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. **Secciones 3.5, 3.6 y 3.7, pp. a353-a354.**

**[6]** Singh, S. (2026). *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. **Sección 6, “Discussion”, p. a355.**

**[7]** Universidad Continental (2026). *Estructura de proyecto final: ADAPTIA – Sistema inteligente de recomendación, monitoreo IoT y gestión de plantas ornamentales*. Facultad de Ingeniería de Sistemas e Informática, Huancayo, Perú.