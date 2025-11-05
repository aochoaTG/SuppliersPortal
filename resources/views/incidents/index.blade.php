@extends('layouts.zircos')


{{-- TÍTULO DE LA PÁGINA       --}}
@section('title', 'Lista de Incidencias Reportadas')

{{-- CSS ADICIONAL (opcional)  --}}
@push('styles')
{{-- Ejemplo: <link rel="stylesheet" href="{{ asset('css/custom.css') }}"> --}}
@endpush

@section('page.title', 'Lista de Incidencias Reportadas')
@section('page.breadcrumbs')
<li class="breadcrumb-item"><a href="javascript:void(0);">Configuración</a></li>
<li class="breadcrumb-item"><a href="javascript:void(0);">Incidentes reportados</a></li>
<li class="breadcrumb-item active">Usuarios</li>
@endsection
{{-- CONTENIDO PRINCIPAL       --}}
@section('content')
<div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            {{-- Título del listado --}}
            <h5 class="mb-0">Usuarios</h5>
            
        </div>
        
        <div class="card-body">
            {{-- Aquí va tu tabla o listado --}}
            <table class="table table-sm table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Módulo</th>
                        <th>Severidad</th>
                        <th>Título</th>
                        <th>Pasos a reproducir</th>
                        <th>Res. Esperado</th>
                        <th>Res. Actual</th>
                        <th>Evidencia</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Ejemplo de fila --}}
                    @foreach($incidents as $incident)
                    @php
                        $evidenceUrl = $incident->image_path ? asset('storage/'.$incident->image_path)
                            : null;
                    @endphp
                    <tr>
                        <td>{{ $incident->id }}</td>
                        <td>{{ $incident->reporter_name }}</td>
                        <td>{{ $incident->reporter_email }}</td>
                        <td>{{ $incident->module }}</td>
                        <td>{{ $incident->severity }}</td>
                        <td>{{ $incident->title }}</td>
                        <td>{{ $incident->steps }}</td>
                        <td>{{ $incident->expected }}</td>
                        <td>{{ $incident->actual }}</td>
                        <td>
                            @if ($evidenceUrl)
                                <a href="#"
                                data-bs-toggle="modal"
                                data-bs-target="#evidenceModal"
                                data-evidence="{{ $evidenceUrl }}">
                                    Ver evidencia
                                </a>
                            @else
                                <span class="text-muted">Sin archivo</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('incidents.destroy', $incident) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal para ver evidencia -->
    <div class="modal fade" id="evidenceModal" tabindex="-1" aria-labelledby="evidenceModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="evidenceModalLabel">Evidencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body text-center">
                    <!-- Contenido dinámico: imagen o video -->
                </div>
            </div>
        </div>
    </div>

@endsection

{{-- JS ADICIONAL (opcional)   --}}
@push('scripts')
    <script>
        document.getElementById('evidenceModal').addEventListener('shown.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const evidenceUrl = trigger?.getAttribute('data-evidence');
            const modalBody = this.querySelector('.modal-body');
            modalBody.innerHTML = '';

            if (!evidenceUrl) {
                modalBody.innerHTML = '<p class="text-muted">No hay evidencia disponible</p>';
                return;
            }

            const ext = evidenceUrl.split('.').pop().toLowerCase();
            const imageExtensions = ['jpg','jpeg','png','gif','webp'];
            const videoExtensions = ['mp4','mov','mkv'];

            if (imageExtensions.includes(ext)) {
                modalBody.innerHTML = `<img src="${evidenceUrl}" class="img-fluid rounded" alt="Evidencia">`;
            } else if (videoExtensions.includes(ext)) {
                modalBody.innerHTML = `
                    <video class="img-fluid rounded" controls>
                        <source src="${evidenceUrl}" type="video/${ext}">
                        Tu navegador no soporta reproducción de video.
                    </video>`;
            } else {
                modalBody.innerHTML = `<a href="${evidenceUrl}" target="_blank">Abrir archivo</a>`;
            }
        });

    </script>
@endpush