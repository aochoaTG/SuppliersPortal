<div class="dropdown">
    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="ti ti-dots-vertical"></i> Acciones
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item js-open-company-modal"
               href="#"
               data-url="{{ route('companies.edit', $row->id) }}">
                <i class="ti ti-pencil me-1 text-primary"></i> Editar
            </a>
        </li>
        <li>
            <a class="dropdown-item js-delete-company"
               href="#"
               data-url="{{ route('companies.destroy', $row->id) }}"
               data-name="{{ $row->name }}">
                <i class="ti ti-trash me-1 text-danger"></i> Eliminar
            </a>
        </li>
    </ul>
</div>

<script>
$(document).off('click', '.js-delete-company').on('click', '.js-delete-company', function (e) {
    e.preventDefault();
    const url = $(this).data('url');
    const name = $(this).data('name') || 'esta empresa';

    Swal.fire({
        title: `¿Eliminar ${name}?`,
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Aqui le vamos a decir al usuario que esta accion no se puede realizar. SWAL
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `No se puede eliminar esta compañia porque tiene usuarios asociados.`,
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
});
</script>
