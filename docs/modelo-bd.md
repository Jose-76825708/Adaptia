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
  espacio : string
  luz : string
  mascotas_ninos : boolean
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
  es_toxica : boolean
  luz_requerida : enum(baja, media, alta, siempre_en_el_sol)
  espacio_requerido : enum(pequena, mediana, grande)
  tipo_ambiente : enum(interiores, exteriores, ambos)
  frecuencia_riego : enum(diario,cada_3_dias,semanal,quincenal,mensualmente)
  estetica : enum(follaje,flor,colgantes,suculenta)
  nivel_cuidado : enum(principiante, intermedio, experto)
  stock_actual : integer
  stock_minimo : integer
  precio : decimal
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
  estado : enum(activo, inactivo)
}

entity "lecturas_sensores" as lecturas_sensores {
  * id : bigint <<PK>>
  --
  * planta_vendida_id : bigint <<FK>>
  humedad : decimal
  temperatura : decimal
  fecha_hora : timestamp
}

' ---- Capa 5: Alertas ----
entity "alertas" as alertas {
  * id : bigint <<PK>>
  --
  planta_vendida_id : bigint <<FK>>
  tipo : enum(riego, abono, stock_bajo)
  leida : boolean
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