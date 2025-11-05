<div class="btn-group">
    <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i> Acciones
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        {{-- Ver --}}
        <li>
            <a href="{{ route('stations.show', $s->id) }}" class="dropdown-item" target="_blank">
                <i class="ti ti-eye me-1"></i> Ver
            </a>
        </li>

        {{-- Editar (abre modal por AJAX) --}}
        <li>
            <a href="javascript:void(0);" class="dropdown-item js-open-station-modal"
                data-url="{{ route('stations.edit', $s->id) }}">
                <i class="ti ti-edit me-1"></i> Editar
            </a>
        </li>

        {{-- Activar / Desactivar (POST) --}}
        <li>
            <form method="POST" action="{{ route('stations.toggle-active', $s->id) }}"
                class="js-toggle-station-active">
                @csrf
                <button type="submit" class="dropdown-item">
                    <i class="ti {{ $s->is_active ? 'ti-toggle-left' : 'ti-toggle-right' }} me-1"></i>
                    {{ $s->is_active ? 'Desactivar' : 'Activar' }}
                </button>
            </form>
        </li>
    </ul>
</div>
