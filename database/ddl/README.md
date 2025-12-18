# DDL

Este directorio aloja el DDL provisto por el equipo contable. No se versiona ningún esquema aquí: copiá el archivo entregado (por ejemplo `schema.sql`) y aplicalo contra la base PostgreSQL usando los datos de conexión definidos en `.env`.

Ejemplo de ejecución atómica:

```
psql "host=$DB_HOST port=$DB_PORT dbname=$DB_DATABASE user=$DB_USERNAME password=$DB_PASSWORD" \
  --set ON_ERROR_STOP=1 \
  --single-transaction \
  --file=database/ddl/schema.sql
```

Asegurate de que el DDL incluya tablas, claves foráneas, triggers y auditoría según la fuente de verdad antes de avanzar de fase.
