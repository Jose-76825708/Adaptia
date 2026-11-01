# Marco Unificado de Ingeniería de Prompts para la Ingeniería de Software: Integración de Autoplanificación y Patrones de Diseño de Prompts

## 1. Fundamentación Teórica y Estado del Arte

La automatización de tareas en el ciclo de vida del desarrollo de software mediante modelos de lenguaje de gran tamaño (LLM) representa una transformación paradigmática en la ingeniería de software moderna. Sin embargo, la traducción directa de especificaciones en lenguaje natural a código fuente ejecutable enfrenta barreras estructurales cuando la intención humana es compleja o ambigua. Como señaló Dijkstra, la esencia de la programación reside en el arte de organizar la complejidad; por lo tanto, forzar a un LLM a resolver requisitos multiobjetivo en un único paso sin abstracción previa suele derivar en código monolítico, acoplado o erróneo.

Para mitigar estas limitaciones, la investigación reciente ha convergido en dos enfoques complementarios:

1. **La descomposición sintáctica e inferencial previa a la codificación**, representada por el trabajo de Xue Jiang, Yihong Dong, Lecheng Wang, Zheng Fang, Qiwei Shang, Ge Li, Zhi Jin y Wenpin Jiao (*Self-planning Code Generation with Large Language Models*, 2023).
2. **La codificación de patrones de diseño de prompts aplicables a todo el ciclo de vida del software**, desarrollada por Jules White, Sam Hays, Quchen Fu, Jesse Spencer-Smith y Douglas C. Schmidt (*ChatGPT Prompt Patterns for Improving Code Quality, Refactoring, Requirements Elicitation, and Software Design*, 2023).

Este documento presenta un marco metodológico unificado que sintetiza la arquitectura de autoplanificación en dos fases de Jiang et al. con el catálogo formal de 13 patrones de prompts para ingeniería de software de White et al., estableciendo una guía rigurosa y sustentada empíricamente para optimizar la interacción con LLMs.

---

## 2. Síntesis Analítica de las Investigaciones

### 2.1 La Propuesta de Jiang et al.: Generación de Código con Autoplanificación (*Self-Planning*)

Jiang et al. (2023) abordan la incapacidad de los LLMs para procesar intenciones compuestas mediante la introducción del concepto de **autoplanificación en la inferencia**. En lugar de requerir entrenamiento adicional o corpus anotados a gran escala, la autoplanificación aprovecha la capacidad de aprendizaje en contexto (*few-shot prompting*) dividiendo el proceso en dos fases secuenciales:

- **Fase de Planificación (*Planning Phase*):** El modelo abstrae la intención del problema y construye una lista numerada de pasos ejecutivos de alto nivel formulados en oraciones imperativas.
- **Fase de Implementación (*Implementation Phase*):** Se concatena el plan obtenido a la intención original, guiando la generación del código paso a paso mediante la predicción secuencial de tokens.

#### Evidencia Empírica y Rendimiento

Los experimentos conducidos por Jiang et al. sobre los benchmarks de evaluación de código **HumanEval** y **MBPP-sanitized**, así como sus extensiones multilingües (**HumanEval-X**) y de casos extremos (**HumanEval-ET**), revelan resultados significativos:

- **Mejora en Pass@1:** El enfoque de autoplanificación alcanza una mejora relativa de hasta un **25.4% en la métrica Pass@1** frente a la generación directa (*zero-shot*) y de un **11.9%** frente a la adaptación de cadena de pensamiento para código (*Code Chain-of-Thought / Code CoT*).
- **Propiedad Emergente y Escala:** La capacidad autónoma de generar planes efectivos es una facultad emergente que se manifiesta plenamente en modelos de escala igual o superior a 175 mil millones de parámetros (como `code-davinci-002` y `text-davinci-003`). Sin embargo, aplicar un plan previamente generado por un modelo superior beneficia sustancialmente a modelos de menor escala (como `code-cushman-001` de 13B).
- **Evaluación Humana de Calidad:** La evaluación cualitativa con desarrolladores profesionales demostró que la autoplanificación supera a la generación directa y a *Code CoT* en tres criterios clave: **corrección funcional**, **legibilidad** (destacando la claridad estructural del código generado) y **robustez** ante casos límite.

### 2.2 La Propuesta de White et al.: Catálogo de Patrones de Prompts para Ingeniería de Software

White et al. (2023) introducen el concepto de que los prompts son una **forma de programación en lenguaje natural** para parametrizar el comportamiento de modelos conversacionales como ChatGPT. Inspirados en los patrones de diseño orientados a objetos de Gamma et al. (GoF), codifican la experiencia de diseño en un **catálogo de 13 patrones organizados en 4 categorías temáticas**:

| Criterio / Categoría | Patrones Incluidos | Objetivo en el Ciclo de Vida |
|---|---|---|
| Elicitación de Requisitos | *Requirements Simulator*, *Specification Disambiguation*, *Change Request Simulation* | Explorar interactivamente requisitos incompletos, detectar vacíos lógicos y evaluar el impacto de cambios en el sistema antes de escribir código. |
| Diseño de Sistemas y Simulación | *API Generator*, *API Simulator*, *Few-shot Code Example Generation*, *Domain-Specific Language (DSL) Creation*, *Architectural Possibilities* | Formalizar especificaciones de interfaces (ej. OpenAPI), simular comportamientos de red/datos, construir DSLs sintácticamente eficientes y explorar arquitecturas alternativas. |
| Calidad del Código | *Code Clustering*, *Intermediate Abstraction*, *Principled Code*, *Hidden Assumptions* | Forzar separación de efectos secundarios, evitar acoplamiento a librerías de terceros, aplicar principios SOLID y hacer explícitas las premisas implícitas del código. |
| Refactorización | *Pseudo-code Refactoring*, *Data-guided Refactoring* | Reestructurar código existente mediante guías de pseudo-código o adaptar la lógica ante cambios en los esquemas de datos de entrada/salida. |

La estructura de cada patrón incluye delimitadores de alcance conversacional (*Conversational Scoping* como `"from now on"` o `"act as X"`), reglas condicionales y especificaciones formales de salida, permitiendo personalizar interacciones complejas.

---

## 3. Análisis Comparativo: Tensiones y Puntos de Complementariedad

Aunque ambas investigaciones abordan la optimización de LLMs para tareas de desarrollo, parten de premisas operativas que presentan tanto tensiones sintácticas como profundas complementariedades estratégicas.

### 3.1 Tensiones Sintácticas y Tácticas

**Granularidad de la Abstracción Intermedia:**

- **Jiang et al.** argumentan empíricamente que el plan intermedio debe ser **extremadamente conciso y de alto nivel**. En sus ablaciones con *Code CoT* (que genera comentarios detallados paso a paso) y *Plan2CoT*, observaron que los pasos hiper-detallados provocan pérdida de diversidad en la generación (reduciendo la métrica Pass@k para $k \ge 5$) e incrementan el consumo ineficiente de tokens.
- **White et al.**, en patrones como *Pseudo-code Refactoring* o *API Simulator*, requieren descripciones minuciosas o esquemas de datos completos (como OpenAPI en YAML) para garantizar que el LLM no desvíe el comportamiento del sistema.

**Paradigma de Inferencia (Pipeline Automático vs. Diálogo Conversacional):**

- ***Self-planning*** está formulado como un algoritmo determinista de dos pasos en pipelines de inferencia (*few-shot prompting* sin intervención humana).
- ***Catalog of Patterns*** asume un entorno conversacional interactivo (*human-in-the-loop*) donde el desarrollador guía iterativamente el alcance mediante instrucciones continuas.

### 3.2 Complementariedad Metodológica

A pesar de las diferencias tácticas, ambos estudios convergen en tres axiomas fundamentales:

1. **Rechazo a la Generación Directa (*Zero-shot*):** La generación directa a partir de intenciones crudas genera código frágil, propenso a omitir restricciones de borde y difícil de mantener.
2. **Necesidad de Capas Intermedias de Abstracción:** Ambos enfoques sostienen que forzar una etapa de razonamiento o estructuración previa —ya sea un plan numérico de sub-tareas o un patrón de simulación de requisitos— incrementa drásticamente la precisión del resultado.
3. **Continuidad en el Ciclo de Vida:** El trabajo de White et al. cubre el espectro macro (requisitos, arquitectura y refactorización), mientras que Jiang et al. ofrecen la micro-arquitectura óptima para ejecutar la generación de las funciones individuales resultantes de dicho diseño.

---

## 4. Marco Metodológico Unificado: El Modelo de 3 Etapas

Integramos las contribuciones de ambos estudios en un **flujo de trabajo en 3 etapas** diseñado para automatizar y elevar la calidad de los prompts en proyectos de ingeniería de software.

### Etapa I: Elicitación de Requisitos y Diseño Arquitectónico

Antes de solicitar código, se utilizan los patrones de White et al. para congelar la especificación y explorar alternativas:

- **Disambiguación:** Aplicar *Specification Disambiguation* sobre el documento de requisitos inicial para que el LLM actúe como un revisor crítico e identifique ambigüedades.
- **Exploración de Arquitecturas:** Utilizar *Architectural Possibilities* para definir la distribución modular del sistema (ej. separación en capas de dominio, infraestructura y controladores).
- **Formalización de Interfaces:** Generar contratos claros utilizando *API Generator* (por ejemplo, OpenAPI).

### Etapa II: Planificación Ejecutiva e Implementación con Autoplanificación

Una vez delimitado el módulo o función específica a construir, se ejecuta el mecanismo de Jiang et al., enriquecido con las restricciones de calidad de White et al.:

- **Fase 1 (Planificación de Alto Nivel):** Se solicita al LLM que construya un plan numérico conciso compuesto por verbos en imperativo, evitando detalles de implementación de bajo nivel (siguiendo los 4 principios de elaboración de prompts de Jiang et al.).
- **Fase 2 (Implementación Principada):** Se envía la intención combinada con el plan generado, incorporando directivas de los patrones *Principled Code* (para forzar cumplimiento de principios SOLID), *Code Clustering* (para aislar funciones con efectos secundarios) e *Intermediate Abstraction* (para desacoplar librerías de terceros).

### Etapa III: Diagnóstico de Supuestos y Refactorización

Con el código generado en la Etapa II, se ejecutan patrones de cierre para elevar la mantenibilidad:

- **Diagnóstico de Supuestos Implícitos:** Se aplica el patrón *Hidden Assumptions* para forzar al LLM a listar las premisas bajo las cuales opera el código (ej. concurrencia, límites de memoria o acoplamiento a formatos).
- **Refactorización Estructurada:** Si se detectan rigideces, se aplica *Pseudo-code Refactoring* o *Data-guided Refactoring* para ajustar la implementación sin romper los contratos de interfaz establecidos en la Etapa I.

---

## 5. Plantilla Práctica: El *Master Prompt Template*

A continuación se presenta una plantilla unificada para ingenieros de software que integra la sintaxis de delimitación conversacional, las restricciones de diseño de White et al. y el proceso de autoplanificación de Jiang et al.

```text
### ROL Y ALCANCE CONVERSACIONAL (White et al. - Scoping)
Actúa como un Arquitecto de Software Principal y Desarrollador Senior.
De aquí en adelante, para cada módulo o función compleja que te solicite implementar,
deberás seguir estrictamente un proceso de resolución en DOS FASES sin omitir ninguna.


### RESTRICCIONES DE DISEÑO Y CALIDAD (White et al. - Principled Code & Code Clustering)
1. Principios SOLID: El código resultante debe adherirse a los principios SOLID.
2. Aislamiento de Efectos Secundarios: Separa la lógica pura de negocio de las operaciones
   con efectos secundarios (acceso a base de datos, E/S de archivos, llamadas de red).
3. Abstracciones Intermedias: Si utilizas librerías de terceros, introduce una interfaz
   o capa intermedia para no acoplar la lógica de negocio directamente a la librería.


### FASE 1: AUTOPLANIFICACIÓN (Jiang et al. - Planning Phase)
Antes de escribir cualquier línea de código, analiza la intención recibida y genera un PLAN CONCISO:
- Presenta el plan como una lista numerada de pasos de alto nivel.
- Comienza cada paso con un verbo en imperativo (ej. "1. Validar parámetros de entrada").
- Mantén las instrucciones abstractas y concisas; NO incluyas pseudo-código detallado ni fragmentos de código en esta fase.


### FASE 2: IMPLEMENTACIÓN GUIADA (Jiang et al. - Implementation Phase)
Una vez redactado el plan, genera el código fuente final asegurando que:
- Cada bloque o función principal corresponda directamente a los pasos del plan numerado.
- Incluyas el manejo explícito de casos límite y validaciones de robustez.


---
### REQUISITO DE ENTRADA (INTENCIÓN):
[Insertar aquí la descripción de la función o módulo requerido]
```

---

## 6. Conclusiones y Referencias Bibliográficas

La integración de la **autoplanificación de Jiang et al. (2023)** y el **catálogo de patrones de White et al. (2023)** demuestra que la ingeniería de prompts no debe tratarse como una serie de heurísticas ad-hoc, sino como una disciplina estructurada de diseño de software. Mientras que los patrones de prompts proporcionan el marco de gobernanza, restricciones y arquitectura para guiar al modelo a lo largo del ciclo de vida, la técnica de autoplanificación optimiza la ejecución sintáctica e inferencial de las tareas de codificación, logrando mejoras cuantificables en corrección (Pass@1), legibilidad y robustez.

### Referencias Académicas

- Jiang, X., Dong, Y., Wang, L., Fang, Z., Shang, Q., Li, G., Jin, Z., & Jiao, W. (2023). *Self-planning Code Generation with Large Language Models*. ACM Transactions on Software Engineering and Methodology (TOSEM), 30 pages (arXiv:2303.06689).
- White, J., Hays, S., Fu, Q., Spencer-Smith, J., & Schmidt, D. C. (2023). *ChatGPT Prompt Patterns for Improving Code Quality, Refactoring, Requirements Elicitation, and Software Design*. IEEE International Conference on Software Architecture / Vanderbilt University (arXiv:2303.07839).