# Contexto del proyecto: Adaptia

## Qué es
Adaptia es un sistema web en Laravel para la tienda de plantas GardenLand
(Huancayo). Tiene cuatro módulos:

1. **Usuarios, clientes y accesos** — cuentas de cliente con login,
   autenticación de vendedor/administrador, roles.
2. **Recomendador de plantas** (motor multicriterio) — CASI TERMINADO
3. **Ventas + inventario del vivero** (CRUD) — EN PROGRESO
4. **Monitoreo IoT** (sensor ESP32) + **Notificaciones** — PENDIENTE

## Cambio de alcance importante (no usar la versión anterior)
Antes se había decidido que el cliente NO tendría cuenta (acceso solo por
código único). ESO YA NO APLICA. La versión actual del proyecto SÍ incluye:
- Cuentas de cliente con login.
- Roles/permisos administrativos (cliente / vendedor / administrador).
- Notificaciones push/correo.
- Gráficos históricos de tendencia (no solo listado de historial).

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

## Arquitectura (seguir siempre esta convención)
Route → Controller → Service → Eloquent/Model → Database
- Controllers delgados: solo reciben el request y llaman al Service.
- Lógica de negocio SIEMPRE en Services, nunca en el Controller.
- Los Services llaman a Eloquent directamente (sin capa
  Repository/Interface — decisión consciente, no la agregues).
- Separar en el Service: métodos públicos que tocan DB vs métodos
  privados de cálculo puro (testeables sin DB).

## Dónde estamos ahora mismo
Terminando el módulo 2 (recomendador). Lo próximo, en este orden:
1. CRUD admin de `tipos_planta` y `plantas`.
2. Sistema de login/registro para users (con rol), + perfiles_cliente.
3. Vista pública del formulario de perfil del cliente (RF-01) y ranking
   de recomendaciones (RF-04), ahora ligados a la cuenta del cliente.
4. Módulo de ventas + inventario (movimientos_inventario).
5. Módulo IoT: sensores, lecturas_sensores, alertas.
6. Módulo de notificaciones (push/correo) sobre las alertas generadas.

## Stack
Laravel + Blade + Livewire + MySQL. Testing: Pest (unit + feature).
Cypress para end-to-end (más adelante).

## Estilo esperado
Código simple y legible por sobre "elegante" — proyecto académico de
pregrado. No Repository, no CQRS, no Domain Events. Prioriza que cada
pieza sea fácil de explicar en una sustentación.