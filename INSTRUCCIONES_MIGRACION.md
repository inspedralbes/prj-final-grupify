# Instrucciones para ejecutar la migración

Para que la funcionalidad de asignación de formularios y seguimiento de respuestas funcione correctamente, es necesario ejecutar la nueva migración que crea la tabla `form_assignments`.

## Pasos para ejecutar la migración:

1. Abre una terminal y navega hasta la carpeta del backend:

```bash
cd /Users/lucasbenitez/Documents/TareasInstituto/2do-DAW/2DAW-PROYECTOS/Proyecto-Final/prj-final-grupify/backend
```

2. Ejecuta el comando de migración:

```bash
php artisan migrate
```

3. Si todo sale bien, verás un mensaje indicando que la migración `2025_05_14_000000_create_form_assignments_table` se ha ejecutado correctamente.

4. Si estás usando Docker, debes ejecutar el comando dentro del contenedor:

```bash
docker-compose exec app php artisan migrate
```

## Verificación:

Para verificar que la tabla se ha creado correctamente, puedes:

1. Acceder a la base de datos MySQL:
```bash
mysql -u tu_usuario -p
```

2. Seleccionar la base de datos y verificar la tabla:
```sql
USE gestioeduca;
SHOW TABLES LIKE 'form_assignments';
```

Deberías ver la tabla `form_assignments` en la lista.

## Nota importante:

La aplicación ha sido modificada para seguir funcionando incluso si la migración no se ha ejecutado aún, pero para tener todas las funcionalidades (como el seguimiento de respuestas y la visualización de quién ha respondido) es necesario que la tabla exista en la base de datos.
