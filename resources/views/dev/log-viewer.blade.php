@extends('layouts.zircos')

@section('title', 'Log Viewer')
@section('page.title', 'Log Viewer')

@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Log Viewer</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="ti ti-bug me-1 text-danger"></i>
                    Log del sistema
                    <span class="badge bg-secondary ms-2 fs-11">{{ $fileSize }}</span>
                </h5>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-copy" title="Copiar contenido">
                        <i class="ti ti-copy me-1"></i>Copiar
                    </button>
                    <form action="{{ route('dev.log.clear') }}" method="POST" id="form-clear-log">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-sm btn-danger" id="btn-clear-log">
                            <i class="ti ti-trash me-1"></i>Vaciar log
                        </button>
                    </form>
                </div>
            </div>

            <div class="card-body p-0">
                @if(session('success'))
                    {{-- Se muestra via Swal en el script --}}
                @endif

                @if(empty(trim($lines ?? '')))
                    <div class="text-center text-muted py-5">
                        <i class="ti ti-file-off fs-48 d-block mb-2"></i>
                        El archivo de log está vacío.
                    </div>
                @else
                    <pre id="log-content"
                         style="background:#1e1e1e; color:#d4d4d4; font-size:12px; line-height:1.5;
                                margin:0; padding:1rem; max-height:75vh; overflow-y:auto;
                                white-space:pre-wrap; word-break:break-all;">{{ $lines }}</pre>
                @endif
            </div>

            <div class="card-footer text-muted small">
                Mostrando las últimas 300 líneas de <code>storage/logs/laravel.log</code>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const pre      = document.getElementById('log-content');
    const btnCopy  = document.getElementById('btn-copy');
    const btnClear = document.getElementById('btn-clear-log');

    // Auto-scroll al final
    if (pre) pre.scrollTop = pre.scrollHeight;

    // Flash de éxito tras vaciar
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Log vaciado',
            text: '{{ session('success') }}',
            confirmButtonColor: '#28a745',
            timer: 3000,
            timerProgressBar: true,
        });
    @endif

    // Confirmar vaciado del log
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            Swal.fire({
                title: '¿Vaciar el log?',
                html: 'Se eliminará todo el contenido de <code>laravel.log</code>.<br>Esta acción <strong>no se puede deshacer</strong>.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="ti ti-trash me-1"></i>Sí, vaciar',
                cancelButtonText: 'Cancelar',
            }).then(result => {
                if (result.isConfirmed) {
                    document.getElementById('form-clear-log').submit();
                }
            });
        });
    }

    // Copiar contenido
    if (btnCopy) {
        btnCopy.addEventListener('click', function () {
            const text = pre ? pre.innerText : '';
            navigator.clipboard.writeText(text).then(() => {
                btnCopy.innerHTML = '<i class="ti ti-check me-1"></i>Copiado';
                btnCopy.classList.replace('btn-outline-secondary', 'btn-outline-success');
                setTimeout(() => {
                    btnCopy.innerHTML = '<i class="ti ti-copy me-1"></i>Copiar';
                    btnCopy.classList.replace('btn-outline-success', 'btn-outline-secondary');
                }, 2000);
            }).catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al copiar',
                    text: 'No se pudo acceder al portapapeles. Intenta seleccionar el texto manualmente.',
                    confirmButtonColor: '#dc3545',
                });
            });
        });
    }
</script>
@endpush
