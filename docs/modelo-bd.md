@startuml Adaptia_ModeloBD

hide circle
skinparam linetype ortho
skinparam nodesep 60
skinparam ranksep 70

' ---- Capa 1: Usuarios ----
entity "users" as users {
  * id : bigint <<PK>>
  --
  name : string
  email : string
  password : string
  rol : enum(cliente, vendedor, administrador)
}

entity "perfiles_cliente" as perfiles_cliente {
  * id : bigint <<PK>>
  --
  * user_id : bigint <<FK>>
  tamaño_adulto : string
  luz_requerida : string
  toxicidad : boolean
  frecuencia_riego : string
  tipo_ambiente : string
  estetica : string
  nivel_cuidado : string
}

' ---- Capa 2: Catálogo ----
entity "tipos_planta" as tipos_planta {
  * id : bigint <<PK>>
  --
  nombre : string
}

entity "plantas" as plantas {
  * id : bigint <<PK>>
  --
  * tipo_planta_id : bigint <<FK>>
  nombre : string
  descripcion : text
  imagen : string
  toxicidad : boolean
  luz_requerida : enum(baja, media, alta, siempre_en_el_sol)
  frecuencia_riego : enum(diario,cada_3_dias,semanal,quincenal,mensualmente)
  tamaño_adulto : enum(pequena, mediana, grande)
  nivel_cuidado : enum(principiante, intermedio, experto)
  tipo_ambiente : enum(interiores, exteriores, ambos)
  estetica : enum(follaje,flor,colgantes,suculentas)
  stock_actual : integer
  stock_minimo : integer
  precio : decimal
  humedad_suelo_min : decimal (nullable)
  humedad_suelo_max : decimal (nullable)
  temperatura_min : decimal (nullable)
  temperatura_max : decimal (nullable)
  humedad_ambiental_min : decimal (nullable)
  humedad_ambiental_max : decimal (nullable)
  luz_min : decimal (nullable)
  luz_max : decimal (nullable)
}

' ---- Capa 3: Mantenimiento ----
entity "casos_mantenimiento" as casos_mantenimiento {
  * id : bigint <<PK>>
  --
  * solicitante_id : bigint <<FK>>
  codigo : string <<unique>>
  area_verde : string
  descripcion : text
  fecha_deseada : date (nullable)
  observaciones : json (nullable)
  tipo_mantenimiento : string
  urgencia : enum(baja, media, alta)
  estado : string
  created_at : timestamp
  updated_at : timestamp
}

entity "evidencias_caso" as evidencias_caso {
  * id : bigint <<PK>>
  --
  * caso_id : bigint <<FK>>
  * autor_id : bigint <<FK>>
  tipo : enum(nota, foto)
  contenido : text (nullable)
  ruta : string (nullable)
  descripcion : string (nullable)
  created_at : timestamp
  updated_at : timestamp
}

entity "diagnosticos_caso" as diagnosticos_caso {
  * id : bigint <<PK>>
  --
  * caso_id : bigint <<FK>>
  * autor_id : bigint <<FK>>
  * version : integer
  severidad : string
  resumen : text
  justificacion : text
  evaluado_en : timestamp
  created_at : timestamp
  updated_at : timestamp
}

entity "hallazgos_diagnostico" as hallazgos_diagnostico {
  * id : bigint <<PK>>
  --
  * diagnostico_id : bigint <<FK>>
  tipo : string
  variable : string (nullable)
  severidad : string
  descripcion : text
  created_at : timestamp
  updated_at : timestamp
}

' ---- Capa 3: Plan de mantenimiento ----
entity "recursos_catalogo" as recursos_catalogo {
  * id : bigint <<PK>>
  --
  tipo : string
  * codigo : string <<unique>>
  nombre : string
  unidad : string
  stock_actual : decimal(10,2)
  stock_minimo : decimal(10,2) (nullable)
}

entity "planes_mantenimiento" as planes_mantenimiento {
  * id : bigint <<PK>>
  --
  * caso_id : bigint <<FK>>
  * autor_id : bigint <<FK>>
  * version : integer
  generado_en : timestamp
  descripcion : text
}

entity "tareas_plan" as tareas_plan {
  * id : bigint <<PK>>
  --
  * plan_id : bigint <<FK>>
  * codigo : string
  titulo : string
  descripcion : text
  prioridad : string
  criticidad : string
  depende_de_tarea_id : bigint <<FK>> (nullable)
  fecha_estimada : date (nullable)
  duracion_dias : integer
  estado : string
}

entity "tarea_recursos" as tarea_recursos {
  * id : bigint <<PK>>
  --
  * tarea_id : bigint <<FK>>
  * recurso_id : bigint <<FK>>
  cantidad : decimal(10,2)
}

entity "items_checklist" as items_checklist {
  * id : bigint <<PK>>
  --
  * tarea_id : bigint <<FK>>
  descripcion : text
  cumplido : boolean
}

' ---- Capa 3: Abastecimiento ----
entity "requisiciones_mantenimiento" as requisiciones_mantenimiento {
  * id : bigint <<PK>>
  --
  * caso_id : bigint <<FK>>
  * autor_id : bigint <<FK>>
  estado : string
  motivo : text (nullable)
}

entity "requisicion_items" as requisicion_items {
  * id : bigint <<PK>>
  --
  * requisicion_id : bigint <<FK>>
  * recurso_id : bigint <<FK>>
  cantidad_solicitada : decimal(10,2)
  cantidad_recibida : decimal(10,2)
  estado : string
}

entity "movimientos_material" as movimientos_material {
  * id : bigint <<PK>>
  --
  requisicion_item_id : bigint <<FK>> (nullable)
  * caso_id : bigint <<FK>>
  * recurso_id : bigint <<FK>>
  tipo : string
  stock_antes : decimal(10,2)
  stock_despues : decimal(10,2)
  cantidad : decimal(10,2)
  motivo : text
  * autor_id : bigint <<FK>>
}

' ---- Capa 4: Ejecución y cierre ----
entity "eventos_ejecucion" as eventos_ejecucion {
  * id : bigint <<PK>>
  --
  * caso_id : bigint <<FK>>
  tarea_id : bigint <<FK>> (nullable)
  tipo : string
  descripcion : text
  * autor_id : bigint <<FK>>
  fecha : timestamp
  resuelta_en : timestamp (nullable)
}

entity "informes_cierre" as informes_cierre {
  * id : bigint <<PK>>
  --
  * caso_id : bigint <<FK>>
  * autor_id : bigint <<FK>>
  * destinatario_id : bigint <<FK>>
  * version : integer
  resumen : text
  contenido : text
  notificacion_pendiente : boolean
  cerrado_en : timestamp
}

' ---- Capa 3: Ventas ----
entity "ventas" as ventas {
  * id : bigint <<PK>>
  --
  * user_id : bigint <<FK>>
  * vendedor_id : bigint <<FK>>
  * planta_id : bigint <<FK>>
  cantidad : integer
  fecha : timestamp
}

entity "movimientos_inventario" as movimientos_inventario {
  * id : bigint <<PK>>
  --
  * planta_id : bigint <<FK>>
  * user_id : bigint <<FK>>
  tipo : enum(entrada, salida)
  cantidad : integer
}

' ---- Capa 4: Monitoreo IoT ----
entity "plantas_vendidas" as plantas_vendidas {
  * id : bigint <<PK>>
  --
  * venta_id : bigint <<FK>>
  * user_id : bigint <<FK>>
  * sensor_id : bigint <<FK>>
}

entity "sensores" as sensores {
  * id : bigint <<PK>>
  --
  identificador_fisico : string
  token_hash : string (nullable, unique)
  estado : enum(activo, inactivo)
}

entity "lecturas_sensores" as lecturas_sensores {
  * id : bigint <<PK>>
  --
  * planta_vendida_id : bigint <<FK>>
  humedad_suelo : decimal
  temperatura : decimal
  humedad_ambiental : decimal
  luz : decimal
  fecha_hora : timestamp
}

' ---- Capa 5: Alertas ----
entity "alertas" as alertas {
  * id : bigint <<PK>>
  --
  planta_vendida_id : bigint <<FK>>
  tipo : enum(riego, abono, stock_bajo, cuidado_planta)
  variable : string (nullable)
  valor_medido : decimal (nullable)
  limite : decimal (nullable)
  rango_minimo : decimal (nullable)
  rango_maximo : decimal (nullable)
  direccion : enum(bajo, alto) (nullable)
  mensaje : text (nullable)
  leida : boolean
  resuelta_en : timestamp (nullable)
}

entity "notificaciones" as notificaciones {
  * id : bigint <<PK>>
  --
  * user_id : bigint <<FK>>
  alerta_id : bigint <<FK>>
  canal : enum(push, email)
  enviada : boolean
}

' ---- Relaciones agrupadas por capa (arriba hacia abajo) ----
users ||--o| perfiles_cliente
tipos_planta ||--o{ plantas

users ||--o{ casos_mantenimiento
casos_mantenimiento ||--o{ evidencias_caso
users ||--o{ evidencias_caso
casos_mantenimiento ||--o{ planes_mantenimiento
casos_mantenimiento ||--o{ requisiciones_mantenimiento
users ||--o{ requisiciones_mantenimiento
requisiciones_mantenimiento ||--o{ requisicion_items
recursos_catalogo ||--o{ requisicion_items
requisicion_items ||--o{ movimientos_material
casos_mantenimiento ||--o{ movimientos_material
casos_mantenimiento ||--o{ eventos_ejecucion
users ||--o{ eventos_ejecucion
tareas_plan ||--o{ eventos_ejecucion
casos_mantenimiento ||--o{ informes_cierre
users ||--o{ informes_cierre : autor_id
users ||--o{ informes_cierre : destinatario_id
recursos_catalogo ||--o{ movimientos_material
users ||--o{ movimientos_material
users ||--o{ planes_mantenimiento
planes_mantenimiento ||--o{ tareas_plan
tareas_plan ||--o{ tareas_plan : depende_de_tarea_id
tareas_plan ||--o{ tarea_recursos
recursos_catalogo ||--o{ tarea_recursos
tareas_plan ||--o{ items_checklist
casos_mantenimiento ||--o{ diagnosticos_caso
users ||--o{ diagnosticos_caso
diagnosticos_caso ||--o{ hallazgos_diagnostico

users ||--o{ ventas
plantas ||--o{ ventas
plantas ||--o{ movimientos_inventario
users ||--o{ movimientos_inventario

ventas ||--o{ plantas_vendidas
users ||--o{ plantas_vendidas
sensores ||--o| plantas_vendidas

plantas_vendidas ||--o{ lecturas_sensores
plantas_vendidas ||--o{ alertas
alertas ||--o{ notificaciones
users ||--o{ notificaciones

@enduml