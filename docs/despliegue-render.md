# Preparación del despliegue en Render

## Alcance del Blueprint

`render.yaml` describe un Web Service Docker llamado `adaptia`, en Ohio, plan Free, desde la rama `main`, con chequeo de salud en `/up`. No se configura un comando de migración durante el build o el arranque.

El Blueprint declara `APP_KEY`, `APP_URL`, datos de conexión Aiven y credenciales Cloudinary como valores `sync: false`. Se deben ingresan únicamente en **Environment** de Render, nunca en el repositorio. La ruta SSL `MYSQL_ATTR_SSL_CA` apunta al certificado incluido en la imagen por el Dockerfile.

No regenerar `APP_KEY`: reutilizar la clave existente de la aplicación en el servicio de Render.

## Verificaciones antes de crear o desplegar el servicio

1. Verificar en Aiven que `docker/ssl/ca.pem` sea el CA vigente del servicio y que la aplicación pueda autenticar por SSL.
2. Conectar Laravel usando exclusivamente las variables del Environment de Render; no cambiar el `.env` local ni su endpoint XAMPP.
3. Ejecutar `php artisan migrate:status --database=mysql` contra Aiven y revisar las columnas de imágenes antes de cualquier migración.
4. Confirmar autorización para aplicar migraciones. No se ejecutan migraciones en la construcción ni en el arranque del contenedor.
5. Inspeccionar con `cloudinary:migrate-local-images --dry-run` cuántas rutas locales faltan. Los archivos legacy locales no se comparten con Render Free, que no ofrece almacenamiento persistente en disco. No ejecutar el comando sin autorización expresa para cargar las imágenes a Cloudinary.
6. Mostrar el diff de configuración y obtener aprobación antes de crear el Blueprint, activar despliegues o publicar la URL.

## Persistencia

- Las plantas y evidencias nuevas deben utilizar Cloudinary.
- Sesiones y caché se configuran con tablas de Aiven para evitar depender del disco efímero del contenedor. Estas tablas deben existir antes de servir tráfico.
- Render Free no proporciona disco persistente. Cualquier archivo que quede en `storage/` dentro del contenedor se perderá al reconstruir o reiniciar el servicio.
- Los datos de negocio continúan en Aiven; la imagen incluye el CA, pero no contiene credenciales.

## Variables de Render Environment

Configurar los valores sensibles solo en Render:

- `APP_KEY`: clave existente de Laravel.
- `APP_URL`: URL HTTPS asignada por Render.
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`: parámetros vigentes de Aiven.
- `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_KEY`, `CLOUDINARY_SECRET`: credenciales de Cloudinary.

Las variables no secretas `APP_ENV`, `APP_DEBUG`, `LOG_CHANNEL`, `DB_CONNECTION`, `MYSQL_ATTR_SSL_CA`, `FILESYSTEM_DISK`, `SESSION_DRIVER`, `SESSION_SECURE_COOKIE` y `CACHE_STORE` están declaradas en el Blueprint.

No incluir `APP_KEY`, contraseñas, tokens ni credenciales Cloudinary en logs, Dockerfile o archivos versionados.
