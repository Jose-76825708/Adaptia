# Contexto del proyecto: Adaptia

## Qué es
Adaptia es un sistema web en Laravel para la tienda de plantas GardenLand
(Huancayo). Tiene cuatro módulos:

1. **Usuarios, clientes y accesos** — cuentas de cliente con login,
   autenticación de vendedor/administrador, roles.
2. **Recomendador de plantas** (motor multicriterio) — COMPLETADO
3. **Ventas + inventario del vivero** (CRUD) — EN PROGRESO
4. **Monitoreo IoT** (sensor ESP32) + **Notificaciones** — PENDIENTE

## Cambio de alcance importante (no usar la versión anterior)
Antes se había decidido que el cliente NO tendría cuenta (acceso solo por
código único). ESO YA NO APLICA. La versión actual del proyecto SÍ incluye:
- Cuentas de cliente con login.
- Roles/permisos administrativos (cliente / vendedor / administrador).
- Notificaciones push/correo.
- Gráficos históricos de tendencia (no solo listado de historial).

## Orden de trabajo (NO saltar fases)
Trabajamos en orden estricto: **Administrador → Cliente → Vendedor → IoT → Notificaciones**.
No avances a una fase nueva hasta que la actual esté completa y confirmada
por José. Si te pido algo que pertenece a una fase posterior a la actual,
avísame antes de hacerlo ("esto es de una fase posterior, ¿seguro quieres
adelantarlo?") en vez de simplemente ejecutarlo.

**Fase actual: FASE 1 (Administrador) — pendiente cerrar antes de seguir.**

### FASE 1 — Administrador
- [x] CRUD `tipos_planta`
- [x] CRUD `plantas`
- [ ] CRUD `movimientos_inventario` (entradas/salidas de stock)
- [ ] Alerta de stock bajo por especie (RF-12)
- [ ] CRUD `sensores` (registrar sensores disponibles antes de asignarlos)

### FASE 2 — Cliente
- [x] Login/registro con rol
- [x] `perfiles_cliente` (edición de perfil)
- [ ] Integrar `RecomendacionService` en la vista pública (ranking real)
- [ ] Vista de historial/alertas del cliente (RF-10) — puede quedar vacía
      hasta que exista Fase 4

### FASE 3 — Vendedor
- [ ] Login de vendedor (mismo sistema, distinto rol)
- [ ] Registrar venta (`ventas`) → descuenta stock automáticamente
- [ ] Asignar sensor a `plantas_vendidas`

### FASE 4 — Monitoreo IoT
- [ ] Endpoint `POST /api/lecturas` (recibe JSON del ESP32, valida, guarda)
- [ ] Evaluación de umbrales + generación de alerta (RF-08, RF-09)
- [ ] Conectar la alerta a la vista de historial del cliente (Fase 2)
- [ ] Firmware del ESP32 (en paralelo, no bloquea el backend)

### FASE 5 — Notificaciones
- [ ] Notificaciones push/correo sobre las alertas generadas (Fase 4)

### FASE 6 — Cierre técnico y documental (en paralelo a las fases 3-5)
- [ ] Tests Pest de cada módulo nuevo, mismo patrón que el recomendador
- [ ] Pruebas end-to-end con Cypress del flujo completo
- [ ] Dockerización (backend + frontend + BD)
- [ ] Documentar cada capítulo del informe a medida que su fase cierra

## Módulo 2: Recomendador (completado, no modificar sin avisar)
- `app/Services/RecomendacionService.php` ya tiene: limpiarPerfil,
  compatibilidadOrdinal, compatibilidadCategoria, calculaScore,
  plantasNoToxicas (filtro eliminatorio duro), generarRecomendaciones
  (orquesta: fetch DB + scoring + ranking).
- Enfoque: filtros eliminatorios (ej. toxicidad si hay mascotas/niños)
  + score ponderado sobre criterios blandos.
- Ya tiene tests con Pest (unitarios sobre las funciones puras, sin DB).

## Modelo de datos
**Ver `docs/modelo-bd.md` para el modelo completo y actualizado (PlantUML).**
Ese archivo reemplaza cualquier esquema mencionado antes en el proyecto.
Resumen de entidades: users (con rol), perfiles_cliente, tipos_planta,
plantas, ventas, plantas_vendidas, sensores, lecturas_sensores, alertas,
notificaciones, movimientos_inventario.

Antes de tocar migraciones, comparar contra `docs/modelo-bd.md`. Si una
tabla existente (tipos_planta, plantas, users) ya tiene datos/tests
corriendo, agregar una migración adicional (ej. `add_rol_to_users_table`)
en vez de reescribir la migración original.

**Cuidado con foreignId()->constrained() en tablas nombradas en español:**
Laravel infiere el nombre de tabla en inglés (singular + "s"). Si el nombre
no coincide exactamente (ej. `sensor_id` → Laravel asume `sensors`, pero la
tabla real es `sensores`), especifica el nombre explícito:
`->constrained('sensores')`. Revisar esto en cada FK nueva antes de migrar.

## Arquitectura (seguir siempre esta convención)
Route → Controller → Service → Eloquent/Model → Database
- Controllers delgados: solo reciben el request y llaman al Service.
- Lógica de negocio SIEMPRE en Services, nunca en el Controller.
- Los Services llaman a Eloquent directamente (sin capa
  Repository/Interface — decisión consciente, no la agregues).
- Separar en el Service: métodos públicos que tocan DB vs métodos
  privados de cálculo puro (testeables sin DB).

## Stack
Laravel + Blade + Livewire + MySQL. Testing: Pest (unit + feature).
Cypress para end-to-end (más adelante).

## Estilo esperado
Código simple y legible por sobre "elegante" — proyecto académico de
pregrado. No Repository, no CQRS, no Domain Events. Prioriza que cada
pieza sea fácil de explicar en una sustentación.