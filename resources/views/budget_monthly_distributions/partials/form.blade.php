{{--
    FORM PARCIAL: Matriz de Distribuciones Mensuales
    Usado tanto en create como en edit
    
    Variables requeridas:
    - $annualBudget: AnnualBudget
    - $allCategories: Collection de TODAS las ExpenseCategory disponibles
    - $selectedCategories: Collection de categorías YA seleccionadas (solo en EDIT)
    - $distributions: Array [category_id][month] => amount o data
    - $isEdit: boolean (true para edit, false para create)
--}}

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="ti ti-table me-2"></i>
            Matriz de Distribución Mensual por Categoría
        </h5>
    </div>
    <div class="card-body">
        {{-- Información del Presupuesto --}}
        <div class="alert alert-info mb-4">
            <div class="row">
                <div class="col-md-6">
                    <strong>Empresa:</strong> {{ $annualBudget->costCenter?->company?->name ?? '—' }}<br>
                    <strong>Centro de Costo:</strong> [{{ $annualBudget->costCenter?->code ?? '—' }}] {{ $annualBudget->costCenter?->name ?? '—' }}
                </div>
                <div class="col-md-6">
                    <strong>Ejercicio Fiscal:</strong> {{ $annualBudget->fiscal_year }}<br>
                    <strong>Monto Total Anual:</strong> <span class="badge bg-success">${{ number_format($annualBudget->total_annual_amount, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Selector de Categorías --}}
        <div class="card mb-4 border-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="ti ti-category me-2"></i>
                    Paso 1: Seleccione las Categorías de Gasto
                </h6>
                <button type="button" class="btn btn-primary btn-sm" id="btnAddCategory" title="Agregar nueva categoría">
                    <i class="ti ti-plus me-1"></i> Nueva Categoría
                </button>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="categorySelector" class="form-label">
                        Categorías de Gasto <span class="text-danger">*</span>
                    </label>
                    <select 
                        id="categorySelector" 
                        class="form-select" 
                        multiple="multiple" 
                        style="width: 100%;">
                        @foreach ($allCategories as $category)
                            <option 
                                value="{{ $category->id }}" 
                                data-code="{{ $category->code }}"
                                data-name="{{ $category->name }}"
                                @if($isEdit && $selectedCategories->contains('id', $category->id))
                                    selected
                                @endif>
                                [{{ $category->code }}] {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Seleccione una o más categorías para este presupuesto</small>
                </div>
            </div>
        </div>
        
        {{-- Botones de Acción --}}
        <div class="d-none gap-2 mb-4" id="distributionButtons">
            <button type="button" class="btn btn-outline-primary" id="btnDistributeUniform">
                <i class="ti ti-equal me-1"></i> Distribución Uniforme
            </button>
            <button type="button" class="btn btn-outline-secondary" id="btnClearAll">
                <i class="ti ti-eraser me-1"></i> Limpiar Todo
            </button>
            <button type="button" class="btn btn-outline-info" id="btnCalculateTotals">
                <i class="ti ti-calculator me-1"></i> Recalcular Totales
            </button>
        </div>

        {{-- Tabla Matriz (se genera dinámicamente) --}}
        <div id="matrixContainer" style="display: none;">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm" id="distributionMatrix">
                    <thead class="table-light">
                        <tr>
                            <th class="sticky-column" style="min-width: 200px;">Categoría</th>
                            @for ($month = 1; $month <= 12; $month++)
                                <th class="text-center text-nowrap">{{ \App\Models\BudgetMonthlyDistribution::make(['month' => $month])->month_label }}</th>
                            @endfor
                            <th class="text-center bg-secondary text-white">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody id="matrixBody">
                        {{-- Se genera con JavaScript --}}
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <th>TOTAL POR MES</th>
                            @for ($month = 1; $month <= 12; $month++)
                                <th class="text-center">
                                    <strong class="month-total" data-month="{{ $month }}">$0.00</strong>
                                </th>
                            @endfor
                            <th class="text-center bg-dark text-white">
                                <strong id="grandTotal">$0.00</strong>
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Alerta de Validación --}}
            <div id="validationAlert" class="alert alert-warning mt-3 d-none">
                <i class="ti ti-alert-triangle me-2"></i>
                <strong>Advertencia:</strong> La suma de las distribuciones mensuales debe coincidir con el monto total anual.
                <div class="mt-2">
                    <strong>Monto Total Anual:</strong> ${{ number_format($annualBudget->total_annual_amount, 2) }}<br>
                    <strong>Suma de Distribuciones:</strong> <span id="sumDistributions">$0.00</span><br>
                    <strong>Diferencia:</strong> <span id="difference" class="text-danger">$0.00</span>
                </div>
            </div>
        </div>

        {{-- Mensaje cuando no hay categorías seleccionadas --}}
        <div id="noCategoriesMessage" class="alert alert-secondary text-center">
            <i class="ti ti-info-circle me-2"></i>
            Seleccione al menos una categoría para comenzar a distribuir el presupuesto.
        </div>
    </div>
</div>
{{-- Scripts --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Script de Distribución Mensual Cargado ===');
    
    // ========================================
    // VARIABLES GLOBALES
    // ========================================
    const budgetTotalAmount = {{ $annualBudget->total_annual_amount }};
    const isEdit = {{ $isEdit ? 'true' : 'false' }};
    const existingDistributions = @json($distributions);

    // Nombres de meses
    const monthLabels = @json(array_map(function($m) {
        return App\Models\BudgetMonthlyDistribution::make(['month' => $m])->month_label;
    }, range(1, 12)));

    let globalIndex = 0;

    // Elementos del DOM
    const matrixBody = document.getElementById('matrixBody');
    const matrixContainer = document.getElementById('matrixContainer');
    const noCategoriesMessage = document.getElementById('noCategoriesMessage');
    const distributionButtons = document.getElementById('distributionButtons');

    // ========================================
    // INICIALIZAR SELECT2
    // ========================================
    console.log('Inicializando Select2...');
    
    const $categorySelector = $('#categorySelector');
    
    if ($categorySelector.length === 0) {
        console.error('ERROR: No se encontró el elemento #categorySelector');
        return;
    }
    
    $categorySelector.select2({
        theme: 'bootstrap-5',
        placeholder: 'Seleccione categorías...',
        allowClear: true,
        closeOnSelect: false
    });

    console.log('Select2 inicializado correctamente');

    // ========================================
    // FUNCIONES PRINCIPALES
    // ========================================

    /**
     * Generar fila de categoría en la matriz
     */
    function generateCategoryRow(categoryId, categoryCode, categoryName) {
        let html = `<tr data-category-id="${categoryId}">`;

        // Columna: Nombre de Categoría
        html += `
            <td class="sticky-column bg-light">
                <strong>[${categoryCode}]</strong><br>
                <small class="text-muted">${categoryName}</small>
            </td>
        `;

        // Columnas: 12 Meses
        for (let month = 1; month <= 12; month++) {
            let distData = null;
            let distributionId = null;
            let assignedAmount = 0;
            let consumedAmount = 0;
            let committedAmount = 0;
            let isLocked = false;

            // Buscar datos existentes
            if (isEdit && existingDistributions[categoryId] && existingDistributions[categoryId][month]) {
                distData = existingDistributions[categoryId][month];
                distributionId = distData.id;
                assignedAmount = distData.assigned_amount;
                consumedAmount = distData.consumed_amount;
                committedAmount = distData.committed_amount;
                isLocked = (consumedAmount > 0 || committedAmount > 0);
            }

            html += `<td class="text-center p-1">`;

            if (isEdit && distributionId) {
                // Modo EDIT: enviar ID de distribución
                html += `
                    <input type="hidden" name="distributions[${globalIndex}][id]" value="${distributionId}">
                    <input
                        type="number"
                        name="distributions[${globalIndex}][assigned_amount]"
                        class="form-control form-control-sm text-end distribution-input"
                        data-category="${categoryId}"
                        data-month="${month}"
                        value="${assignedAmount.toFixed(2)}"
                        step="0.01"
                        min="${(consumedAmount + committedAmount).toFixed(2)}"
                        ${isLocked ? `title="Este mes tiene consumo o compromisos. Mínimo: $${(consumedAmount + committedAmount).toFixed(2)}"` : ''}
                        required>
                `;

                if (isLocked) {
                    html += `
                        <small class="text-warning d-block">
                            <i class="ti ti-lock"></i>
                            Min: $${(consumedAmount + committedAmount).toFixed(2)}
                        </small>
                    `;
                }

                globalIndex++;
            } else {
                // Modo CREATE: solo monto
                html += `
                    <input
                        type="number"
                        name="distributions[${categoryId}][${month}]"
                        class="form-control form-control-sm text-end distribution-input"
                        data-category="${categoryId}"
                        data-month="${month}"
                        value="0.00"
                        step="0.01"
                        min="0"
                        required>
                `;
            }

            html += `</td>`;
        }

        // Columna: TOTAL por Categoría
        html += `
            <td class="text-center bg-light">
                <strong class="category-total" data-category="${categoryId}">$0.00</strong>
            </td>
        `;

        html += `</tr>`;
        return html;
    }

    /**
     * Actualizar matriz según categorías seleccionadas
     */
    function updateMatrix() {
        console.log('updateMatrix() ejecutándose...');
        
        const selectedCategories = $categorySelector.select2('data');
        console.log('Categorías seleccionadas:', selectedCategories.length);

        // Limpiar
        matrixBody.innerHTML = '';
        globalIndex = 0;

        if (selectedCategories.length === 0) {
            matrixContainer.style.display = 'none';
            noCategoriesMessage.style.display = 'block';
            distributionButtons.classList.add('d-none');
            distributionButtons.classList.remove('d-flex');
            return;
        }

        // Mostrar matriz
        matrixContainer.style.display = 'block';
        noCategoriesMessage.style.display = 'none';
        distributionButtons.classList.remove('d-none');
        distributionButtons.classList.add('d-flex');

        // Generar filas
        selectedCategories.forEach(cat => {
            const row = generateCategoryRow(
                cat.id,
                cat.element.dataset.code,
                cat.element.dataset.name
            );
            matrixBody.insertAdjacentHTML('beforeend', row);
        });

        // Re-asignar eventos
        attachInputEvents();
        calculateTotals();
        
        console.log('Matriz actualizada con', selectedCategories.length, 'categorías');
    }

    /**
     * Asignar eventos a inputs de distribución
     */
    function attachInputEvents() {
        document.querySelectorAll('.distribution-input').forEach(input => {
            input.removeEventListener('input', calculateTotals);
            input.removeEventListener('change', calculateTotals);
            input.addEventListener('input', calculateTotals);
            input.addEventListener('change', calculateTotals);
        });
    }

    /**
     * Calcular todos los totales (categorías, meses, gran total)
     */
    function calculateTotals() {
        let grandTotal = 0;

        // Totales por categoría
        document.querySelectorAll('[data-category-id]').forEach(row => {
            const categoryId = row.dataset.categoryId;
            let categorySum = 0;

            row.querySelectorAll('.distribution-input').forEach(input => {
                const value = parseFloat(input.value) || 0;
                categorySum += value;
            });

            const totalCell = row.querySelector(`.category-total[data-category="${categoryId}"]`);
            if (totalCell) {
                totalCell.textContent = '$' + categorySum.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }

            grandTotal += categorySum;
        });

        // Totales por mes
        for (let month = 1; month <= 12; month++) {
            let monthSum = 0;

            document.querySelectorAll(`.distribution-input[data-month="${month}"]`).forEach(input => {
                const value = parseFloat(input.value) || 0;
                monthSum += value;
            });

            const monthTotalCell = document.querySelector(`.month-total[data-month="${month}"]`);
            if (monthTotalCell) {
                monthTotalCell.textContent = '$' + monthSum.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
        }

        // Gran Total
        const grandTotalElement = document.getElementById('grandTotal');
        if (grandTotalElement) {
            grandTotalElement.textContent = '$' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Validación
        const difference = Math.abs(grandTotal - budgetTotalAmount);
        const validationAlert = document.getElementById('validationAlert');

        const sumDistributionsElement = document.getElementById('sumDistributions');
        const differenceElement = document.getElementById('difference');

        if (sumDistributionsElement) {
            sumDistributionsElement.textContent = '$' + grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        if (differenceElement) {
            differenceElement.textContent = '$' + difference.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        if (validationAlert) {
            if (difference > 0.01) {
                validationAlert.classList.remove('d-none');
            } else {
                validationAlert.classList.add('d-none');
            }
        }
    }

    // ========================================
    // EVENTOS SELECT2
    // ========================================
    $categorySelector.on('change', function() {
        console.log('Select2: categorías cambiadas');
        updateMatrix();
    });

    // ========================================
    // BOTONES DE DISTRIBUCIÓN
    // ========================================

    /**
     * Distribución Uniforme
     */
    const btnDistributeUniform = document.getElementById('btnDistributeUniform');
    if (btnDistributeUniform) {
        btnDistributeUniform.addEventListener('click', function() {
            const selectedCategories = $categorySelector.select2('data');
            const totalCategories = selectedCategories.length;

            if (totalCategories === 0) {
                Swal.fire({
                    title: 'Sin Categorías',
                    text: 'Debe seleccionar al menos una categoría primero',
                    icon: 'warning',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            const amountPerCategory = budgetTotalAmount / totalCategories;
            const amountPerMonth = amountPerCategory / 12;

            Swal.fire({
                title: '¿Distribución Uniforme?',
                html: `¿Desea distribuir uniformemente <strong>$${budgetTotalAmount.toFixed(2)}</strong> entre ${totalCategories} categorías y 12 meses?<br><br>Cada mes por categoría recibirá: <strong>$${amountPerMonth.toFixed(2)}</strong>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, distribuir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelectorAll('.distribution-input').forEach(input => {
                        const minValue = parseFloat(input.getAttribute('min')) || 0;
                        if (minValue === 0) {
                            input.value = amountPerMonth.toFixed(2);
                        }
                    });

                    calculateTotals();

                    Swal.fire({
                        title: '¡Distribuido!',
                        text: 'Los montos se han distribuido uniformemente',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
    }

    /**
     * Limpiar Todo
     */
    const btnClearAll = document.getElementById('btnClearAll');
    if (btnClearAll) {
        btnClearAll.addEventListener('click', function() {
            Swal.fire({
                title: '¿Limpiar Distribuciones?',
                text: '¿Está seguro de limpiar todas las distribuciones?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, limpiar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.querySelectorAll('.distribution-input').forEach(input => {
                        const minValue = parseFloat(input.getAttribute('min')) || 0;
                        if (minValue === 0) {
                            input.value = '0.00';
                        }
                    });

                    calculateTotals();

                    Swal.fire({
                        title: '¡Limpiado!',
                        text: 'Todas las distribuciones han sido limpiadas',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        });
    }

    /**
     * Recalcular Totales
     */
    const btnCalculateTotals = document.getElementById('btnCalculateTotals');
    if (btnCalculateTotals) {
        btnCalculateTotals.addEventListener('click', function() {
            calculateTotals();
            Swal.fire({
                title: '¡Recalculado!',
                text: 'Los totales han sido actualizados',
                icon: 'info',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    // ========================================
    // VALIDACIÓN ANTES DE SUBMIT
    // ========================================
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const selectedCategories = $categorySelector.select2('data');

            if (selectedCategories.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Sin Categorías',
                    text: 'Debe seleccionar al menos una categoría para el presupuesto',
                    icon: 'error',
                    confirmButtonText: 'Entendido'
                });
                return false;
            }

            const grandTotalElement = document.getElementById('grandTotal');
            if (!grandTotalElement) {
                return true;
            }

            const grandTotal = parseFloat(grandTotalElement.textContent.replace(/[$,]/g, ''));
            const difference = Math.abs(grandTotal - budgetTotalAmount);

            if (difference > 0.01) {
                e.preventDefault();

                Swal.fire({
                    title: '¡Error en la Distribución!',
                    html: `
                        <div class="text-start">
                            <p>La suma de las distribuciones <strong>no coincide</strong> con el monto total anual.</p>
                            <hr>
                            <p><strong>Monto Total Anual:</strong> $${budgetTotalAmount.toFixed(2)}</p>
                            <p><strong>Suma de Distribuciones:</strong> $${grandTotal.toFixed(2)}</p>
                            <p class="text-danger"><strong>Diferencia:</strong> $${difference.toFixed(2)}</p>
                            <hr>
                            <p class="mb-0">Por favor, ajuste las distribuciones antes de guardar.</p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Entendido',
                    width: '600px'
                });

                return false;
            }
        });
    }

    // ========================================
    // MODAL: NUEVA CATEGORÍA
    // ========================================
    const newCategoryModalElement = document.getElementById('newCategoryModal');
    if (!newCategoryModalElement) {
        console.error('ERROR: No se encontró el modal #newCategoryModal');
    } else {
        const newCategoryModal = new bootstrap.Modal(newCategoryModalElement);
        const categoryForm = document.getElementById('newCategoryForm');

        /**
         * Abrir modal
         */
        const btnAddCategory = document.getElementById('btnAddCategory');
        if (btnAddCategory) {
            btnAddCategory.addEventListener('click', function() {
                if (categoryForm) {
                    categoryForm.reset();
                }
                const errorsDiv = document.getElementById('categoryFormErrors');
                if (errorsDiv) {
                    errorsDiv.classList.add('d-none');
                }
                newCategoryModal.show();
            });
        }

        /**
         * Convertir código a mayúsculas automáticamente
         */
        const codeInput = document.getElementById('newCategoryCode');
        if (codeInput) {
            codeInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        /**
         * Enviar formulario de nueva categoría
         */
        if (categoryForm) {
            categoryForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const errorsDiv = document.getElementById('categoryFormErrors');
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                // Mostrar estado de carga
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

                try {
                    const response = await fetch('{{ route("expense-categories.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw data;
                    }

                    // Cerrar el modal
                    newCategoryModal.hide();

                    // Agregar la nueva opción al select
                    const option = new Option(
                        `[${data.code}] ${data.name}`,
                        data.id,
                        false,
                        true
                    );
                    option.dataset.code = data.code;
                    option.dataset.name = data.name;

                    // Agregar la opción al select2
                    $categorySelector.append(option);
                    $categorySelector.trigger('change');

                    // Mostrar mensaje de éxito
                    if (typeof toastOk === 'function') {
                        toastOk('Categoría creada exitosamente');
                    } else {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Categoría creada exitosamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } catch (error) {
                    // Manejar errores de validación
                    if (error.errors) {
                        let errorHtml = '<ul class="mb-0">';
                        Object.values(error.errors).forEach(err => {
                            errorHtml += `<li>${err[0]}</li>`;
                        });
                        errorHtml += '</ul>';

                        if (errorsDiv) {
                            errorsDiv.innerHTML = errorHtml;
                            errorsDiv.classList.remove('d-none');

                            // Desplazarse al mensaje de error
                            errorsDiv.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                        }
                    } else {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'Error',
                            text: 'Ocurrió un error al guardar la categoría: ' + (error.message || 'Error desconocido'),
                            icon: 'error',
                            confirmButtonText: 'Entendido'
                        });
                    }
                } finally {
                    // Restaurar el botón
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        }

        /**
         * Limpiar errores al abrir el modal
         */
        newCategoryModalElement.addEventListener('show.bs.modal', function() {
            const errorsDiv = document.getElementById('categoryFormErrors');
            if (errorsDiv) {
                errorsDiv.classList.add('d-none');
                errorsDiv.innerHTML = '';
            }
        });
    }

    // ========================================
    // INICIALIZACIÓN
    // ========================================
    console.log('Ejecutando inicialización...');
    updateMatrix();
    console.log('=== Script de Distribución Mensual Completado ===');
});
</script>

<style>
    /* Columna fija izquierda */
    .sticky-column {
        position: sticky;
        left: 0;
        background: #f8f9fa;
        z-index: 10;
    }

    /* Inputs más pequeños */
    #distributionMatrix input[type="number"] {
        font-size: 0.875rem;
        padding: 0.25rem;
    }

    /* Resaltar errores */
    #distributionMatrix input[type="number"]:invalid {
        border-color: #dc3545;
    }
</style>
@endpush