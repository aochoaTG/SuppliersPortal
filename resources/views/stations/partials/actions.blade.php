<div class="d-flex justify-content-end gap-1">
    {{-- Ver --}}
    <a href="{{ route('stations.show', $s->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank" title="Ver"><i class="ti ti-eye"></i></a>

    {{-- Editar (abre modal por AJAX) --}}
    <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary js-open-station-modal"
        data-url="{{ route('stations.edit', $s->id) }}" title="Editar"><i class="ti ti-pencil"></i></a>

    {{-- Activar / Desactivar (POST) --}}
    <form method="POST" action="{{ route('stations.toggle-active', $s->id) }}" class="js-toggle-station-active d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-warning" title="{{ $s->is_active ? 'Desactivar' : 'Activar' }}">
            <i class="ti {{ $s->is_active ? 'ti-toggle-left' : 'ti-toggle-right' }}"></i>
        </button>
    </form>
</div>
