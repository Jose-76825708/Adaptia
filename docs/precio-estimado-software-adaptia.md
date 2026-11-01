# Estimación del precio comercial de Adaptia

**Moneda:** soles peruanos (PEN)  
**Fecha de estimación:** 18 de octubre de 2026  
**Tipo de estimación:** referencia comercial preliminar, no cotización contractual

## Precio de referencia

Se recomienda un precio comercial de referencia de **S/ 28 000, antes del IGV**, para una licencia de uso no exclusiva de la versión funcional descrita en este documento, su instalación inicial en un entorno del cliente y una entrega básica del sistema. El precio se propone por el producto y el alcance que recibe el cliente, no como cobro por cada hora de programación. Por ello, una herramienta de IA puede reducir el costo interno o el tiempo de entrega sin reducir automáticamente el precio comercial, siempre que se mantengan el alcance, la calidad acordada y la utilidad del producto. Los estudios consultados orientan la estimación del esfuerzo y sus riesgos, pero no determinan el precio de mercado de Adaptia (Lavazza et al., 2024; Paradis et al., s. f.; Xu et al., s. f.; Alaswad et al., 2026).

El intervalo estimado es **S/ 22 400 a S/ 34 900, antes del IGV**. Es un rango calculado para esta estimación, no un intervalo publicado en los artículos. La variación depende principalmente de la complejidad de la instalación, los datos iniciales, los ajustes de aceptación y el nivel de validación requerido, factores coherentes con las dimensiones de complejidad, integración y supervisión destacadas en la literatura (Lavazza et al., 2024; Alaswad et al., 2026). La tasa por hora indicada abajo es un supuesto de cálculo, no una tarifa de mercado verificada.

**Supuestos propios de cálculo, no tarifas ni resultados tomados de los artículos.** Las 377 horas son una estimación de esfuerzo de entrega con asistencia de IA modelada; no son un registro horario del equipo.

| Concepto | Estimación base |
|---|---:|
| Esfuerzo de referencia con asistencia de IA modelada | 377 horas-persona |
| Tarifa analítica asumida | S/ 50 por hora-persona |
| Costo base calculado | S/ 18 850 |
| Reserva de riesgo y retrabajo (20 % del costo base) | S/ 3 770 |
| Subtotal con reserva | S/ 22 620 |
| Recargo comercial supuesto (24 % del subtotal) | S/ 5 429 |
| Precio calculado | S/ 28 049 |
| **Precio recomendado redondeado** | **S/ 28 000** |

El precio recomendado no incluye IGV. Si corresponde aplicar la tasa general del 18 %, el total referencial sería **S/ 33 040** (cálculo propio). La tasa aplicable y su tratamiento deben confirmarse con la autoridad tributaria o un profesional contable según la situación del proveedor y del cliente.

### Cómo se considera la productividad de la IA

El ensayo de Paradis et al. encontró una reducción estimada del tiempo de tarea de aproximadamente 21–26 % en modelos ajustados para un trabajo específico; el estudio se hizo con 96 ingenieros de Google y el efecto ajustado principal no fue estadísticamente concluyente (Paradis et al., s. f., secciones V–VII). No es válido asumir que Adaptia tendrá la misma mejora en todas sus actividades.

Para mostrar la posible incidencia sin extrapolar directamente ese resultado, el cálculo usa **un supuesto propio de planificación del 10 % de reducción neta sobre el esfuerzo total** en un escenario donde el equipo sí emplea IA en tareas aptas. Con ese supuesto, 377 horas de esfuerzo asistido equivalen aproximadamente a 419 horas de referencia sin esa eficiencia (cálculo propio: 377 / 0,90). La diferencia aproximada de 42 horas expresa capacidad potencial liberada, **no un ahorro medido en Adaptia ni una garantía**. A la tarifa analítica supuesta de S/ 50 por hora, el costo directo de referencia sería S/ 20 950 sin esa eficiencia frente a S/ 18 850 en el escenario asistido: una posible reducción interna de S/ 2 100 antes de reserva y recargo (cálculo propio). Tampoco presupone que la IA sustituya análisis, revisión, pruebas, integración o responsabilidad técnica; el trabajo de supervisión y retrabajo sigue formando parte del esfuerzo estimado (Xu et al., s. f.; Alaswad et al., 2026).

El cliente paga por el alcance funcional, el resultado utilizable y las condiciones de entrega descritas, no por una cantidad de horas de tecleo ni por la herramienta interna que se use para producirlo. Este es el criterio comercial adoptado en esta propuesta, no una conclusión sobre fijación de precios extraída de los artículos. La asistencia de IA puede traducirse en menor costo interno, entrega más rápida o margen para atender mejor las revisiones; **no reduce por sí sola la utilidad del sistema para el cliente ni cambia el precio comercial acordado**. Esta lógica aplica mientras el proveedor cumpla el alcance, los criterios de aceptación, la seguridad y el soporte que se hayan pactado. Si se cobrara por horas reales en vez de por producto o alcance, el ahorro de tiempo sí debería reflejarse en la factura.

## Alcance considerado

La estimación considera el producto web Laravel y las funciones implementadas hasta el alcance descrito para las fases 1 a 4. Esta delimitación se ha contrastado con el [modelo de datos del proyecto](./modelo-bd.md) y sus [rutas web](../routes/web.php) y [ruta de recepción IoT](../routes/api.php); la siguiente lista describe el alcance valorado, no datos ni resultados de los cuatro estudios:

- Autenticación y separación de permisos para cliente, vendedor y administrador.
- Administración de tipos y catálogo de plantas, existencias y movimientos de inventario, incluida la alerta de stock bajo.
- Perfil del cliente y recomendaciones de plantas según compatibilidad y restricciones de toxicidad.
- Registro de ventas con actualización de existencias y asociación de sensores a plantas vendidas.
- Administración de sensores, recepción autenticada de lecturas IoT, evaluación de umbrales y generación y resolución de alertas.
- Consulta del cliente de alertas e historial de mediciones con visualización de tendencias.
- Integración de las funciones anteriores, pruebas de aceptación básicas, documentación de entrega e instalación inicial.

Este es un precio de referencia para un despliegue inicial de un cliente (supuesto comercial propio). No representa una valoración de la propiedad intelectual exclusiva ni el costo ya incurrido por el equipo del proyecto. La licencia, la titularidad del código fuente, las garantías y las obligaciones de soporte deben quedar definidas en el acuerdo comercial.

## Exclusiones

No se consideran en este precio; estas exclusiones delimitan el cálculo comercial y no son conclusiones de los artículos:

- Firmware del ESP32, compra de sensores, instalación física ni conectividad IoT.
- Notificaciones push o por correo, que corresponden a la fase 5 pendiente.
- Pruebas end-to-end con Cypress, dockerización completa y otros trabajos de cierre técnico aún pendientes.
- Hosting, dominio, almacenamiento, servicios externos, operación continua y mantenimiento posterior a la entrega.
- Migración o carga masiva de datos, capacitación extensa, cambios de alcance o integraciones no descritas.
- Cesión exclusiva de derechos de propiedad intelectual.

Por lo tanto, el monto no debe interpretarse como el precio de una solución ya terminada con todas las fases del proyecto ni como una cotización para operar el sistema a escala productiva.

## Método de estimación

Se usó una estimación de esfuerzo por funcionalidades observables para el usuario, contrastada con las limitaciones de las medidas funcionales y los factores de complejidad, validación, integración, iteración y retrabajo discutidos en los artículos (Lavazza et al., 2024; Paradis et al., s. f.; Xu et al., s. f.; Alaswad et al., 2026). No se afirma haber realizado un conteo certificado de Function Points (FP), ni que alguno de los estudios proporcione una tarifa en soles.

La siguiente descomposición representa el esfuerzo hipotético para valorar una entrega equivalente al alcance considerado en un escenario de asistencia de IA; **no es un registro de horas realmente trabajadas ni una cifra calculada por los artículos**. Los intervalos son supuestos propios de planificación, informados cualitativamente por la complejidad funcional y el trabajo de validación e integración descritos en las referencias (Lavazza et al., 2024; Alaswad et al., 2026).

| Área funcional y de entrega | Horas-persona estimadas |
|---|---:|
| Análisis, delimitación funcional y coordinación | 28–36 |
| Autenticación, roles y perfil del cliente | 44–58 |
| Catálogo, administración e inventario | 50–66 |
| Motor de recomendaciones | 32–42 |
| Ventas y asignación de sensores | 34–46 |
| API IoT, lecturas, umbrales y alertas | 40–54 |
| Historial, alertas y tendencias para el cliente | 42–56 |
| Integración, validación básica, seguridad, documentación e instalación inicial | 54–72 |
| **Total estimado** | **324–430** |

La tarifa analítica asumida de S/ 50 por hora-persona se utiliza únicamente para convertir esfuerzo hipotético en un costo interno de referencia. No fue publicada ni validada por las investigaciones citadas; la reserva del 15–25 % y el recargo comercial del 20–30 % también son supuestos propios. Aplicando esos supuestos a los extremos del intervalo se obtiene el rango indicativo de S/ 22 400–34 900 antes de impuestos (cálculo propio). El precio recomendado usa el punto medio de esfuerzo asistido (377 horas), una reserva del 20 % y un recargo comercial del 24 %, y se redondea a S/ 28 000 (cálculo propio). Esta operación es una comprobación de viabilidad del precio, no afirma que el valor comercial de la solución sea igual a las horas empleadas. La hipótesis de productividad de IA se presenta por separado y no se multiplica como descuento automático al precio; los artículos no calibran las horas, tarifas ni precio de Adaptia (Lavazza et al., 2024; Paradis et al., s. f.; Alaswad et al., 2026).

## Justificación basada en los artículos

1. **El tamaño funcional ayuda a ordenar el alcance, pero no determina por sí solo el precio.** Lavazza, Locoro y Meli comparan Function Points sin ajustar (UFP) con medidas funcionales simplificadas en proyectos nuevos, extensiones y mejoras. En 15 de 24 comparaciones generales, las medidas simplificadas obtienen precisión equivalente a UFP; además, los errores de estimación crecen con la complejidad sin que las medidas analizadas la capturen adecuadamente (Lavazza et al., 2024, secciones 5.1–5.3). Por ello, aquí se separan las funcionalidades de Adaptia y se añade explícitamente esfuerzo de integración y validación; no se convierte un conteo de pantallas o tablas directamente en dinero. El estudio analiza proyectos del conjunto ISBSG y no proporciona una tarifa aplicable a Adaptia (Lavazza et al., 2024, sección 4.1).

2. **La asistencia de IA puede acelerar tareas, pero el ahorro no es constante ni garantiza igual calidad.** Paradis et al. reportan un ensayo aleatorizado con 96 ingenieros de Google: la comparación inicial encuentra menor tiempo de tarea en el grupo con IA y los modelos ajustados estiman una reducción aproximada del tiempo de 21–26 %, pero el efecto ajustado del modelo principal no alcanza significación estadística convencional y su intervalo de confianza incluye ausencia de efecto (Paradis et al., s. f., secciones V–VII). Los autores advierten que es una única tarea en un contexto empresarial específico y que los resultados no necesariamente se generalizan a otros equipos o herramientas (Paradis et al., s. f., secciones V y VIII). Por tanto, no se descuenta automáticamente un porcentaje de productividad del precio de Adaptia.

3. **La productividad adicional puede trasladar trabajo a revisión y mantenimiento.** En un análisis de proyectos de software abierto, Xu et al. encuentran más retrabajo en pull requests tras la introducción de Copilot y una redistribución de actividad hacia el mantenimiento de los contribuyentes centrales (Xu et al., s. f., secciones 4 y 7). Estos resultados corresponden a ese contexto, no son un porcentaje transferible a Adaptia ni justifican sumar una penalización fija. Sí motivan considerar revisión, correcciones y mantenimiento potencial al valorar el trabajo, en lugar de medir únicamente la velocidad de producción (Xu et al., s. f.).

4. **En flujos asistidos por LLM, parte del esfuerzo está en comprender, iterar, verificar e integrar.** Alaswad, Poovammal y Aljaddouh proponen un marco conceptual de esfuerzo híbrido con cinco dimensiones: complejidad del razonamiento del LLM, completitud del contexto, impacto de las transformaciones, ciclos iterativos y supervisión humana (Alaswad et al., 2026, sección 4). El artículo señala que el marco es conceptual, todavía no está calibrado empíricamente y no ofrece una fórmula predictiva validada (Alaswad et al., 2026, secciones 6.3 y 7). Aquí se usa como lista cualitativa para revisar el esfuerzo y los riesgos, no como modelo numérico ni como prueba de que usar IA reduzca el precio.

En conjunto, los artículos respaldan considerar alcance funcional, incertidumbre, mantenimiento y validación al estimar esfuerzo, aunque sus contextos y limitaciones impiden trasladar directamente sus resultados a este producto (Lavazza et al., 2024; Paradis et al., s. f.; Xu et al., s. f.; Alaswad et al., 2026). **No permiten deducir por sí solos un precio de venta en soles**; la tarifa, el recargo y las horas de Adaptia son supuestos explícitos de esta estimación y deben confirmarse con cotizaciones locales, el cliente y el alcance contractual.

## Condiciones para convertirlo en cotización

Antes de presentar el precio como oferta definitiva, se recomienda:

1. Cerrar los criterios de aceptación de cada módulo y confirmar qué funciones se consideran listas para entregar.
2. Precisar si la instalación inicial incluye configuración de servidor, carga de datos, capacitación y correcciones posteriores.
3. Cotizar por separado las fases pendientes, el hardware IoT, las notificaciones y el mantenimiento.
4. Validar la tarifa con proveedores locales y definir por contrato la licencia, los derechos sobre el código, los impuestos, los plazos y la garantía.

## Referencias consultadas

- Xu, Feiyang (Amber), Poonacha K. Medappa, Murat M. Tunc, Martijn Vroegindeweij, and Jan C. Fransoo. [AI-Assisted Programming Decreases the Productivity of Experienced Developers by Increasing the Technical Debt and Maintenance Burden](./AI-Assisted%20Programming%20Decreases%20the%20Productivity%20of%20Experienced%20Developers%20by%20Increasing%20the%20Technical%20Debt%20and%20Maintenance%20Burden.md). Documento consultado en `docs/`; el archivo no indica año, revista ni DOI (citado como s. f.).
- Paradis, Elise, Kate Grey, Quinn Madison, Daye Nam, Andrew Macvean, Vahid Meimand, Nan Zhang, Ben Ferrari-Church, and Satish Chandra. [How much does AI impact development speed? An enterprise-based randomized controlled trial](./How%20much%20does%20AI%20impact%20development%20speed%20An%20enterprise-based%20randomized%20controlled%20trial.md). Documento consultado en `docs/`; el archivo no indica año, revista ni DOI (citado como s. f.).
- Lavazza, Luigi, Angela Locoro, and Roberto Meli. [Software Development and Maintenance Effort Estimation Using Function Points and Simpler Functional Measures](./Software%20Development%20and%20Maintenance%20Effort%20Estimation%20Using%20Function%20Points%20and%20Simpler%20Functional%20Measures.md). *Software*, 2024, 3, 442–472. https://doi.org/10.3390/software3040022.
- Alaswad, Feisal, Eswaran Poovammal, and Batoul Aljaddouh. [Toward LLM-aware software effort estimation: a conceptual framework](./Toward%20LLM-aware%20software%20effort%20estimation%20a%20conceptual%20framework.md). *Frontiers in Artificial Intelligence*, 2026, 9:1772418. https://doi.org/10.3389/frai.2026.1772418.
