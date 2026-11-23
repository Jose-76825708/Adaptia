# Migración de imágenes a Cloudinary

Las imágenes nuevas de plantas y las fotos de evidencias se guardan en Cloudinary. La aplicación conserva `secure_url` en `plantas.imagen` o `evidencias_caso.ruta`, y guarda el `public_id` correspondiente en `imagen_public_id` o `public_id`.

## Migrar registros locales existentes

1. Verifica que el disco `cloudinary` esté configurado en el entorno y realiza un respaldo de la base de datos.
2. Aplica la migración de esquema:

   ```sh
   php artisan migrate
   ```

3. Revisa los registros que se transferirían, sin realizar cargas:

   ```sh
   php artisan cloudinary:migrate-local-images --dry-run
   ```

4. Transfiere los archivos por lotes:

   ```sh
   php artisan cloudinary:migrate-local-images --batch=100
   ```

   El comando puede ejecutarse de nuevo: omite registros que ya tienen `public_id`. Si un archivo no existe o Cloudinary falla, informa el registro y finaliza con error para facilitar la revisión y el reintento.

Las rutas originales se guardan en `plantas.imagen_ruta_local` y `evidencias_caso.ruta_local`. Los archivos locales no se borran; la aplicación continúa mostrando las rutas heredadas mientras un registro todavía no tenga `public_id`. No elimines los originales hasta comprobar manualmente las imágenes y respaldos.

La cantidad de plantas con imágenes que no se cargaron se puede revisar con:

```sql
SELECT id, nombre, imagen
FROM plantas
WHERE imagen_public_id IS NULL;
```

La cantidad de fotos de mantenimiento pendientes se puede revisar con:

```sql
SELECT id, caso_id, ruta
FROM evidencias_caso
WHERE tipo = 'foto' AND public_id IS NULL;
```
