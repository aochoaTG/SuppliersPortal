{{--
    Componente: Tarjeta de Partida
    
    Props:
    - $item: RequisitionItem
--}}

<div class="item-card card mb-2" 
     data-item-id="{{ $item->id }}"
     draggable="true"
     style="cursor: grab;">
    <div class="card-body p-2">
        <div class="d-flex align-items-start">
            {{-- Checkbox de selección - Centrado ligeramente --}}
            <div class="form-check me-2 mt-1">
                <input class="form-check-input item-checkbox" 
                       type="checkbox" 
                       value="{{ $item->id }}"
                       id="item-{{ $item->id }}">
                <label class="form-check-label" for="item-{{ $item->id }}"></label>
            </div>

            {{-- Contenido principal --}}
            <div class="flex-grow-1">
                {{-- Header: Número, nombre y grip --}}
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="text-truncate">
                        <span class="badge bg-secondary px-1">#{{ $loop->iteration }}</span>
                        <span class="item-name fw-bold small">{{ $item->product->name ?? 'Producto sin nombre' }}</span>
                    </div>
                    <i class="ti ti-grip-vertical text-muted fs-5"></i>
                </div>

                {{-- Metadata y Categoría en una sola línea --}}
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="category-badge badge bg-light text-dark border py-0 px-1" style="font-size: 0.75rem;">
                        <i class="ti ti-tag me-1"></i>
                        {{ $item->product->category->name ?? 'Sin categoría' }}
                    </span>
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="ti ti-package me-1"></i>
                        <strong>{{ $item->quantity }}</strong> {{ $item->unit }}
                    </small>
                </div>

                {{-- Descripción (si existe) - Más apretada --}}
                @if($item->description)
                <div class="mb-1">
                    <p class="text-muted mb-0 lh-sm" style="font-size: 0.7rem;">
                        <i class="ti ti-file-text me-1"></i>{{ Str::limit($item->description, 60) }}
                    </p>
                </div>
                @endif

                {{-- Estado de agrupación --}}
                <div>
                    <span class="badge badge-warning-subtle py-0" style="font-size: 0.7rem;">
                        <i class="ti ti-alert-circle me-1"></i>Sin agrupar
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>