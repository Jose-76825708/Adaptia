# FRAMEWORK DE CICLO DE VIDA DE DESARROLLO DE SOFTWARE ASISTIDO POR IA (SDLC-AI) PARA ADAPTIA

## 1. Introducción y justificación metodológica

Para el desarrollo del proyecto **Adaptia** se propone el framework **SDLC-AI**, que integra Inteligencia Artificial Generativa (GenAI) a lo largo del ciclo de vida del software bajo supervisión humana. Su estructura principal conserva las **siete capas de Ingeniería de Software Inteligente de Singh (2026)** y las complementa con evidencia empírica sobre productividad y deuda técnica, con una visión de esfuerzo híbrido humano–IA y con una adaptación de la estimación funcional.

La propuesta surge como respuesta a las limitaciones de los enfoques tradicionales de desarrollo. En la **Sección 1, “Introduction”** del estudio de Singh (2026, p. a351), se señala:

> “Traditional software development processes are highly dependent on manual effort across requirement analysis, design, coding, testing, deployment, and maintenance, making them time-consuming and error-prone.” (Singh, 2026, p. a351)

Esta afirmación plantea que los procesos tradicionales dependen considerablemente del trabajo manual durante el análisis de requisitos, diseño, codificación, pruebas, despliegue y mantenimiento, lo que puede incrementar el tiempo y la posibilidad de errores.

En el mismo apartado, Singh (2026, p. a351) introduce la Inteligencia Artificial Generativa como un nuevo paradigma para la ingeniería de software:

> “Generative Artificial Intelligence (GenAI), powered by Large Language Models (LLMs), has introduced a new paradigm in software engineering by enabling machines to generate human-like text, code, and system designs based on natural language inputs.” (Singh, 2026, p. a351)

A partir de este enfoque, **Adaptia** emplea un flujo de desarrollo colaborativo entre el equipo de desarrollo y un agente de Inteligencia Artificial. En particular, se utiliza **Claude Code** como agente inteligente para apoyar diferentes actividades del proyecto, incluyendo el desarrollo del backend en Laravel, la interfaz reactiva mediante Blade y Livewire, el firmware en C++ para el ESP32 y la automatización de pruebas.

El marco metodológico se organiza en **siete capas**, las cuales permiten incorporar la IA generativa en diferentes actividades del ciclo de vida del software.

---

# 2. Aplicación de las 7 capas del Framework de Singh (2026) en Adaptia

## 2.1. Capa 1: Inteligencia de Requerimientos

### Requirement Intelligence Layer

### Fundamentación teórica

La primera capa corresponde a la **Inteligencia de Requerimientos**. De acuerdo con Singh (2026), esta capa permite transformar las necesidades expresadas en lenguaje natural en requisitos estructurados para el desarrollo del software.

En la **Sección 3.1, “Requirement Intelligence Layer”** (Singh, 2026, p. a352), se establece:

> “This layer processes natural language inputs such as user requirements, business logic, and system specifications using NLP techniques. It converts unstructured text into structured software requirements.” (Singh, 2026, p. a352)

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

> “Design patterns, Coding standards, API libraries, Historical project data, Reusable modules. It improves the accuracy of AI-generated outputs.” (Singh, 2026, p. a352)

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

> “Source code, APIs, Database schemas, Frontend interfaces, System configurations. It supports multiple programming languages and frameworks.” (Singh, 2026, p. a353)

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

> “Unit tests, Integration tests, Regression tests, Performance tests. It ensures correctness and reliability of generated software.” (Singh, 2026, p. a353)

De acuerdo con esta propuesta, la utilización de IA dentro del proceso de pruebas permite trabajar con diferentes niveles de validación, con el objetivo de comprobar la corrección y confiabilidad del software.

Este enfoque adquiere especial importancia en sistemas IoT. Ferreira et al. (2023, p. 2), en la **Sección 2, “Challenges of Internet of Things Testing”**, señalan:

> “In IoT systems, devices usually have a stronger coupling between software and hardware, making testing more complex and costly... This context demands previous testing steps in simulators or emulators that could mitigate the risks of the system malfunctioning.” (Ferreira et al., 2023, p. 2)

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

> “Static code analysis, Vulnerability detection, Code optimization, Performance evaluation. It ensures that generated software is secure and efficient.” (Singh, 2026, p. a353)

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

> “This layer automatically generates: Technical documentation, API documentation, User manuals, System design documents. Documentation remains synchronized with code changes.” (Singh, 2026, p. a353)

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

> “Developer corrections, Runtime logs, User feedback. It enables continuous model improvement.” (Singh, 2026, pp. a353-a354)

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

> “The integration of Generative AI into software engineering improves productivity and reduces human effort. However, human oversight remains essential to ensure correctness, security, and ethical compliance.” (Singh, 2026, p. a355)

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

# 5. Síntesis y análisis bibliográfico para el SDLC-AI

Las fuentes cumplen funciones diferentes dentro de la propuesta. Singh (2026) aporta la estructura del ciclo; los estudios empíricos informan los beneficios y riesgos que deben medirse; el marco de esfuerzo híbrido redefine qué trabajo se debe contabilizar; Lavazza et al. (2024) establece límites importantes para interpretar las medidas funcionales; y el AI-Driven Development Lifecycle (AI-DLC) aporta una cadencia AI-First basada en *Intents*, *Units* y *Bolts* (Raja SP, 2026).

| Fuente | Contribución relevante | Implicación para el SDLC-AI |
|---|---|---|
| Singh (2026), *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence* | Organiza la integración de GenAI en siete capas: requerimientos, conocimiento, desarrollo, pruebas, seguridad/QA, documentación y aprendizaje continuo. Advierte que la supervisión humana sigue siendo esencial. | Se conserva como columna vertebral del flujo. La IA propone y ejecuta tareas acotadas; el equipo responde por las decisiones y la aceptación de los artefactos. |
| Xu et al. (s. f.), *AI-Assisted Programming Decreases the Productivity of Experienced Developers by Increasing the Technical Debt and Maintenance Burden* | Analiza repositorios OSS con un diseño de diferencias en diferencias. Reporta mayor actividad de desarrollo tras Copilot, 2,4 % más re-trabajo de PR y una redistribución de trabajo hacia mantenedores experimentados; el resumen reporta 6,5 % más código revisado y una caída del 19 % en su productividad de código original. | La velocidad de producción no basta como indicador. Se añaden controles de revisión, retrabajo, mantenibilidad y carga de mantenimiento, con límites explícitos a la aceptación de código no comprendido. |
| Paradis et al. (s. f.), *How much does AI impact development speed? An enterprise-based randomized controlled trial* | RCT con 96 ingenieros de Google y una tarea empresarial compleja. La estimación ajustada más favorable equivale a cerca de 21 % menos tiempo; su intervalo de confianza es amplio y el resultado ajustado de mejor ajuste no alcanza significación estadística convencional. El beneficio varía según perfiles, pero la interacción por experiencia y horas de código no queda demostrada concluyentemente. | El 21 % es una hipótesis inicial para planificar pilotos, no una promesa ni un factor universal. Medir resultados locales por tarea y perfil y comunicar la incertidumbre. |
| Alaswad, Poovammal y Aljaddouh (2026), *Toward LLM-aware software effort estimation: a conceptual framework* | Define *Hybrid Intelligence Effort* (HIE) como esfuerzo conjunto del razonamiento asistido por LLM y la supervisión humana, articulado en cinco dimensiones. Es un marco conceptual, todavía sin coeficientes empíricos universales. | Registrar esfuerzo de interacción y supervisión además de tamaño funcional. Calibrar indicadores y pesos con datos propios; no presentar una ecuación universal como validada por el estudio. |
| Lavazza, Locoro y Meli (2024), *Software Development and Maintenance Effort Estimation Using Function Points and Simpler Functional Measures* | En datos ISBSG compara UFP, SFP y tSFP en desarrollo nuevo, extensiones y mejoras. No encuentra superioridad general de UFP en proyectos complejos; el error aumenta con complejidad para todas las medidas. | Mantener Puntos de Función como medida de tamaño funcional cuando haya requisitos suficientes, pero estimar complejidad y esfuerzo híbrido por separado. Usar SFP/tSFP en etapas tempranas como aproximación, no como sustituto universal. |
| Raja SP (2026), *AI-Driven Development Lifecycle (AI-DLC) Method Definition* | Propone que la IA inicie la planificación y descomposición de intenciones en unidades de trabajo; define *Bolts* como iteraciones de construcción y validación de horas o días, con humanos responsables de validar los artefactos y decisiones. | Usar *Intents → Units → Bolts* como cadencia operativa que se integra en las capas de Singh, sin sustituir sus Quality Gates ni transferir a la IA la aprobación estratégica. |

**Alcance de la evidencia.** Los resultados de Xu et al. proceden de repositorios open source y miden efectos asociados a la adopción de Copilot; no deben extrapolarse automáticamente a Adaptia ni a todas las organizaciones. El RCT de Google estudió un conjunto concreto de herramientas y una tarea en un entorno interno. Las dos fuentes sustentan hipótesis y controles que deben evaluarse localmente, no estimaciones garantizadas de resultado (Xu et al., s. f.; Paradis et al., s. f.).

# 6. Flujo operativo del SDLC-AI: siete capas y puertas de calidad

El ciclo se ejecuta de forma iterativa por funcionalidad o incremento. Cada artefacto producido con IA debe tener responsable humano, criterios de aceptación y evidencia de validación. Una puerta que no se supera bloquea la integración o el despliegue; no se compensa un fallo de calidad con mayor velocidad de generación. Esta gobernanza operacionaliza el principio *Human-in-the-Loop* de Singh (2026) y responde al retrabajo observado por Xu et al. (s. f.).

## Cadencia AI-First: Intents, Units y Bolts

El AI-DLC se incorpora como **modelo operativo complementario** a las siete capas de Singh; no elimina ni renumera capas, gates, responsabilidades de seguridad ni actividades de mantenimiento. La arquitectura metodológica puede leerse en dos fases principales, alineadas con el flujo visual:

1. **Inception:** las capas 1 y 2 elaboran el Intent, generan y validan su descomposición en Units y aseguran que el agente disponga de contexto confiable. G1 y G2 son condiciones de entrada para la construcción.
2. **Construction:** las capas 3 y 4 ejecutan uno o más Bolts por Unit. G3 valida el cambio de código y G4 valida las pruebas. Las capas 5 y 6 completan los controles de seguridad/calidad y documentación; la capa 7 recoge resultados para el siguiente ciclo.

Esta organización en fases da una cadencia a las siete capas, no una ruta alternativa. Cada incremento recorre los controles que le aplican y no omite los gates posteriores de seguridad, documentación o aprendizaje antes de la aceptación o liberación correspondiente (Singh, 2026; Raja SP, 2026).

### Jerarquía de trabajo y responsabilidades

- **Intent:** declaración de alto nivel del resultado de negocio o técnico esperado. En Adaptia puede ser, por ejemplo, incorporar una capacidad del sistema de monitoreo; no es todavía una orden de implementar detalles técnicos.
- **Unit:** bloque de trabajo cohesivo, verificable y tan independiente como sea razonable, derivado del Intent. Claude Code puede proponer Units y dependencias; el responsable de producto y el equipo técnico ajustan el alcance, los límites, los riesgos, los criterios de aceptación y las decisiones de acoplamiento antes de aprobarlos.
- **Bolt:** micro-iteración de construcción y validación de alcance acotado, normalmente de horas o días, no un Sprint fijo de varias semanas. Una Unit puede requerir uno o varios Bolts secuenciales; solo deben paralelizarse cuando las dependencias y contratos estén definidos. Cada Bolt entrega un incremento comprobable, no necesariamente una Unit completa (Raja SP, 2026).

### Inversión de conversación y aprobación humana

En el flujo **AI-First**, el equipo proporciona el Intent y Claude Code toma la iniciativa proponiendo un **Level 1 Plan**: descomposición preliminar en Units, dependencias, tareas, riesgos, criterios de aceptación y alternativas de arquitectura. Luego puede refinar el plan en subtareas o Bolts. La propuesta no es autorización automática: el humano audita alcance, contexto, riesgos, arquitectura, seguridad y alineación con el negocio, modifica las opciones cuando haga falta y aprueba las decisiones estratégicas antes de que el agente avance a ejecución. Durante el Bolt, Claude Code puede generar cambios y pruebas; las personas conservan la responsabilidad por revisar, validar y aceptar resultados (Raja SP, 2026; Singh, 2026).

Por tanto, se invierte quién inicia la propuesta, no quién responde por el producto: la IA propone y ejecuta tareas delegadas bajo límites explícitos; el equipo decide, aprueba y asume la gobernanza. El *Human-in-the-Loop* de la sección 3 sigue siendo obligatorio en todos los puntos de decisión de riesgo o impacto.

## 6.1. Capa 1 — Inteligencia de Requerimientos

**Propósito.** Convertir necesidades de Garden Land y de los usuarios en requisitos trazables, reglas de negocio, restricciones y criterios verificables. La IA puede resumir entrevistas, identificar ambigüedades, proponer historias de usuario y generar casos de aceptación, pero el responsable del producto valida cada decisión de dominio (Singh, 2026).

**Actividades del ciclo:**

1. Registrar el problema, los actores, los procesos actuales y las restricciones conocidas.
2. Solicitar a Claude Code que estructure los requisitos en historias, reglas y criterios de aceptación; hacer explícitos los datos faltantes en vez de pedir al modelo que los invente.
3. Solicitar a Claude Code un Level 1 Plan con alternativas y descomposición inicial del Intent en Units independientes, dependencias, riesgos y Bolts sugeridos; el plan se considera borrador hasta su revisión.
4. El responsable de producto y el equipo técnico corrigen, priorizan y aprueban el alcance, las decisiones estratégicas y los criterios; definen qué queda fuera del incremento.
5. Vincular cada requisito y Unit aprobada con diseño, pruebas, cambios y evidencia de aceptación.

**Quality Gate G1 — Requerimientos listos.** No iniciar implementación ni aprobar el Level 1 Plan hasta que cada requisito y Unit del alcance tenga identificador, actor o disparador, resultado esperado, criterios comprobables, dependencias y reglas de error. Las ambigüedades de seguridad, datos, persistencia o interacción con sensores, así como las decisiones estratégicas y arquitectónicas de alto impacto, deben resolverse por el responsable humano. La trazabilidad también sirve de base para contar tamaño funcional y evitar subestimar requisitos omitidos (Singh, 2026; Raja SP, 2026; Lavazza et al., 2024).

**Dimensiones HIE dominantes:** completitud del contexto (información disponible para el modelo) e impacto de transformación (alcance funcional esperado) (Alaswad et al., 2026).

## 6.2. Capa 2 — Gestión del Conocimiento

**Propósito.** Asegurar que el agente recibe contexto correcto, vigente y limitado al trabajo autorizado. Para Adaptia incluye convenciones Laravel, responsabilidades de los Services, esquema de datos aprobado, reglas del recomendador, contrato JSON de telemetría y restricciones físicas del ESP32 (Singh, 2026).

**Actividades del ciclo:**

- Recuperar documentación versionada, contratos y ejemplos pertinentes; indicar explícitamente las fuentes de verdad.
- Comprobar si la tarea afecta módulos existentes o decisiones arquitectónicas que no están documentadas.
- Mantener actualizado el contexto de la tarea con alcance, archivos relevantes, convenciones y límites.
- No incluir credenciales, tokens, datos personales ni secretos en prompts. El uso de servicios externos debe ajustarse a las políticas de privacidad y al manejo de datos del proyecto.

**Quality Gate G2 — Contexto suficiente y seguro.** El responsable revisa que el contexto sea pertinente, consistente con el repositorio y no contenga secretos. Si falta información que pueda cambiar el diseño o el comportamiento, la tarea vuelve a G1 o se solicita una decisión humana. Se registra la fuente de las restricciones relevantes; no se almacena razonamiento interno del modelo como evidencia.

**Dimensiones HIE dominantes:** completitud de contexto y complejidad de razonamiento, esta última inferida únicamente mediante observables como ambigüedad, respuestas inconsistentes y correcciones requeridas, no mediante suposiciones sobre estados internos del LLM (Alaswad et al., 2026).

## 6.3. Capa 3 — Desarrollo con IA Generativa

**Propósito.** Construir cambios pequeños y trazables que satisfagan requisitos aprobados. La ejecución de esta capa forma, junto con la Capa 4, la fase de Construction organizada en Bolts: ciclos breves de implementación y validación de horas o días. Un Bolt trabaja sobre un subconjunto aprobado de una Unit y puede generar uno o varios cambios incrementales. Claude Code puede proponer o modificar controladores, Services, modelos, migraciones, vistas, endpoints y firmware, siempre respetando la arquitectura de Adaptia: Route → Controller → Service → Eloquent/Model → Database. La lógica de negocio permanece en Services (Raja SP, 2026).

**Actividades del ciclo:**

1. Seleccionar una Unit aprobada y el Bolt actual de su plan; confirmar sus límites, dependencias, criterio de finalización y relación con los requisitos.
2. Pedir a Claude Code el plan detallado del Bolt; el humano confirma el alcance de ejecución y resuelve las decisiones de arquitectura, riesgos o diseño que no estén previamente aprobadas.
3. Generar el cambio junto con pruebas y actualización documental pertinentes, evitando aceptar fragmentos que el responsable no pueda explicar.
4. Revisar diffs por archivo: compatibilidad, validación, autorización, manejo de errores, efectos sobre datos, dependencias y comportamiento existente.
5. Para cambios IoT, validar contrato, unidades, rangos, frecuencia y tolerancia a pérdida de conectividad; no asumir que simulación sustituye pruebas físicas.
6. Tratar cambios no previstos o que excedan el alcance como motivo para pausar el Bolt y pedir una decisión humana o replanificarlo; no permitir expansión autónoma del alcance.

**Quality Gate G3 — Auditoría de código del Bolt.** Antes de pasar de desarrollo a pruebas formales y de considerar superada la auditoría de construcción, cada diff debe estar asociado a un requisito y Unit aprobados, limitarse al alcance del Bolt y ser comprendido por un revisor humano. Revisar arquitectura, seguridad, validación, compatibilidad, cambios de datos y mantenibilidad; ejecutar análisis estático disponible (incluido SonarQube cuando esté configurado). No se integra código con fallos conocidos, lógica de negocio en Controllers contraria a la convención del proyecto, migraciones incompatibles o modificaciones colaterales sin justificar. Si G3 falla, el Bolt sigue abierto: Claude Code propone una corrección y el equipo vuelve a revisar el cambio. Esta auditoría es esencial porque el aumento de producción puede ir acompañado por más retrabajo y carga de mantenimiento (Singh, 2026; Xu et al., s. f.; Raja SP, 2026).

**Dimensiones HIE dominantes:** impacto de transformación, ciclos iterativos y complejidad de razonamiento (Alaswad et al., 2026). Los cambios que cruzan varias capas, tablas, APIs o componentes ESP32 reciben estimación y revisión reforzadas.

## 6.4. Capa 4 — Pruebas Inteligentes

**Propósito.** Comprobar, dentro del mismo Bolt de construcción, que el comportamiento implementado satisface los criterios aprobados y que las regresiones se detectan antes de integrar. La IA puede sugerir casos límite y generar esqueletos de pruebas; el equipo revisa que las pruebas no repitan el mismo supuesto defectuoso que el código (Singh, 2026; Raja SP, 2026).

**Estrategia por nivel:**

- **Unitarias:** lógica pura de Services y reglas de cálculo; Pest.
- **Integración:** persistencia, relaciones, autorización y contratos API; Pest con base configurada para pruebas.
- **Regresión:** escenarios de módulos existentes afectados por el cambio.
- **E2E:** recorridos críticos de usuarios mediante Cypress cuando el entorno esté disponible.
- **IoT:** pruebas de contrato y payload, casos inválidos, desconexión, valores extremos y simulación/emulación antes de pruebas con el hardware real. La mayor unión entre software y hardware vuelve más compleja la validación IoT, por lo que la simulación reduce riesgos pero no sustituye la comprobación en dispositivo (Ferreira et al., 2023).

**Quality Gate G4 — Pruebas aprobadas y cierre del Bolt.** Todas las pruebas exigidas por el cambio deben ejecutarse y aprobarse; cada criterio de aceptación debe tener al menos una prueba o una verificación manual documentada. Los fallos no se silencian ni se eliminan para obtener una ejecución verde. Se requiere revisión del caso de prueba generado por IA y cobertura de caminos negativos relevantes, especialmente validación de payloads y errores. Un Bolt no se cierra ni se da paso al siguiente Bolt de la Unit hasta que haya superado G3 (auditoría de código) y G4 (pruebas aprobadas). Si G4 falla, el trabajo vuelve a construcción dentro del mismo Bolt, se corrige y se repiten las pruebas; G5, G6 y G7 siguen siendo controles aplicables para aceptación, liberación y aprendizaje posteriores (Raja SP, 2026; Singh, 2026).

**Dimensiones HIE dominantes:** esfuerzo de supervisión humana, impacto de transformación y ciclos iterativos (Alaswad et al., 2026).

## 6.5. Capa 5 — Seguridad y Garantía de Calidad

**Propósito.** Evaluar seguridad, robustez, rendimiento, privacidad y mantenibilidad antes de liberar. En Adaptia son especialmente relevantes la validación de entradas del endpoint IoT, autorización, tokens asociados a macetas, rate limiting, asignación de sensores y protección de datos de clientes.

**Quality Gate G5 — Seguridad y calidad.** Antes de integrar o desplegar:

- Validar entradas en el servidor y respuestas de error coherentes (por ejemplo, HTTP 422 para datos no válidos).
- Verificar autorización y acceso a los datos por rol/propietario; no confiar en identificadores no predecibles como único control.
- Revisar secretos, dependencias, consultas, exposición de información, límites de tasa y manejo de errores.
- Ejecutar análisis estático y verificaciones automatizadas disponibles en el repositorio, incluido SonarQube cuando esté configurado.
- No dejar vulnerabilidades críticas o altas conocidas sin mitigación aprobada; una excepción debe tener responsable, justificación, alcance y fecha de revisión.
- Confirmar mantenibilidad: nombres, separación de responsabilidades, ausencia de duplicación evitable y documentación de decisiones no obvias.

La mayor velocidad de generación no elimina el coste de verificar la calidad ni las obligaciones de mantenimiento (Singh, 2026; Xu et al., s. f.). Los controles se registran como evidencia del cambio, no como una declaración genérica de que “la IA lo revisó”.

**Dimensiones HIE dominantes:** supervisión humana, impacto de transformación y complejidad de razonamiento (Alaswad et al., 2026).

## 6.6. Capa 6 — Generación de Documentación

**Propósito.** Mantener sincronizados código, contratos, modelos y guías. Claude Code puede redactar documentación de API, instrucciones, diccionario de datos y diagramas PlantUML, pero la persona responsable confirma que describan la implementación real (Singh, 2026).

**Quality Gate G6 — Documentación consistente.** Actualizar contratos y documentación afectada por cambios de API, base de datos, configuración, seguridad o hardware. Revisar nombres de tablas y claves foráneas con el esquema aprobado, y verificar que instrucciones ejecutables correspondan al código. La documentación requerida forma parte de la Definition of Done.

**Dimensiones HIE dominantes:** completitud del contexto, ciclos iterativos e impacto de transformación (Alaswad et al., 2026).

## 6.7. Capa 7 — Aprendizaje Continuo

**Propósito.** Cerrar el ciclo con evidencia de operación, revisión y retroalimentación. Los errores de pruebas, incidentes, correcciones de PR, alertas y opiniones de usuarios se convierten en acciones priorizadas; no se usan automáticamente para modificar reglas o datos sin revisión (Singh, 2026).

**Quality Gate G7 — Aprendizaje verificable.** Para cada incremento se revisan defectos escapados, retrabajo, tiempo de revisión, excepciones de seguridad y desviación entre esfuerzo estimado y real. Los aprendizajes se convierten en una actualización versionada de requisitos, pruebas, instrucciones o conocimiento del proyecto. Se excluyen secretos y datos personales de los registros y prompts.

**Dimensiones HIE dominantes:** las cinco dimensiones, con foco en los ciclos de refinamiento y el esfuerzo de supervisión (Alaswad et al., 2026).

## 6.8. Matriz de puertas de calidad

| Puerta | Momento | Evidencia mínima para continuar | Responsable de aceptación |
|---|---|---|---|
| G1 Requerimientos listos | Antes de estimar/implementar | Requisito trazable, criterios verificables, límites y ambigüedades resueltos | Responsable de producto/dominio |
| G2 Contexto seguro | Antes de solicitar generación | Fuentes pertinentes, restricciones vigentes, ausencia de secretos y contexto suficiente para el plan de trabajo | Responsable técnico |
| G3 Cambio auditado | Después de generar código y antes de pruebas formales | Diff dentro del alcance del Bolt, explicado, trazable, revisado por humano y sin fallos de análisis estático bloqueantes | Desarrollador/revisor |
| G4 Pruebas aprobadas | Antes de cerrar el Bolt o avanzar al siguiente | Suite pertinente en verde, criterios cubiertos y pruebas revisadas; fallos devuelven el mismo Bolt a construcción | Desarrollador/revisor |
| G5 Seguridad y calidad | Antes de integrar/desplegar | Validaciones de seguridad/QA aprobadas y excepciones documentadas | Responsable técnico |
| G6 Documentación actualizada | Antes de cerrar el incremento | Contratos y documentos afectados coherentes con código | Responsable del cambio |
| G7 Aprendizaje capturado | En retrospectiva/revisión de operación | Métricas, desviaciones y acciones de mejora registradas | Equipo del proyecto |

Los umbrales cuantitativos adicionales (por ejemplo, cobertura mínima, presupuesto de retrabajo o tiempo máximo de revisión) deben definirse con una línea base de Adaptia y revisarse periódicamente. No se atribuyen valores universales a los estudios citados.

# 7. Esfuerzo híbrido humano–IA

Alaswad et al. (2026) proponen entender el esfuerzo como *Hybrid Intelligence Effort*: el coste conjunto de razonamiento delegado al LLM y supervisión humana necesario para producir artefactos correctos y desplegables. Las cinco dimensiones siguientes deben registrarse por historia, tarea o incremento. Se miden mediante trazas observables; no implican acceso a estados internos del modelo y no existe todavía una escala universalmente calibrada.

| Dimensión HIE | Pregunta operacional | Indicadores observables propuestos en Adaptia | Capas donde se registra principalmente |
|---|---|---|---|
| **Complejidad de razonamiento del LLM** | ¿Qué dificultad observable hubo para alinear las instrucciones y restricciones con una solución aceptable? | Ambigüedades, inconsistencias, errores de salida, necesidad de descomponer el problema y correcciones sustantivas. | Requerimientos, desarrollo, pruebas y seguridad |
| **Completitud de contexto e información** | ¿Estaba disponible la información necesaria al iniciar la interacción? | Preguntas de aclaración por información faltante, fuentes recuperadas, decisiones no documentadas y tiempo dedicado a preparar contexto. | Requerimientos y gestión del conocimiento |
| **Impacto de transformación del código** | ¿Cuánto se propaga el cambio entre archivos, interfaces y subsistemas? | Archivos/componentes/tablas/API/sensores afectados, cambios de esquema, dependencias y rutas de regresión. | Diseño, desarrollo, pruebas y QA |
| **Ciclos de razonamiento iterativo** | ¿Cuántas rondas prompt–respuesta–refinamiento fueron necesarias hasta lograr aceptación? | Número de iteraciones, correcciones y re-ejecuciones de pruebas; motivo de cada ciclo. | Desarrollo y aprendizaje continuo |
| **Esfuerzo de supervisión humana** | ¿Cuánto trabajo humano fue necesario para asumir responsabilidad por el resultado? | Horas/minutos de revisión, pruebas, corrección, validación de dominio, seguridad, integración y documentación. | Todas las capas, en especial pruebas, QA e integración |

Para evitar que las dimensiones se confundan, el registro distingue condiciones iniciales (completitud de contexto), dificultad que emerge (complejidad de razonamiento), extensión del cambio (impacto), iteraciones observadas (ciclos) y responsabilidad humana efectivamente ejercida (supervisión). Cada dato debe asociarse a una tarea y al tipo de actividad, sin almacenar prompts sensibles ni convertir el conteo de tokens en una medida de calidad (Alaswad et al., 2026).

# 8. Productividad esperada y evaluación del beneficio

El RCT de Paradis et al. (s. f.) encontró que quienes recibieron las herramientas estudiadas completaron la tarea en un promedio aproximado de 96 minutos frente a 114 minutos en el grupo de control. El modelo ajustado de mejor ajuste estimó una reducción del tiempo equivalente a alrededor de **21 %**; sin embargo, el intervalo de confianza fue amplio (95 % CI reportado: −0,51 a 0,03 para el coeficiente logarítmico) y el efecto ajustado de ese modelo no fue estadísticamente significativo a p < 0,05 (p = 0,086). El análisis sin los mismos ajustes sí encontró diferencia en tiempo (p = 0,038). La lectura correcta es una señal prometedora con incertidumbre, no una garantía de que cada proyecto ganará 21 %.

El estudio también encontró que más horas diarias de programación y mayor nivel de seniority estaban asociados a menor tiempo de tarea. La interacción entre IA y horas de programación fue grande pero no estadísticamente significativa; las interacciones con seniority y frecuencia de uso tampoco fueron significativas. Por tanto, las diferencias por experiencia y horas de código deben tratarse como factores a medir, no como moderadores causales ya demostrados por este RCT (Paradis et al., s. f.).

**Aplicación en Adaptia:**

1. Usar el 21 % únicamente como hipótesis inicial para diseñar una evaluación, no para reducir automáticamente presupuesto o plazos.
2. Comparar tareas similares con y sin asistencia, o comparar una línea base histórica con tareas equivalentes; registrar complejidad, seniority y horas de código habituales.
3. Medir tiempo hasta aceptación, incluyendo preparación de contexto, interacción, revisión, retrabajo, pruebas, seguridad, integración y documentación. No medir solo el tiempo de generación del primer borrador.
4. Reportar tamaño de muestra, dispersión, intervalos de incertidumbre y tipo de tarea. Separar velocidad de producción, calidad, defectos escapados y mantenimiento.
5. Actualizar la estimación de productividad de Adaptia conforme existan datos suficientes. No trasladar sin validación el efecto de herramientas internas de Google a Claude Code, Laravel o firmware ESP32.

Xu et al. (s. f.) refuerzan la necesidad de medir efectos netos: el aumento de actividad puede coincidir con más retrabajo y redistribución de la carga hacia personas experimentadas. Por eso, el indicador de éxito es la entrega aceptada y sostenible, no líneas de código ni sugerencias generadas.

# 9. Estimación de esfuerzo: Puntos de Función + HIE

## 9.1. Principios

Los Puntos de Función (FP) ofrecen una medida funcional que puede elaborarse antes de disponer del código. Lavazza et al. (2024) comparan Puntos de Función no ajustados (UFP) con medidas simplificadas, entre ellas SFP y tSFP, y no encuentran que UFP sea generalmente más precisa en proyectos complejos. También observan que el error aumenta con la complejidad cualquiera sea la medida funcional. En consecuencia, el SDLC-AI conserva la medida funcional como una base de alcance, pero no la trata como medida suficiente de complejidad ni como sustituto del esfuerzo de supervisión descrito por Alaswad et al. (2026).

## 9.2. Procedimiento de estimación

**Paso 1 — Delimitar el alcance funcional.** Fijar frontera del sistema, actores, procesos y funciones incluidas en el incremento. Identificar altas, cambios y eliminaciones respecto del software existente; esta distinción importa al estimar desarrollo nuevo, extensiones y mejoras (Lavazza et al., 2024).

**Paso 2 — Medir tamaño funcional.** Cuando el detalle lo permita, contar UFP con el método acordado por el equipo, clasificando funciones transaccionales (entradas externas, salidas externas y consultas externas) y datos lógicos (archivos lógicos internos y archivos de interfaz externa), con complejidad según reglas FP. En etapas tempranas o con requisitos todavía incompletos, emplear SFP/tSFP como aproximación rápida y marcar el resultado como preliminar. Registrar método, supuestos, frontera y persona estimadora.

**Paso 3 — Evaluar complejidad y riesgo por separado.** Para la historia, registrar factores como número de interfaces y dependencias, cambios de esquema, integración de módulos, seguridad/datos, variabilidad de reglas y acoplamiento hardware–software. Clasificarlos con una escala ordinal definida por el equipo y justificar cada calificación. No inflar los FP para simular complejidad, porque la evidencia muestra que el tamaño funcional por sí solo no representa adecuadamente ese efecto (Lavazza et al., 2024).

**Paso 4 — Estimar el perfil HIE.** Para cada una de las cinco dimensiones de la sección 7, asignar una valoración inicial cualitativa (por ejemplo, baja/media/alta) y una justificación observable: calidad del contexto, alcance de transformación, interacciones esperadas, riesgo de razonamiento y validación humana prevista. No convertir estas etiquetas en coeficientes universales; el marco HIE todavía requiere operacionalización y calibración empírica (Alaswad et al., 2026).

**Paso 5 — Convertir tamaño y perfil en esfuerzo.** Calcular una línea base con la productividad histórica de Adaptia para trabajo comparable:

> **Esfuerzo base (horas-persona) = tamaño funcional (FP) / productividad histórica local (FP por hora-persona)**

La productividad de referencia debe distinguir, cuando existan datos suficientes, entre trabajo sin asistencia y trabajo asistido, y comparar incrementos de complejidad similar. Cuando aún no exista productividad FP propia, documentar una analogía con tareas comparables y expresar un rango, no una cifra precisa sin evidencia. Hay dos formas de aplicar la base sin duplicar esfuerzo:

- **Estimación top-down:** usar una productividad histórica *end-to-end* comparable para calcular el esfuerzo total; ajustar el rango solo con efectos de asistencia medidos localmente. El perfil HIE ayuda a explicar y distribuir ese esfuerzo entre actividades, pero no se vuelve a sumar como horas si ya está incluido en la productividad observada.
- **Estimación bottom-up:** estimar horas-persona por categoría y sumarlas: formulación/contexto + construcción/interacción IA + revisión + corrección/retrabajo + pruebas + seguridad/QA + integración/despliegue + documentación. Los FP sirven como comprobación del alcance, no como una cantidad adicional que se suma a las horas.

El tamaño funcional informa alcance; el perfil de complejidad e HIE modifica la distribución y el rango de esfuerzo, calibrados con incrementos observados. La reducción atribuible a IA se estima únicamente a partir de productividad observada localmente, evitando descontar dos veces el mismo ahorro en construcción y en la productividad global.

**Paso 6 — Registrar costes y esfuerzo por separado.** Separar horas humanas de revisión, pruebas y corrección de los costes de uso del modelo (si se miden), y distinguir ambos del coste de mantenimiento futuro. El gasto computacional no equivale por sí mismo a esfuerzo humano ni a calidad del resultado (Alaswad et al., 2026).

**Paso 7 — Reestimar después de la primera iteración y al cierre.** Sustituir supuestos por datos observados: ciclos reales, tiempo humano, cambios propagados, re-trabajo, resultados de gates y defectos. Comparar estimado con real por tipo de trabajo y usar los registros para ajustar productividad y pesos propios. No presentar esta fórmula operacional como el modelo numérico validado por Alaswad et al.; ese artículo propone una base conceptual y deja la calibración para estudios empíricos futuros (Alaswad et al., 2026).

## 9.3. Ejemplo cualitativo

Una modificación localizada de una vista de historial puede tener pocos FP y bajo impacto de transformación, pero aun así requerir esfuerzo de contexto, revisión de autorización y pruebas de regresión. En cambio, una función de telemetría que agrega contrato HTTP, validación, persistencia, evaluación de umbrales y alerta afecta más interfaces; su perfil de transformación, seguridad y pruebas IoT eleva el rango de estimación aunque el número funcional de transacciones parezca moderado. La dimensión de complejidad y el HIE explican esas diferencias que una medida de tamaño aislada puede omitir (Alaswad et al., 2026; Lavazza et al., 2024).

# 10. Métricas de evaluación y mejora continua

Para valorar el SDLC-AI se recomienda mantener un tablero por incremento con métricas balanceadas:

| Categoría | Métricas recomendadas |
|---|---|
| Flujo y velocidad | Tiempo desde inicio hasta aceptación; duración de cada Bolt; tiempo humano por actividad; tamaño funcional planificado y entregado; ciclos de interacción; Bolts reabiertos por fallos en G3/G4 |
| Calidad | Pruebas aprobadas/fallidas, defectos detectados antes y después del despliegue, regresiones, incidentes y conformidad con criterios |
| Deuda y mantenimiento | Re-trabajo por PR, cambios solicitados en revisión, tiempo de revisión, retrabajo posterior a integración y deuda priorizada |
| Esfuerzo híbrido | Valores cualitativos y observables de las cinco dimensiones HIE; horas de revisión, prueba, corrección e integración |
| Seguridad y gobernanza | Hallazgos por severidad, excepciones abiertas, trazabilidad de aceptación, documentación actualizada y exposición de datos |

Estas métricas permiten contrastar las mejoras de tiempo con sus costes de validación y mantenimiento. Deben interpretarse con tamaño de muestra, tipo de tarea y experiencia; los resultados de Google y de repositorios OSS son referencias para formular hipótesis, no una línea base propia de Adaptia (Paradis et al., s. f.; Xu et al., s. f.). La retroalimentación de métricas actualiza las instrucciones, los casos de prueba, las estimaciones y los criterios de aceptación bajo control humano (Singh, 2026).

# Referencias Bibliográficas

**[1]** Singh, S. (2026). *A Framework for Intelligent Software Engineering Using Generative Artificial Intelligence*. *Journal of Novel Research and Innovative Development (JNRID), 4*(6), a351-a355.

**[2]** Ferreira, V. G., Herrera, C. G., Souza, S. R. S., Santos, R. R., & Souza, P. S. L. (2023). *Software Testing Applied to the Development of IoT Systems: preliminary results*. Proceedings of the 8th Brazilian Symposium on Systematic and Automated Software Testing (SAST 2023), ACM, pp. 1-10.

**[3]** Xu, F. (Amber), Medappa, P. K., Tunc, M. M., Vroegindeweij, M., & Fransoo, J. C. (s. f.). *AI-Assisted Programming Decreases the Productivity of Experienced Developers by Increasing the Technical Debt and Maintenance Burden*. Manuscrito proporcionado en el corpus documental del proyecto; análisis de actividad y retrabajo en repositorios open source tras la adopción de GitHub Copilot. La copia consultada no especifica publicación, año ni DOI.

**[4]** Paradis, E., Grey, K., Madison, Q., Nam, D., Macvean, A., Meimand, V., Zhang, N., Ferrari-Church, B., & Chandra, S. (s. f.). *How much does AI impact development speed? An enterprise-based randomized controlled trial*. Estudio de Google con 96 ingenieros de software. Manuscrito proporcionado en el corpus documental del proyecto; la copia consultada no especifica venue ni DOI.

**[5]** Alaswad, F., Poovammal, E., & Aljaddouh, B. (2026). Toward LLM-aware software effort estimation: a conceptual framework. *Frontiers in Artificial Intelligence, 9*, 1772418. https://doi.org/10.3389/frai.2026.1772418

**[6]** Lavazza, L., Locoro, A., & Meli, R. (2024). Software Development and Maintenance Effort Estimation Using Function Points and Simpler Functional Measures. *Software, 3*, 442–472. https://doi.org/10.3390/software3040022

**[7]** Raja SP. (2026). *AI-Driven Development Lifecycle (AI-DLC) Method Definition*. Amazon Web Services. Documento de referencia metodológica consultado en el corpus del proyecto.