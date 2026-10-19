# Estimación del precio comercial de Adaptia

**Moneda:** soles peruanos (PEN)  
**Fecha de estimación:** 18 de octubre de 2026  
**Tipo de estimación:** referencia comercial preliminar, no cotización contractual

## Precio de referencia

Se recomienda un precio comercial de referencia de **S/ 28 000, antes del IGV**, para una licencia de uso no exclusiva de la versión funcional descrita en este documento, su instalación inicial en un entorno del cliente y una entrega básica del sistema.

El intervalo estimado es **S/ 22 400 a S/ 34 900, antes del IGV**. La variación depende principalmente de la complejidad de la instalación, los datos iniciales, los ajustes de aceptación y el nivel de validación requerido. La tasa por hora indicada abajo es un supuesto de cálculo, no una tarifa de mercado verificada.

| Concepto | Estimación base |
|---|---:|
| Esfuerzo de referencia | 377 horas-persona |
| Tarifa analítica asumida | S/ 50 por hora-persona |
| Costo base calculado | S/ 18 850 |
| Reserva de riesgo y retrabajo (20 % del costo base) | S/ 3 770 |
| Subtotal con reserva | S/ 22 620 |
| Recargo comercial supuesto (24 % del subtotal) | S/ 5 429 |
| Precio calculado | S/ 28 049 |
| **Precio recomendado redondeado** | **S/ 28 000** |

El precio recomendado no incluye IGV. Si corresponde aplicar la tasa general vigente del 18 %, el total referencial sería **S/ 33 040**. La aplicación de impuestos debe confirmarse según la situación tributaria del proveedor y del cliente.

## Alcance considerado

La estimación considera el producto web Laravel y las funciones implementadas hasta el alcance descrito para las fases 1 a 4:

- Autenticación y separación de permisos para cliente, vendedor y administrador.
- Administración de tipos y catálogo de plantas, existencias y movimientos de inventario, incluida la alerta de stock bajo.
- Perfil del cliente y recomendaciones de plantas según compatibilidad y restricciones de toxicidad.
- Registro de ventas con actualización de existencias y asociación de sensores a plantas vendidas.
- Administración de sensores, recepción autenticada de lecturas IoT, evaluación de umbrales y generación y resolución de alertas.
- Consulta del cliente de alertas e historial de mediciones con visualización de tendencias.
- Integración de las funciones anteriores, pruebas de aceptación básicas, documentación de entrega e instalación inicial.

Este es un precio de referencia para un despliegue inicial de un cliente. No representa una valoración de la propiedad intelectual exclusiva ni el costo ya incurrido por el equipo del proyecto. La licencia, la titularidad del código fuente, las garantías y las obligaciones de soporte deben quedar definidas en el acuerdo comercial.

## Exclusiones

No se consideran en este precio:

- Firmware del ESP32, compra de sensores, instalación física ni conectividad IoT.
- Notificaciones push o por correo, que corresponden a la fase 5 pendiente.
- Pruebas end-to-end con Cypress, dockerización completa y otros trabajos de cierre técnico aún pendientes.
- Hosting, dominio, almacenamiento, servicios externos, operación continua y mantenimiento posterior a la entrega.
- Migración o carga masiva de datos, capacitación extensa, cambios de alcance o integraciones no descritas.
- Cesión exclusiva de derechos de propiedad intelectual.

Por lo tanto, el monto no debe interpretarse como el precio de una solución ya terminada con todas las fases del proyecto ni como una cotización para operar el sistema a escala productiva.

## Método de estimación

Se usó una estimación de esfuerzo por funcionalidades observables para el usuario, contrastada con los factores de complejidad, validación, integración y retrabajo descritos en los cuatro artículos de referencia. No se afirma haber realizado un conteo certificado de Function Points (FP), ni que alguno de los estudios proporcione una tarifa en soles.

La siguiente descomposición representa el esfuerzo hipotético para valorar una entrega equivalente al alcance considerado; **no es un registro de horas realmente trabajadas**.

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

La tarifa analítica asumida de S/ 50 por hora-persona se utiliza únicamente para convertir esfuerzo hipotético en un monto. No fue publicada ni validada por las investigaciones citadas. Con los extremos del intervalo, una reserva del 15–25 % y un recargo comercial del 20–30 %, se obtiene el rango indicativo de S/ 22 400–34 900 antes de impuestos. El precio recomendado usa el punto medio de esfuerzo (377 horas), una reserva del 20 % y un recargo comercial del 24 %, redondeado a S/ 28 000.

## Justificación basada en los artículos

1. **El tamaño funcional ayuda a ordenar el alcance, pero no determina por sí solo el precio.** Lavazza, Locoro y Meli comparan Function Points sin ajustar (UFP) con medidas funcionales simplificadas en proyectos nuevos, extensiones y mejoras. En 15 de 24 comparaciones generales, las medidas simplificadas obtienen precisión equivalente a UFP; además, los errores de estimación crecen con la complejidad sin que las medidas analizadas la capturen adecuadamente. Por ello, aquí se separan las funcionalidades de Adaptia y se añade explícitamente esfuerzo de integración y validación; no se convierte un conteo de pantallas o tablas directamente en dinero. El estudio usa proyectos del conjunto ISBSG y no proporciona una tarifa aplicable a Adaptia.

2. **La asistencia de IA puede acelerar tareas, pero el ahorro no es constante ni garantiza igual calidad.** Paradis et al. reportan un ensayo aleatorizado con 96 ingenieros de Google: en el análisis ajustado estiman que el grupo con IA fue aproximadamente 21–26 % más rápido, pero el efecto ajustado no alcanza significación estadística convencional y el intervalo de confianza es amplio. Los autores advierten que es una única tarea y un contexto empresarial específico. Por tanto, no se descuenta automáticamente un 21 % del precio de Adaptia.

3. **La productividad adicional puede trasladar trabajo a revisión y mantenimiento.** Xu et al. estudian proyectos de software abierto y encuentran, en ese contexto, más retrabajo en pull requests tras la introducción de Copilot y una redistribución de actividad hacia el mantenimiento. Estos resultados no son un porcentaje transferible a Adaptia ni justifican sumar una penalización fija; respaldan reservar esfuerzo para revisión, correcciones y mantenimiento en vez de valorar únicamente la velocidad de producción.

4. **En flujos asistidos por LLM, parte del esfuerzo está en comprender, iterar, verificar e integrar.** Alaswad, Poovammal y Aljaddouh proponen un marco conceptual de esfuerzo híbrido que incluye complejidad del razonamiento, completitud del contexto, impacto de las transformaciones, ciclos iterativos y supervisión humana. El artículo señala que el marco todavía no está calibrado empíricamente y no ofrece una fórmula predictiva validada. Se utiliza aquí como lista cualitativa para revisar el esfuerzo y los riesgos, no como modelo numérico ni como prueba de que usar IA reduzca el precio.

En conjunto, los artículos sustentan una estimación por alcance con una reserva visible para incertidumbre y calidad. **No permiten deducir por sí solos un precio de venta en soles**; la tarifa, el recargo y las horas de Adaptia son supuestos explícitos de esta estimación y deben confirmarse con cotizaciones locales, el cliente y el alcance contractual.

## Condiciones para convertirlo en cotización

Antes de presentar el precio como oferta definitiva, se recomienda:

1. Cerrar los criterios de aceptación de cada módulo y confirmar qué funciones se consideran listas para entregar.
2. Precisar si la instalación inicial incluye configuración de servidor, carga de datos, capacitación y correcciones posteriores.
3. Cotizar por separado las fases pendientes, el hardware IoT, las notificaciones y el mantenimiento.
4. Validar la tarifa con proveedores locales y definir por contrato la licencia, los derechos sobre el código, los impuestos, los plazos y la garantía.

## Referencias consultadas

- Xu, Feiyang (Amber), et al. [AI-Assisted Programming Decreases the Productivity of Experienced Developers by Increasing the Technical Debt and Maintenance Burden](./AI-Assisted%20Programming%20Decreases%20the%20Productivity%20of%20Experienced%20Developers%20by%20Increasing%20the%20Technical%20Debt%20and%20Maintenance%20Burden.md).
- Paradis, Elise, et al. [How much does AI impact development speed? An enterprise-based randomized controlled trial](./How%20much%20does%20AI%20impact%20development%20speed%20An%20enterprise-based%20randomized%20controlled%20trial.md).
- Lavazza, Luigi, Angela Locoro, and Roberto Meli. [Software Development and Maintenance Effort Estimation Using Function Points and Simpler Functional Measures](./Software%20Development%20and%20Maintenance%20Effort%20Estimation%20Using%20Function%20Points%20and%20Simpler%20Functional%20Measures.md). *Software*, 2024, 3, 442–472. https://doi.org/10.3390/software3040022.
- Alaswad, Feisal, Eswaran Poovammal, and Batoul Aljaddouh. [Toward LLM-aware software effort estimation: a conceptual framework](./Toward%20LLM-aware%20software%20effort%20estimation%20a%20conceptual%20framework.md). *Frontiers in Artificial Intelligence*, 2026, 9:1772418. https://doi.org/10.3389/frai.2026.1772418.
