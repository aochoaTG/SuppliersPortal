# Portal de Proveedores

Aplicacion Laravel 12 para gestion de proveedores, requisiciones, cotizaciones, ordenes de compra, recepciones y control presupuestal.

## Stack

- PHP 8.2
- Laravel 12
- Livewire 3
- SQL Server en operacion
- SQLite en pruebas
- Vite para assets

## Comandos principales

```bash
composer install
npm install
php artisan migrate --seed
composer dev
composer test
```

## Estructura relevante

- `app/` logica de aplicacion
- `routes/` rutas web, API y scheduler
- `resources/views/` vistas Blade
- `database/` migraciones, factories y seeders
- `tests/` pruebas
- `docs/` documentacion del proyecto

## Documentacion vigente

La referencia oficial del proyecto esta en:

- [Indice de documentacion](docs/README.md)
- [Documento tecnico del portal](docs/documento-tecnico-portal-proveedores.md)

## Documentacion no vigente

Se conservaron algunos archivos historicos o de trabajo interno dentro de `docs/`, pero no deben usarse como fuente principal de verdad si contradicen al codigo o al documento tecnico actual.

## Notas operativas

- La aplicacion usa colas `database`.
- El scheduler visible del repositorio esta definido en `routes/console.php`.
- Los permisos se gestionan con Spatie Permission.
- La visibilidad de varias entidades depende de empresa y centro de costo asignados al usuario.
