@props(['name', 'value' => null])

@php
$months = [
1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
$value = old($name, $value);
@endphp

<select
    id="{{ $name }}"
    name="{{ $name }}"
    class="form-select select2 @error($name) is-invalid @enderror"
    {{ $attributes->merge(['required' => true]) }}
    data-placeholder="Seleccione un mes"
    aria-required="true"
    aria-describedby="{{ $name }}_help"
    aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}">
    <option value=""></option>
    <option value="" disabled {{ !$value ? 'selected' : '' }}>Seleccione un mes</option>
    @foreach($months as $key => $month)
    <option value="{{ $key }}" {{ $value == $key ? 'selected' : '' }}>
        {{ $month }}
    </option>
    @endforeach
</select>
@error($name)
<div id="{{ $name }}_error" class="invalid-feedback" role="alert">
    {{ $message }}
</div>
@enderror

@push('scripts')
<script>
    $(document).ready(function() {
        $('#{{ $name }}').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Seleccione un mes',
            allowClear: true,
            dropdownParent: $('#{{ $name }}').parent()
        });
    });
</script>
@endpush