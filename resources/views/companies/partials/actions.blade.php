<div class="d-flex justify-content-end gap-1">
    <a class="btn btn-sm btn-outline-primary js-open-company-modal"
       href="#"
       data-url="{{ route('companies.edit', $row->id) }}"
       title="Editar"><i class="ti ti-pencil"></i></a>
    <a class="btn btn-sm btn-outline-danger js-delete-company"
       href="#"
       data-url="{{ route('companies.destroy', $row->id) }}"
       data-name="{{ $row->name }}"
       title="Eliminar"><i class="ti ti-trash"></i></a>
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
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-light'
        },
        buttonsStyling: false,
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: `No se puede eliminar esta compañia porque tiene usuarios asociados.`,
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false,
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
});
</script>
