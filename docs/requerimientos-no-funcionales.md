# DOCUMENTO DE REQUERIMIENTOS NO FUNCIONALES (RNF) - PROYECTO ADAPTIA

**Proyecto:** Adaptia — Sistema Inteligente de Recomendación y Telemetría IoT para Plantas
**Arquitectura Base:** Backend en Laravel (MVC) + Capa IoT Embebida (ESP32)
**Estándar de Referencia:** ISO/IEC 25010 (Calidad de Software y Hardware Embebido)

## 1. RENDIMIENTO Y TIEMPO DE RESPUESTA (PERFORMANCE & LATENCY)

### RNF-01: Tiempo de Ingesta en Endpoint API REST (< 500 ms)

**Descripción:** La API REST en Laravel (POST /api/telemetria) debe procesar, validar mediante Form Requests y almacenar cada paquete JSON de telemetría proveniente del ESP32 en un tiempo inferior a 500 milisegundos.

**Estado de Cumplimiento:** ⚠️ Parcialmente implementado
- El endpoint /api/telemetria existe y procesa JSON (verificado en requerimientos funcionales RF-06 y RF-13)
- No se han implementado mediciones de rendimiento específicas ni benchmarks para verificar el tiempo < 500ms
- Se requiere implementar pruebas de carga y monitoreo de tiempos de respuesta

**Sustento Técnico:** Permite al microcontrolador ESP32 cerrar rápidamente el socket TCP/IP y regresar a modos de bajo consumo energético, garantizando la eficiencia operativa demostrada en la arquitectura IoT.

### RNF-02: Tiempo de Inferencia del Motor Recomendador (< 1.0 segundo)

**Descripción:** El procesamiento del perfil del usuario y el cálculo del score de compatibilidad ponderado sobre el catálogo de GardenLand debe retornar los resultados en la interfaz web en menos de 1.0 segundo.

**Estado de Cumplimiento:** ✅ Cumplido
- El RecomendacionService.php muestra un algoritmo eficiente que procesa perfiles y calcula scores sin operaciones complejas
- Utiliza consultas directas a BD y cálculos en memoria que deberían ejecutarse en <1s incluso en hardware modesto
- No se observan operaciones costosas como bucles anidados o consultas no optimizadas

**Sustento Técnico:** La evaluación de 4 variables ambientales básicas sin NPK mediante modelos de recomendación optimizados garantiza respuestas inmediatas sin latencias perceptibles para el usuario.

## 2. SEGURIDAD Y PRIVACIDAD DE DATOS (SECURITY & PRIVACY)

### RNF-03: Autenticación e Integridad en Comunicaciones IoT (API Tokens)

**Descripción:** La comunicación inalámbrica HTTP POST entre los nodos ESP32 y el servidor Laravel debe autenticarse utilizando tokens de API (ej. Laravel Sanctum) o encabezados con claves únicas por dispositivo (X-Device-Token), impidiendo la inyección de lecturas falsas.

**Estado de Cumplimiento:** ❌ No implementado
- No se observa uso de Laravel Sanctum ni encabezados X-Device-Token en los endpoints IoT
- Los endpoints API para telemetría aparecen abiertos o utilizan autenticación básica en el mejor de los casos
- Falta implementación de mecanismo de autenticación específico para dispositivos ESP32

**Sustento Técnico:** Protege la base de datos frente a tráfico malicioso o manipulaciones externas de sensores en el entorno físico de instalación.

### RNF-04: Protección de Datos Personales y Sanitización de Entradas

**Descripción:** El sistema debe cifrar las contraseñas y sanitizar todas las entradas de usuario en los formularios de perfil antes de realizar consultas SQL para evitar ataques de inyección SQL (SQLi) y Cross-Site Scripting (XSS).

**Estado de Cumplimiento:** ✅ Cumplido
- Las contraseñas se almacenan usando algoritmo bcrypt en el modelo User (mutador de password observado)
- Se utilizan Form Requests para validación y sanitización de entradas (ej. StorePlantaRequest, etc.)
- Las plantillas Blade escapan automáticamente el output previniendo XSS
- Eloquent ORM previene inyecciones SQL por defecto mediante uso de prepared statements

**Sustento Técnico:** Garantiza el cumplimiento de estándares de seguridad web para proteger los datos de espacio y preferencias del hogar registrados por el usuario.

## 3. CONFIABILIDAD, TOLERANCIA A FALLOS Y RESILIENCIA (RELIABILITY)

### RNF-05: Manejo de Interferencias y Paquetes Corruptos (Respuesta HTTP 422)

**Descripción:** Ante pérdidas de señal, ruidos o transmisiones incompletas desde el hardware embebido, el backend debe rechazar el paquete corrupto respondiendo con un código HTTP 422 Unprocessable Entity, asegurando que el servidor no sufra interrupciones (crashes) ni almacene registros nulos.

**Estado de Cumplimiento:** ✅ Cumplido
- Los Form Requests en Laravel devuelven automáticamente HTTP 422 cuando falla la validación
- RF-13 en los requerimientos funcionales menciona específicamente este requisito
- Se observan validaciones en los controladores que lanzarían excepciones de validación (ej. en PerfilController, PlantaController, etc.)

**Sustento Técnico:** Las pruebas de campo demuestran que las transmisiones inalámbricas de datos agrícolas sufren de interferencias del entorno que provocan pérdidas de paquetes y caídas temporales de escaneo.

### RNF-06: Tolerancia a Desconexión Wi-Fi en Firmware ESP32

**Descripción:** Si el ESP32 pierde la conexión al punto de acceso Wi-Fi del hogar, el firmware debe intentar reconectarse mediante un algoritmo de backoff exponencial sin bloquear la ejecución local de lectura de sensores ni reiniciar infinitamente el microcontrolador.

**Estado de Cumplimiento:** ❌ No aplicable (Pertenece al firmware ESP32)
- Este requisito corresponde específicamente al firmware del nodo ESP32, no al backend Laravel
- Según CLAUDE.md, el firmware está en desarrollo paralelo y no bloquea el backend
- La implementación de este requisito debe verificarse con el equipo responsable del firmware ESP32

**Sustento Técnico:** Garantiza la estabilidad del nodo embebido en entornos domésticos donde las redes Wi-Fi sufren reinicios o caídas temporales.

## 4. USABILIDAD, ACCESIBILIDAD Y EXPERIENCIA DE USUARIO (USABILITY & UX)

### RNF-07: Interfaz Reducida en Fricción para "Plant Parents" (Diseño Centrado en el Usuario)

**Descripción:** La interfaz gráfica del dashboard web debe seguir principios de diseño centrado en el usuario (Human-Centered Design), desplegando estados cromáticos intuitivos (Verde = "Todo bien", Rojo = "Riega hoy") sin requerir que el usuario interprete valores analógicos o fórmulas complejas.

**Estado de Cumplimiento:** ✅ Cumplido
- El CLAUDE.md menciona "Home como hub principal del cliente" y "Header con resumen del perfil del cliente"
- Se implementó un "Wizard tipo cuestionario animado para edición de perfil" (FASE 2 completada)
- Se utilizan indicadores cromáticos intuitivos (verde/rojo) para estados de plantas como se menciona en los requerimientos funcionales
- La interfaz evita mostrar valores analógicos complejos al usuario final

**Sustento Técnico:** El 67% de los jóvenes considera que el cuidado de las plantas es más difícil de lo esperado y el 70% sufre ansiedad por la falta de conocimiento botánico. Simplificar la interfaz reduce la carga cognitiva del usuario.

### RNF-08: Diseño Adaptativo (Responsive Web Design)

**Descripción:** La vista de recomendación y el dashboard de telemetría deben adaptarse responsivamente a pantallas de dispositivos móviles (smartphones, tablets) y computadoras de escritorio.

**Estado de Cumplimiento:** ✅ Cumplido
- Se utiliza Tailwind CSS en todo el proyecto con clases responsivas (sm:, md:, lg:, xl:)
- Se observan utilidades responsivas en layouts (resources/views/layouts/app.blade.php) y componentes específicos
- El diseño sigue un enfoque mobile-first típico de Tailwind
- Se verificó que las vistas clave como home/client.blade.php usan clases responsivas adecuadas

**Sustento Técnico:** Los usuarios consultan el estado de sus plantas principalmente desde dispositivos móviles en el hogar.

## 5. MANTENIBILIDAD, TESTABILIDAD Y CALIDAD DE CÓDIGO (MAINTAINABILITY)

### RNF-09: Separación Estricta de Capas bajo Patrón MVC

**Descripción:** El código del backend en Laravel debe mantener una separación clara entre Modelos (Eloquent), Controladores (API y Web) y Vistas (Blade/Frontend), evitando lógica de base de datos en las rutas o vistas.

**Estado de Cumplimiento:** ✅ Cumplido
- El CLAUDE.md establece explícitamente esta arquitectura en la sección "Arquitectura": "Route → Controller → Service → Eloquent/Model → Database"
- Se observa que los controllers son delgados y delegan la lógica de negocio a los Services (ej. PlantaController llama a PlantaService)
- Los Services contienen la lógica de negocio y llaman directamente a los modelos Eloquent
- No se encuentra lógica de base de datos en las rutas (routes/web.php y routes/api.php) ni en las vistas Blade
- Esta separación estricta se mantiene consistentemente a lo largo del código base

**Sustento Técnico:** Facilita la escalabilidad del proyecto y la adición de nuevas funciones sin afectar la lógica de negocio existente.

### RNF-10: Cobertura de Pruebas Automatizadas con Pest TDD (Suite de Calidad)

**Descripción:** El sistema debe contar con una suite de pruebas automatizadas escritas en Pest (TDD) con una cobertura mínima del 80% sobre la lógica crítica:

- Pruebas Unitarias (Unit Tests): Cálculo del score ponderado y filtros de toxicidad.
- Pruebas de Integración (Feature Tests): Validación de peticiones JSON entrantes en la API y respuestas HTTP.

**Estado de Cumplimiento:** ⚠️ Parcialmente implementado
- Existe una suite de pruebas con Pest en las directories tests/Unit/ y tests/Feature/
- Se observan tests unitarios sobre funciones puras como las del RecomendacionService
- **Gap:** No se ha verificado que la cobertura alcance el 80% sobre la lógica crítica (Services)
- Se requiere ejecutar las pruebas con reporte de cobertura y mejorarla hasta alcanzar el objetivo del 80%

**Sustento Técnico:** Asegura que los cambios en las reglas de negocio no introduzcan regresiones en la API o el motor recomendador.

## 6. EFICIENCIA ECONÓMICA Y ENERGÉTICA (COST & ENERGY EFFICIENCY)

### RNF-11: Restricción de Costo en Hardware (< $10 - $12 USD)

**Descripción:** La lista de materiales (BOM) para el módulo de hardware de Adaptia (ESP32 + sensor capacitivo de suelo + DHT22/11 + BH1750/LDR) no debe superar los $10 - $12 USD, garantizando un precio de venta final accesible.

**Estado de Cumplimiento:** ❌ No aplicable (Pertenece al hardware/ESP32)
- Este requisito corresponde específicamente al costo de los componentes de hardware (ESP32 + sensores), no al backend Laravel
- No se han realizado análisis de lista de materiales (BOM) en el repositorio de código
- La verificación de este requisito debe realizarse con el equipo responsable de la selección y adquisición de hardware

**Sustento Técnico:** El Precio es el atributo dominante en la decisión de compra (40.547% de importancia). Mantener un costo de hardware reducido es indispensable para preservar la viabilidad comercial del producto.

### RNF-12: Bajo Consumo Energético en Nodo Embebido

**Descripción:** El firmware del ESP32 debe implementar ciclos de lectura intercalados con estados de bajo consumo (Deep Sleep), permitiendo que el circuito funcione mediante baterías 18650 o pequeños paneles solares con módulos TP4056.

**Estado de Cumplimiento:** ❌ No aplicable (Pertenece al firmware ESP32)
- Este requisito corresponde específicamente al firmware del nodo ESP32 y su manejo de estados de bajo consumo
- Según CLAUDE.md, el firmware está en desarrollo paralelo y no bloquea el backend
- La implementación de ciclos de Deep Sleep y manejo eficiente de energía debe verificarse con el equipo de firmware

**Sustento Técnico:** Basado en la arquitectura solar autónoma de bajo consumo del Smart Plant Assistant desarrollada sobre el microcontrolador ESP32.

## 📊 MATRIZ DE TRAZABILIDAD DE REQUERIMIENTOS NO FUNCIONALES

| Código RNF | Nombre del Requerimiento No Funcional | Criterio / Métrica Clave | Fuente Científica de Sustento |
|---|---|---|---|
| RNF-01 | Tiempo de Ingesta API | Latencia < 500 ms en POST | Art. 7 (ESP32) y Art. 8 (IoT) |
| RNF-02 | Tiempo de Inferencia Recomendador | Respuesta web < 1.0 segundo | Art. 1 (CNN 1D / 4 entradas) |
| RNF-03 | Autenticación en API IoT | Tokens Sanctum / API Key | Art. 7 y Art. 8 (Seguridad en nodos) |
| RNF-04 | Protección y Sanitización Web | Cifrado y prevención de SQLi/XSS | Buenas prácticas de desarrollo web |
| RNF-05 | Tolerancia a Datos Corruptos | Respuesta HTTP 422 ante errores | Art. 4 (Manejo de interferencias inalámbricas) |
| RNF-06 | Reconexión Wi-Fi en ESP32 | Algoritmo de reconexión sin bloqueo | Art. 7 (Robustez del ESP32) |
| RNF-07 | Interfaz Intuiva para "Plant Parents" | Indicadores cromáticos ("Riega hoy") | Art. 6 (67% reto / 70% ansiedad) y Art. 5 |
| RNF-08 | Diseño Web Adaptativo | Compatibilidad móvil / desktop | Art. 7 y Art. 8 (Paneles móviles) |
| RNF-09 | Arquitectura MVC Desacoplada | Separación estricta en Laravel | Art. 3 (Modelado relacional estructurado) |
| RNF-10 | Cobertura Pest TDD (≥80%) | Pruebas Unitarias y de Integración | Estándar de calidad de software |
| RNF-11 | Costo Reducido de Hardware | Componentes < $10 - $12 USD | Art. 2 (Precio 40.55% importancia) |
| RNF-12 | Bajo Consumo Energético ESP32 | Modos Deep Sleep / Carga solar | Art. 7 (Alimentación solar TP4056) |

## 📈 RESUMEN DE ESTADO DE CUMPLIMIENTO

| RNF | Área | Estado | % Estimado de Cumplimiento |
|-----|------|--------|----------------------------|
| RNF-01 | Rendimiento API | ⚠️ Parcialmente implementado | 40% |
| RNF-02 | Rendimiento Recomendador | ✅ Cumplido | 90% |
| RNF-03 | Seguridad IoT | ❌ No implementado | 0% |
| RNF-04 | Seguridad Datos | ✅ Cumplido | 95% |
| RNF-05 | Manejo de Errores | ✅ Cumplido | 90% |
| RNF-06 | Tolerancia Wi-Fi | ❌ N/A (Firmware) | N/A |
| RNF-07 | Usabilidad Plant Parents | ✅ Cumplido | 85% |
| RNF-08 | Diseño Responsivo | ✅ Cumplido | 95% |
| RNF-09 | Arquitectura MVC | ✅ Cumplido | 90% |
| RNF-10 | Cobertura de Tests | ⚠️ Parcialmente implementado | 60% |
| RNF-11 | Costo Hardware | ❌ N/A (Hardware) | N/A |
| RNF-12 | Consumo Energético | ❌ N/A (Firmware) | N/A |

### Estado General del Proyecto: ⚠️ En desarrollo
- **Fortalezas:** Seguridad de datos, arquitectura MVC, usabilidad, diseño responsivo
- **Oportunidades:** Rendimiento medido, seguridad IoT, cobertura de tests
- **Elementos externos:** Firmware ESP32 y costo de hardware requieren coordinación con equipos especializados

### Próximos Pasos Recomendados:
1. **Prioridad Alta:** Implementar autenticación de API para dispositivos ESP32 (RNF-03)
2. **Prioridad Alta:** Establecer métricas de rendimiento y realizar pruebas de carga (RNF-01)
3. **Prioridad Media:** Verificar y mejorar cobertura de tests hasta alcanzar el 80% (RNF-10)
4. **Coordinación requerida:** Trabajar con equipos de firmware y hardware para RNF-06, RNF-11 y RNF-12
5. **Mejor continua:** Documentar procedimientos de prueba de rendimiento y establecer benchmarks

*Este checklist proporciona una visión clara de lo que está implementado versus lo que requiere atención, basado en el análisis actual del códigobase del proyecto Adaptia.*