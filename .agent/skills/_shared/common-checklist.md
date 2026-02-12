# Lista de Verificación Común - TotalGas

## ✅ Código PHP/Laravel

### Seguridad
- [ ] Validación de entrada con Form Requests
- [ ] Protección CSRF en formularios
- [ ] Sanitización de datos antes de consultas SQL
- [ ] Uso de Eloquent/Query Builder (nunca SQL raw sin bindings)
- [ ] Verificación de permisos en controllers
- [ ] Prevención de SQL Injection en consultas SQL Server

### Calidad
- [ ] Seguir PSR-12 coding standards
- [ ] Nombres de variables/métodos descriptivos en inglés
- [ ] Comentarios en español solo cuando sea necesario
- [ ] Manejo de errores con try-catch
- [ ] Logging de operaciones críticas
- [ ] Transacciones DB para operaciones múltiples

### Performance
- [ ] Eager loading para evitar N+1 queries
- [ ] Índices en columnas de búsqueda frecuente
- [ ] Cache para queries pesadas
- [ ] Paginación en listados grandes

## ✅ Frontend (Blade + Bootstrap)

### UI/UX
- [ ] Responsive design (Bootstrap grid)
- [ ] Iconos de Tabler Icons consistentes
- [ ] Mensajes de éxito/error con SweetAlert2
- [ ] Loading states en botones/formularios
- [ ] Validación client-side con jQuery Validation

### DataTables
- [ ] Server-side processing para > 1000 registros
- [ ] Búsqueda y filtros funcionales
- [ ] Exportación configurada (Excel, PDF)
- [ ] Columnas ordenables donde tenga sentido
- [ ] Responsive o scroll horizontal en móvil

### JavaScript
- [ ] No hay errores en consola
- [ ] Eventos delegados para contenido dinámico
- [ ] AJAX con manejo de errores 401/403/500
- [ ] Token CSRF en headers de AJAX

## ✅ Base de Datos (SQL Server)

### Migraciones
- [ ] Foreign keys con ON DELETE/UPDATE apropiado
- [ ] Índices en columnas de búsqueda/joins
- [ ] Defaults values cuando corresponda
- [ ] Nombres en snake_case
- [ ] Timestamps (created_at, updated_at)

### Consultas
- [ ] Uso de transacciones para operaciones críticas
- [ ] Optimización de JOINs (evitar subconsultas innecesarias)
- [ ] Paginación en lugar de ->get() sin límite
- [ ] whereHas() en lugar de joins cuando sea posible

## ✅ Rutas y Controllers

### Rutas
- [ ] Agrupadas con prefijos lógicos
- [ ] Middleware de autenticación aplicado
- [ ] Nombres de ruta consistentes (resource naming)

### Controllers
- [ ] Single Responsibility (un controller, una entidad)
- [ ] Lógica de negocio en Services, no en controllers
- [ ] Return consistente: redirect()->with() o response()->json()
- [ ] Autorización con Gates/Policies cuando sea complejo

## ✅ Archivos y Convenciones

### Blade Templates
- [ ] Extends de layout correcto (Zircos)
- [ ] Secciones @section definidas
- [ ] CSRF token en formularios
- [ ] Sin lógica compleja en vistas (mover a Controller/Service)
- [ ] Uso de @include para componentes reutilizables

### Archivos estáticos
- [ ] Assets compilados con Vite
- [ ] Archivos en public/ accesibles
- [ ] Imágenes optimizadas

## ✅ Testing (Opcional pero recomendado)

- [ ] Feature tests para flujos críticos
- [ ] Validación de Form Requests testeada
- [ ] Casos edge probados manualmente