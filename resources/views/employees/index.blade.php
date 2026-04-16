@extends('layouts.zircos')

@section('title', 'Empleados')
@section('page.title', 'Empleados')
@section('page.breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Empleados</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-id-badge me-1"></i> Catálogo de Empleados</h5>
            <small class="text-muted">Sincronizado diariamente desde TRESS</small>
        </div>

        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table id="employeesTable" class="table table-bordered table-hover w-100">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>No. Empleado</th>
                            <th>Nombre Completo</th>
                            <th>Empresa</th>
                            <th>Departamento</th>
                            <th>Puesto</th>
                            <th>Líder</th>
                            <th class="text-center" style="width: 80px;">Activo</th>
                            <th class="text-end" style="width: 80px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal: Promover a usuario staff --}}
    <div class="modal fade" id="promoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="promoteModalContent">
                <div class="modal-body text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const employeesTable = $('#employeesTable').DataTable({
                responsive: false,
                processing: true,
                serverSide: true,
                dom: '<"top"Bf>rt<"bottom"lip>',
                pageLength: 50,
                order: [[1, 'asc']],
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="ti ti-file-spreadsheet me-1"></i> Excel',
                        className: 'btn btn-success btn-sm'
                    },
                    {
                        extend: 'copy',
                        text: '<i class="ti ti-copy me-1"></i> Copiar',
                        className: 'btn btn-warning btn-sm'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="ti ti-file-text me-1"></i> PDF',
                        className: 'btn btn-info btn-sm',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ],
                ajax: {
                    url: "{{ route('employees.datatable') }}",
                    type: "GET",
                    error: function (xhr) {
                        console.error('Error en DataTable:', xhr.responseText);
                    }
                },
                columns: [
                    { data: 'id',              name: 'id',              width: '60px' },
                    { data: 'employee_number', name: 'employee_number' },
                    { data: 'full_name',       name: 'full_name',       searchable: false, orderable: false },
                    { data: 'company',         name: 'company' },
                    { data: 'department',      name: 'department' },
                    { data: 'job_title',       name: 'job_title' },
                    { data: 'leader',          name: 'leader' },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-end'
                    }
                ],
                language: {
                    url: "{{ asset('assets/vendor/datatables.net/es-MX.json') }}"
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });

            // ── Promote Employee to User ──────────────────────────────────────
            const promoteModal = new bootstrap.Modal(document.getElementById('promoteModal'));

            $(document).on('click', '.js-promote-btn', function () {
                const employeeId = $(this).data('id');
                const url = `/employees/${employeeId}/promote-form`;

                $('#promoteModalContent').html(
                    '<div class="modal-body text-center py-5"><div class="spinner-border text-primary" role="status"></div></div>'
                );
                promoteModal.show();

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => {
                    if (res.status === 409) {
                        promoteModal.hide();
                        return res.json().then(data => {
                            Swal.fire({ icon: 'warning', title: 'Atención', text: data.error });
                        });
                    }
                    return res.text().then(html => {
                        $('#promoteModalContent').html(html);
                    });
                })
                .catch(() => {
                    promoteModal.hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo cargar el formulario.' });
                });
            });

            $(document).on('submit', '#promoteForm', function (e) {
                e.preventDefault();

                const form    = this;
                const url     = form.action;
                const formData = new FormData(form);
                const submitBtn = document.getElementById('promoteSubmitBtn');

                // Limpiar errores previos
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
                document.getElementById('promoteGeneralError')?.classList.add('d-none');

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Creando...';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                })
                .then(res => res.json().then(data => ({ status: res.status, data })))
                .then(({ status, data }) => {
                    if (status === 200 && data.success) {
                        promoteModal.hide();
                        employeesTable.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: '¡Listo!', text: data.message, timer: 3000, showConfirmButton: false });
                    } else if (status === 422 && data.errors) {
                        Object.entries(data.errors).forEach(([field, messages]) => {
                            const input = form.querySelector(`[name="${field}"]`);
                            const errEl = document.getElementById(`err-${field}`);
                            if (input) input.classList.add('is-invalid');
                            if (errEl) errEl.textContent = messages[0];
                        });
                    } else {
                        const errEl = document.getElementById('promoteGeneralError');
                        if (errEl) { errEl.textContent = data.error || 'Ocurrió un error.'; errEl.classList.remove('d-none'); }
                    }
                })
                .catch(() => {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo completar la operación.' });
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="ti ti-user-plus me-1"></i>Crear usuario';
                });
            });
        });
    </script>
@endpush
