<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;


class AnnouncementController extends Controller
{
    /* =========================================================================
     |  ADMIN: Listado
     * ========================================================================= */
    public function adminIndex()
    {
        // Los más recientes primero
        $announcements = Announcement::orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('announcements.admin.index', compact('announcements'));
    }

    /* =========================================================================
     |  ADMIN: Crear
     * ========================================================================= */
    public function create()
    {
        return view('announcements.admin.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => ['required','string','max:50'],
            'description'   => ['required','string','max:500'],
            'published_at'  => ['required','date'],
            'visible_until' => ['nullable','date','after_or_equal:published_at'],
            'is_active'     => ['required','boolean'],
            'cover'         => ['nullable','image','max:4096'], // 4MB
            'priority'      => ['required','integer','in:1,2,3,4'], // 1: Baja, 2: Normal, 3: Alta, 4: Urgente
        ]);

        $coverPath = null;
        if ($request->hasFile('cover')) {
            $coverPath = $request->file('cover')->store('announcements', 'public');
        }

        $announcement = Announcement::create([
            'title'         => $data['title'],
            'description'   => $data['description'],
            'cover_path'    => $coverPath,
            'published_at'  => $data['published_at'],
            'visible_until' => $data['visible_until'] ?? null,
            'is_active'     => (bool) $data['is_active'],
            'priority'      => $data['priority'],
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /* =========================================================================
     |  ADMIN: Editar
     * ========================================================================= */
    public function edit(Announcement $announcement)
    {
        return view('announcements.admin.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'         => ['required','string','max:50'],
            'description'   => ['required','string','max:500'],
            'published_at'  => ['required','date'],
            'visible_until' => ['nullable','date','after_or_equal:published_at'],
            'is_active'     => ['required','boolean'],
            'cover'         => ['nullable','image','max:4096'],
            'remove_cover'  => ['nullable','boolean'], // checkbox para quitar portada
            'priority'      => ['required','integer','in:1,2,3,4'], // 1: Baja, 2: Normal, 3: Alta, 4: Urgente
        ]);

        // Portada
        if (!empty($data['remove_cover']) && $announcement->cover_path) {
            Storage::disk('public')->delete($announcement->cover_path);
            $announcement->cover_path = null;
        }
        if ($request->hasFile('cover')) {
            // reemplaza la anterior
            if ($announcement->cover_path) {
                Storage::disk('public')->delete($announcement->cover_path);
            }
            $announcement->cover_path = $request->file('cover')->store('announcements', 'public');
        }

        $announcement->fill([
            'title'         => $data['title'],
            'description'   => $data['description'],
            'published_at'  => $data['published_at'],
            'visible_until' => $data['visible_until'] ?? null,
            'is_active'     => (bool) $data['is_active'],
            'priority'      => $data['priority'],
        ])->save();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /* =========================================================================
     |  ADMIN: Eliminar (Soft delete)
     * ========================================================================= */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    /* =========================================================================
     |  PROVEEDOR: Bandeja e interacción
     * ========================================================================= */

    // Lista para el proveedor de los comunicados listos y no descartados
    public function inbox()
    {

        $supplierId = $this->getSupplierIdFromAuth();
        if (!$supplierId) {
            abort(403, 'Cuenta de usuario no asociada a un proveedor');
        }

        $announcements = Announcement::query()
            ->readyToShow()
            ->forSupplier($supplierId) // filtra los "dismissed"
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('announcements.suppliers.inbox', compact('announcements'));
    }

    // Ver un comunicado (marca primera/última vista)
    public function show(Announcement $announcement)
    {
        $supplierId = $this->getSupplierIdFromAuth();
        if (!$supplierId) {
            abort(403, 'Cuenta de usuario no asociada a un proveedor');
        }

        // Solo deja ver si está listo para mostrarse
        abort_unless($announcement->should_display, 404);

        // Marca visto
        $announcement->markViewedBy($supplierId);

        return view('announcements.suppliers.show', compact('announcement'));
    }

    // Marcar visto vía POST/AJAX
    public function markViewed(Announcement $announcement)
    {
        $supplierId = $this->getSupplierIdFromAuth();
        if (!$supplierId) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if (!$announcement->should_display) {
            return response()->json(['message' => 'Comunicado no disponible'], 404);
        }

        $announcement->markViewedBy($supplierId);

        return response()->json(['message' => 'Vista registrada correctamente']);
    }

    // Descartar: "No mostrar más"
    public function dismiss(Announcement $announcement)
    {
        $supplierId = $this->getSupplierIdFromAuth();
        if (!$supplierId) {
            return back()->with('error', 'No autorizado.');
        }

        if (!$announcement->should_display) {
            return back()->with('error', 'Comunicado no disponible.');
        }

        $announcement->dismissFor($supplierId);

        return back()->with('success', 'Comunicado descartado correctamente.');
    }

    /* =========================================================================
     |  Helper para obtener supplier_id del usuario autenticado
     * ========================================================================= */
    protected function getSupplierIdFromAuth(): ?int
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        // suppliers.user_id → User hasOne Supplier
        $supplier = $user->supplier()->first();

        return $supplier?->id;
    }

    public function adminDatatable(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim($request->input('search.value', ''));
        $order  = $request->input('order.0', ['column' => 0, 'dir' => 'desc']);
        $estado = $request->input('estado');      // activo|inactivo
        $priori = $request->input('prioridad'); // baja|normal|alta|urgente

        // Mapeo de columnas (usa los ALIAS del subquery)
        $columns = [
            0 => 'id',
            1 => 'titulo',
            2 => 'contenido',
            3 => 'prioridad',
            4 => 'estado',              // alias
            5 => 'fecha_publicacion',
            6 => 'vistas',
            7 => 'acciones', // no ordenable
        ];
        $orderColumn = $columns[$order['column'] ?? 0] ?? 'id';
        $orderDir    = in_array(strtolower($order['dir'] ?? 'desc'), ['asc','desc']) ? $order['dir'] : 'desc';

        // Subquery base (ahora usando el campo priority del modelo)
        $base = Announcement::query()
            ->select([
                'announcements.id',
                'announcements.title as titulo',
                'announcements.description as contenido',
                'announcements.published_at as fecha_publicacion',
                'announcements.priority', // Usamos el campo directo del modelo
            ])
            ->addSelect(DB::raw("
                CASE
                    WHEN announcements.is_active = 1 THEN 'activo'
                    ELSE 'inactivo'
                END AS estado
            "))
            ->addSelect(DB::raw("
                CASE
                    WHEN announcements.priority = 1 THEN 'baja'
                    WHEN announcements.priority = 2 THEN 'normal'
                    WHEN announcements.priority = 3 THEN 'alta'
                    WHEN announcements.priority = 4 THEN 'urgente'
                    ELSE 'baja'
                END AS prioridad_label
            "))
            ->withCount(['views as vistas' => function ($q) {
                $q->whereNotNull('first_viewed_at');
            }]);

        // Totales sin filtros (SoftDeletes ya aplica el global scope)
        $recordsTotal = (clone $base)->count('announcements.id');

        // Envolver en subconsulta para poder filtrar por aliases (estado/prioridad)
        $wrapped = DB::query()->fromSub($base, 'x');

        // Búsqueda global
        if ($search !== '') {
            $wrapped->where(function ($q) use ($search) {
                $q->where('x.titulo', 'like', "%{$search}%")
                ->orWhere('x.contenido', 'like', "%{$search}%");
            });
        }

        // Filtro por estado (activo|inactivo)
        if ($estado && in_array($estado, ['activo','inactivo'], true)) {
            $wrapped->where('x.estado', $estado);
        }
        if ($priori && in_array($priori, ['baja','normal','alta','urgente'], true)) {
            $priorityMap = ['baja'=>1, 'normal'=>2, 'alta'=>3, 'urgente'=>4];
            $wrapped->where('x.priority', $priorityMap[$priori]);
        }

        // Conteo filtrado
        $recordsFiltered = (clone $wrapped)->count();

        // Ordenación (si eligen "acciones", forzamos por id)
        if ($orderColumn === 'acciones') {
            $orderColumn = 'id';
        }
        $wrapped->orderBy($orderColumn, $orderDir);

        // Paginación
        if ($length > 0) {
            $wrapped->skip($start)->take($length);
        }

        $rows = $wrapped->get();

        // Formateo de salida
        $data = $rows->map(function ($r) {
            $editUrl = route('admin.announcements.edit', $r->id);
            $delUrl  = route('admin.announcements.destroy', $r->id);
            $token   = csrf_token();

            $acciones = <<<HTML
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i> Acciones
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{$editUrl}" class="dropdown-item btn-edit-comunicado" data-edit-url="{$editUrl}">
                                <i class="ti ti-pencil me-1"></i> Editar
                            </a>
                        </li>
                        <li>
                            <form method="POST" action="{$delUrl}" class="form-eliminar-comunicado">
                                <input type="hidden" name="_token" value="{$token}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="button" class="dropdown-item text-danger btn-eliminar-comunicado">
                                    <i class="ti ti-trash me-1"></i> Eliminar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            HTML;

            return [
                'id'                 => (int) $r->id,
                'titulo'             => $r->titulo,
                'contenido'          => $r->contenido,
                'prioridad'          => $r->prioridad_label, // Usamos el label en lugar del valor numérico
                'estado'             => $r->estado,          // 'activo' | 'inactivo'
                'fecha_publicacion'  => $r->fecha_publicacion ? Carbon::parse($r->fecha_publicacion)->toIso8601String() : null,
                'vistas'             => (int) $r->vistas,
                'acciones'           => $acciones,
                'es_importante'      => $r->priority >= 3,  // Alta (3) o Urgente (4)
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    public function supplierDatatable(Request $request)
    {
        $supplierId = $this->getSupplierIdFromAuth();
        abort_if(!$supplierId, 403, 'Cuenta de usuario no asociada a un proveedor');

        // DataTables
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 25);
        $search = trim($request->input('search.value', ''));
        $order  = $request->input('order.0', ['column' => 0, 'dir' => 'desc']);

        // Filtros (opcionales). Por defecto: sólo vigentes y NO descartados.
        $status    = $request->input('status');     // "", "active", "expired", "upcoming"
        $dismissed = $request->input('dismissed');  // "", "dismissed", "not_dismissed"
        if ($dismissed === null || $dismissed === '') {
            $dismissed = 'not_dismissed';
        }

        $columns = [
            0 => 'id',
            1 => 'title',
            2 => 'description',
            3 => 'published_at',
            4 => 'visible_until',
            5 => 'views',
            6 => 'actions',
        ];
        $orderColumn = $columns[$order['column'] ?? 0] ?? 'id';
        $orderDir    = in_array(strtolower($order['dir'] ?? 'desc'), ['asc','desc']) ? $order['dir'] : 'desc';

        $now = now();

        // Query base + info por supplier
        $base = Announcement::query()
            ->select([
                'announcements.id',
                'announcements.title',
                'announcements.description',
                'announcements.published_at',
                'announcements.visible_until',
                'announcements.is_active',
                'announcements.cover_path',
                'announcements.priority',
            ])
            ->addSelect(DB::raw("
                CASE WHEN announcements.is_active = 1 THEN 'activo' ELSE 'inactivo' END AS estado
            "))
            ->addSelect(DB::raw("
                CASE WHEN asv.first_viewed_at IS NULL THEN 0 ELSE 1 END AS has_viewed
            "))
            ->addSelect(DB::raw("
                CASE
                    WHEN announcements.priority = 1 THEN 'baja'
                    WHEN announcements.priority = 2 THEN 'normal'
                    WHEN announcements.priority = 3 THEN 'alta'
                    WHEN announcements.priority = 4 THEN 'urgente'
                    ELSE 'baja'
                END AS prioridad_label
            "))
            ->addSelect(DB::raw("
                CASE WHEN asv.is_dismissed = 1 OR asv.dismissed_at IS NOT NULL THEN 1 ELSE 0 END AS is_dismissed
            "))
            ->withCount(['views as views' => function ($q) {
                $q->whereNotNull('first_viewed_at');
            }])
            ->leftJoin('announcement_supplier as asv', function ($join) use ($supplierId) {
                $join->on('asv.announcement_id', '=', 'announcements.id')
                    ->where('asv.supplier_id', '=', $supplierId);
            });

        // Envolver para poder filtrar por alias
        $wrapped = DB::query()->fromSub($base, 'x');

        // --- FILTRO POR DEFECTO: SOLO VIGENTES ---
        // is_active = 1, published_at <= now, (visible_until is null or >= now)
        $wrapped->where('x.is_active', 1)
                ->where('x.published_at', '<=', $now)
                ->where(function ($qq) use ($now) {
                    $qq->whereNull('x.visible_until')
                    ->orWhere('x.visible_until', '>=', $now);
                });

        // Busca en título/descr.
        if ($search !== '') {
            $wrapped->where(function ($q) use ($search) {
                $q->where('x.title', 'like', "%{$search}%")
                ->orWhere('x.description', 'like', "%{$search}%");
            });
        }

        // Filtro dismissed (por defecto = not_dismissed)
        if ($dismissed === 'dismissed') {
            $wrapped->where('x.is_dismissed', 1);
        } elseif ($dismissed === 'not_dismissed') {
            $wrapped->where(function ($q) {
                $q->where('x.is_dismissed', 0)
                ->orWhereNull('x.is_dismissed');
            });
        }

        // Si ADEMÁS quieres permitir ver "expired" o "upcoming" cuando el usuario lo pida explícitamente:
        if ($status === 'expired') {
            // Quita la condición de vigencia y aplica "expirados"
            $wrapped->whereNotNull('x.visible_until')
                    ->where('x.visible_until', '<', $now);
        } elseif ($status === 'upcoming') {
            $wrapped->where('x.published_at', '>', $now);
        }
        // Nota: si se selecciona "active" explícitamente, ya está cubierto por el filtro por defecto.

        // Totales (respecto a lo que sí mostramos por defecto)
        $recordsTotal = (clone $wrapped)->count();

        // Ordenar
        if ($orderColumn === 'actions') $orderColumn = 'id';
        $wrapped->orderBy($orderColumn, $orderDir);

        // Paginación
        if ($length > 0) $wrapped->skip($start)->take($length);

        $rows = $wrapped->get();

        // Salida
        $data = $rows->map(function ($r) {
            $showUrl    = route('supplier.announcements.show', $r->id);
            $pdfUrl     = route('supplier.announcements.pdf',  $r->id);
            $dismissUrl = route('supplier.announcements.dismiss', $r->id);

            $coverUrl = $r->cover_path ? asset('storage/'.$r->cover_path) : null;

            $actions = <<<HTML
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i> Acciones
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="{$showUrl}" class="dropdown-item" title="Abrir">
                                <i class="ti ti-external-link me-1"></i> Abrir
                            </a>
                        </li>
                        <li>
                            <a href="{$pdfUrl}" class="dropdown-item" target="_blank" title="Abrir en PDF">
                                <i class="ti ti-file-text me-1"></i> Abrir en PDF
                            </a>
                        </li>
                        <li>
                            <button class="dropdown-item text-danger js-dismiss" data-url="{$dismissUrl}" title="No mostrar más">
                                <i class="ti ti-eye-off me-1"></i> No mostrar más
                            </button>
                        </li>
                    </ul>
                </div>
            HTML;

            return [
                'id'            => (int) $r->id,
                'thumb_url'     => $coverUrl,      // 👈 nuevo campo
                'title'         => $r->title,
                'description'   => $r->description,
                'priority'      => $r->prioridad_label,
                'estado'        => $r->estado,
                'published_at'  => $r->published_at ? Carbon::parse($r->published_at)->toIso8601String() : null,
                'visible_until' => $r->visible_until ? Carbon::parse($r->visible_until)->toIso8601String() : null,
                'views'         => (int) ($r->views ?? 0),
                'actions'       => $actions,
                'cover_url'     => $coverUrl,
                'has_viewed'    => (bool) $r->has_viewed,
                'is_dismissed'  => (bool) $r->is_dismissed,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsTotal, // si quieres separar, recalcúlalo antes de paginar
            'data'            => $data,
        ]);
    }

    public function pdf(Announcement $announcement)
    {
        $logoPath = public_path('images/logos/logo_TotalGas_hor.png');
        // Marca visto si quieres (opcional, ya se marca en show())
        if ($supplierId = optional(auth()->user()->supplier)->id) {
            $announcement->markViewedBy($supplierId);
        }

        // Portada en Base64
        $coverBase64 = null;
        if ($announcement->cover_path && Storage::disk('public')->exists($announcement->cover_path)) {
            $mime = Storage::disk('public')->mimeType($announcement->cover_path) ?: 'image/jpeg';
            $data = Storage::disk('public')->get($announcement->cover_path);
            $coverBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
        }

        $faviconUrl = asset('images/favicon.png');    // 👈 generamos aquí la URL completa

        $pdf = \PDF::loadView('announcements.suppliers.pdf', [
            'announcement' => $announcement,
            'logoPath'    => $logoPath,
            'coverBase64'  => $coverBase64, // << aquí pasamos la portada
            'faviconUrl'  => $faviconUrl
        ])->setPaper('a4', 'portrait');

        $filename = 'announcement_'.$announcement->id.'.pdf';
        return $pdf->stream($filename); // o ->download($filename)
    }
}
