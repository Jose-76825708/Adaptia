# Informe Técnico: Especificación de Reglas de Negocio - Proyecto ADAPTIA

## 1. Resumen Ejecutivo y Marco de Referencia de ADAPTIA

El proyecto ADAPTIA representa una solución de arquitectura integral diseñada para cerrar la brecha técnica en el cuidado botánico doméstico mediante la convergencia de sistemas de recomendación inteligente y telemetría IoT de precisión. La visión estratégica del proyecto no solo busca la automatización, sino la democratización del conocimiento botánico a través de una infraestructura robusta que transforma datos biológicos en acciones preventivas. Al integrar modelos de aprendizaje profundo con hardware de grado industrial, ADAPTIA mitiga el fracaso del "Plant Parent" mediante una gestión de datos que sustituye la suposición por la certidumbre técnica.

La dualidad funcional del sistema (Recomendador vs. Telemetría) es fundamental para la viabilidad biológica del cultivo urbano. Esta sinergia justifica la inversión en hardware basándose en el análisis de la ansiedad del usuario: dado que las deficiencias lumínicas generan un 60% de estrés reportado y la incertidumbre del riego un 56%, la monitorización en tiempo real actúa como un estabilizador psicológico y operativo.

**Objetivos Centrales del Proyecto:**

- Reducción de la mortalidad vegetal: Implementando filtros de seguridad y monitoreo reactivo basado en umbrales biológicos.
- Mitigación de la carga cognitiva: Optimizando la toma de decisiones mediante un motor de inferencia con precisión de validación del 93.64%.
- Integridad de Datos en Tiempo Real: Utilizando procesadores ARM Cortex-M4 para garantizar lecturas estables y protocolos de recuperación ante fallos.
- Escalabilidad del Inventario: Unificando el catálogo botánico bajo estándares de identificación electrónica de 12 bits (EPC).

La efectividad de este ecosistema depende de la adherencia estricta a un catálogo robusto, cuyas reglas de filtrado preventivo y lógico se detallan en las secciones técnicas subsiguientes.

## 2. Módulo Recomendador: Reglas de Negocio del Catálogo y Filtrado Botánico

Este módulo opera como el motor de toma de decisiones preventivo de ADAPTIA. Su función estratégica es garantizar que la adquisición de especies no solo cumpla con criterios estéticos, sino con estándares de seguridad doméstica y compatibilidad ambiental, asegurando la satisfacción a largo plazo del usuario.

**RN-01: Descarte Absoluto por Toxicidad** El sistema debe ejecutar un filtrado eliminatorio de seguridad que invalide cualquier especie clasificada como tóxica en hogares con presencia de niños o mascotas.

- Impacto: Minimización de riesgos de responsabilidad civil y salud doméstica, estableciendo un entorno de cuidado seguro.

Sustento Científico: Basado en el marco de gestión de riesgos botánicos del Artículo 5, este filtro es la prioridad cero para la seguridad del usuario.

**RN-02: Descarte por Incompatibilidad Lumínica** El motor de inferencia invalidará opciones botánicas cuyos requerimientos de radiación fotosintética excedan la disponibilidad solar del microclima del usuario.

- Impacto: Reducción directa de la principal causa de mortalidad vegetal y estrés del usuario.

Sustento Científico: Los Artículos 5 y 6 demuestran que la luz inadecuada es responsable del 60% de la ansiedad del usuario, validando la necesidad de este filtro técnico.

**RN-03: Ranking Multicriterio por Score Ponderado** La jerarquización de recomendaciones se basa en un análisis de decisión conjunta (Conjoint Analysis) con pesos específicos para optimizar la satisfacción de compra.

- Precio: 40.55% (Priorizando el umbral de < Rp. 100,000 como el más valorado).
- Facilidad de Cuidado: 24.99% (Especies de bajo mantenimiento).
- Color: 20.43% (Preferencia por color verde).
- Tamaño: 9.62% (Preferencia por tamaño mediano).

- Impacto: Alineación del catálogo con las realidades económicas y preferencias estéticas del mercado actual.

Sustento Científico: Jerarquía de atributos validada por Fauzia et al. (2023) en el Artículo 2, identificando el precio competitivo como el factor dominante.

**RN-04: Suficiencia de Entrada Ambiental Básica** Para operar el modelo de recomendación regional, el sistema requiere 4 variables: Temperatura, Humedad, pH y Precipitaciones. El sistema descarta explícitamente el uso de NPK (Nitrógeno, Fósforo, Potasio).

- Impacto: Mantenimiento de una precisión del 93.64% bajo condiciones de escasez de sensores específicos en entornos urbanos.

Sustento Científico: Aradea et al. (2023) en el Artículo 1 demuestran que el descarte de NPK es una respuesta estratégica a las "limitaciones de datos en la evaluación ambiental", manteniendo la robustez del modelo CNN 1D + Adagrad a través de 1000 épocas de entrenamiento.

**RN-05: Emparejamiento por Nivel de Experiencia** El sistema asignará especies con un factor de utilidad de +0.419 (Cuidado Fácil) a usuarios identificados como "Millennial Plant Parents" o perfiles principiantes.

- Impacto: Reducción de la carga cognitiva y mejora de la autopercepción de competencia del usuario.

Sustento Científico: El Artículo 6 identifica la necesidad de simplicidad del usuario moderno, mientras que el Artículo 2 cuantifica la alta valoración de la facilidad de mantenimiento.

## 3. Módulo de Telemetría e IoT: Reglas de Monitoreo en Tiempo Real (ESP32)

Este módulo transforma el cuidado vegetal en una gestión basada en datos mediante el uso de hardware dedicado, centrado en el microcontrolador ESP32 y el controlador de núcleo ARM Cortex-M4 (MK66FX1M0VMD18).

**RN-06: Evaluación de Umbral Mínimo de Riego** El sistema activará la alerta "Riega Hoy" cuando el sensor capacitivo (conectado al GPIO D34) registre valores inferiores al punto de marchitamiento permanente de la especie.

- Impacto: Reducción del 56% en la ansiedad por riego identificada en estudios de comportamiento.

Sustento Científico: Integración del hardware de detección capacitiva del Artículo 7 con el análisis de estrés hídrico del Artículo 6.

**RN-07: Estado de Confort y Rango Óptimo** Se emitirá un estado de "Todo Bien" solo si la telemetría se mantiene estable dentro de los rangos biológicos. Esta estabilidad es procesada por el núcleo ARM Cortex-M4 para filtrar ruidos de lectura.

- Impacto: Proporciona un estado de tranquilidad operativa al usuario final fundamentado en estabilidad electrónica.

Sustento Científico: Los Artículos 7 y 8 respaldan la robustez del monitoreo continuo mediante arquitecturas de bajo ruido.

**RN-08: Alerta por Estrés Térmico y Microclima** El sistema notificará desviaciones peligrosas de temperatura mediante el uso de sensores DHT22 (GPIO D4) y DHT11.

- Impacto: Prevención de daños tisulares irreversibles por eventos climáticos extremos.

Sustento Científico: Rangos biológicos de operación definidos en los Artículos 7 y 8 para entornos de agricultura de precisión.

**RN-09: Alertas Informativas por Mediciones Fuera de Rango** Adaptia compara las lecturas recibidas con los rangos de referencia configurados para cada especie. Una humedad del suelo inferior al mínimo genera una alerta de tipo "riego"; las demás desviaciones generan una alerta de tipo "cuidado_planta". La alerta identifica la variable, el valor medido, el rango esperado y si la lectura está por debajo o por encima. El sistema no controla relés, bombas, electroválvulas ni riego automático.

- Impacto: Informa al cliente qué condición de cuidado debe revisar; no ejecuta acciones físicas sobre la planta.

Las alertas se deduplican por unidad y variable durante un episodio: las lecturas que mantengan la desviación no crean alertas repetidas; una lectura dentro del rango resuelve el episodio. Si vuelve a salir del rango, se genera una nueva alerta.

## 4. Validación y Calidad de Software: Reglas de API e Integridad de Datos

La robustez del software ADAPTIA reside en su capacidad para manejar la naturaleza no estructurada de los entornos agrícolas y las posibles interferencias de señal.

**RN-10: Sanitización del Payload JSON y Fail-Safe Local** Toda entrada de datos IoT debe ser validada. Ante datos corruptos o fuera de rango (HTTP 422), el sistema debe ejecutar obligatoriamente un evento de registro local (local logging) en la tarjeta microSD del dispositivo.

- Impacto: Prevención de pérdida de datos históricos durante interrupciones de conectividad o interferencias de señal de 7 dB.

Sustento Científico: El Artículo 4 detalla la importancia del registro en microSD para mitigar los desafíos de transmisión en entornos no estructurados.

**RN-11: Manejo de Desconexión y Pérdida de Señal** El sistema declarará una instancia como "Offline" si no se recibe un "latido" (heartbeat) del dispositivo en 24 horas, activando protocolos de recuperación de paquetes.

- Impacto: Mantenimiento de la integridad de la línea de tiempo de salud vegetal.

Sustento Científico: Protocolos de gestión de instancias y redes de sensores inalámbricos analizados en los Artículos 3 y 4.

**RN-12: Integridad Relacional Unificada del Catálogo** Cada planta será identificada mediante un código único de 12 bits bajo el estándar EPC (Electronic Product Code), unificando la entrada del catálogo con la instancia física de cultivo.

- Impacto: Trazabilidad absoluta del ciclo de vida, compatible con sistemas de gestión NMIS.

Sustento Científico: El Artículo 4 define el formato EPC de 12 bits como el identificador único para la gestión de inventario automatizado mediante RFID.

## 5. Cuadro Resumen de Trazabilidad Científica

| ID Regla | Descripción Breve | Sustento Científico (Artículos) | Estado |
|---|---|---|---|
| RN-01 | Filtro de toxicidad mandatorio | Artículo 5 | Completo |
| RN-02 | Validación lumínica preventiva | Artículos 5, 6 | Completo |
| RN-03 | Ranking multicriterio (Precio <100k) | Fauzia et al. (2023) / Artículo 2 | Completo |
| RN-04 | CNN 1D (Exclusión NPK por escasez) | Aradea et al. (2023) / Artículo 1 | Completo |
| RN-05 | Filtro por experiencia (Utilidad +0.419) | Artículos 2, 6 | Completo |
| RN-06 | Telemetría de riego (GPIO D34) | Artículos 6, 7 | Pendiente (Fase 4) |
| RN-07 | Procesamiento Cortex-M4 (Confort) | Artículos 4, 7, 8 | Pendiente (Fase 4) |
| RN-08 | Alerta microclima (DHT22/GPIO D4) | Artículos 7, 8 | Pendiente (Fase 4) |
| RN-09 | Alertas informativas por mediciones fuera de rango | Rangos IoT por especie | Pendiente (Fase 4) |
| RN-10 | Sanitización y Log en microSD | Artículos 4, 7 | Pendiente (Fase 4) |
| RN-11 | Protocolo Heartbeat (Latido) | Artículos 3, 4 | Pendiente (Fase 4) |
| RN-12 | Identificación EPC de 12 bits (NMIS) | Artículos 3, 4 | Completo |

**Conclusión** La arquitectura de reglas de negocio de ADAPTIA garantiza un sistema donde cada decisión técnica, desde el descarte de variables NPK por escasez de datos hasta la implementación de registros en microSD, está fundamentada en evidencia científica. Esta rigurosidad asegura la viabilidad técnica y comercial, posicionando a ADAPTIA como el estándar líder en sistemas de agricultura de precisión doméstica.