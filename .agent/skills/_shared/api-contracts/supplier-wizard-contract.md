# API Contract: Supplier Registration Wizard

## Base URL
`/suppliers/register`

---

## 1. Validar Paso 1
**Endpoint**: `POST /step-1-validate`
**Descripción**: Valida los datos generales antes de permitir avanzar al paso 2.

### Request Body
```json
{
  "rfc": "XAXX010101000",
  "company_name": "Empresa de Prueba S.A. de C.V.",
  "contact_name": "Juan Pérez",
  "email": "juan@empresa.com",
  "phone": "5551234567",
  "curp": "PEPJ800101HDFRXX00" // Opcional si es persona moral
}
```

### Validations
- `rfc`: required, unique:suppliers, regex:/^[A-Z&Ñ]{3,4}[0-9]{6}[A-V1-9][A-Z1-9][0-9A]$/i
- `company_name`: required, string, max:255
- `email`: required, email
- `curp`: nullable, regex (solo si es persona física)

### Response (200 OK)
```json
{
  "valid": true,
  "message": "Datos válidos"
}
```

### Response (422 Unprocessable Entity)
```json
{
  "message": "El RFC ya se encuentra registrado.",
  "errors": {
    "rfc": ["El RFC ya se encuentra registrado."]
  }
}
```

---

## 2. Registro Completo
**Endpoint**: `POST /store`
**Descripción**: Guarda toda la información y archivos procesados (Multipart Form Data).

### Request Body (FormData)
- `rfc`: "XAXX010101000"
- ... (todos los campos del paso 1)
- `address`: "Calle Falsa 123, Col. Centro..."
- `file_constancia`: [Binary File] (.pdf, .jpg)
- `file_contrato`: [Binary File] (.pdf)
- `file_domicilio`: [Binary File] (.pdf, .jpg)

### Validations
- `file_constancia`: required, mimes:pdf,jpg,max:2048
- `file_contrato`: required, mimes:pdf,max:5120
- `file_domicilio`: required, mimes:pdf,jpg,max:2048
- `address`: required, string

### Response (201 Created)
```json
{
  "success": true,
  "message": "Registro completado exitosamente. Se ha enviado un correo de confirmación.",
  "supplier_id": 15
}
```

---

## 3. Estructura de Base de Datos

### Tabla: `suppliers`
| Columna | Tipo | Detalles |
|---------|------|----------|
| id | BIGINT | PK, AI |
| rfc | VARCHAR(13) | Unique, Index |
| company_name | VARCHAR(255) | |
| contact_name | VARCHAR(255) | |
| email | VARCHAR(255) | |
| phone | VARCHAR(20) | |
| curp | VARCHAR(18) | Nullable |
| address | TEXT | |
| status | VARCHAR(20) | Default 'pending' |
| created_at | DATETIME | |

### Tabla: `supplier_documents`
| Columna | Tipo | Detalles |
|---------|------|----------|
| id | BIGINT | PK, AI |
| supplier_id | BIGINT | FK -> suppliers.id |
| document_type | VARCHAR(50) | 'constancia', 'contrato', 'domicilio' |
| file_path | VARCHAR(255) | Ruta relativa en storage |
| file_name | VARCHAR(255) | Nombre original |
| created_at | DATETIME | |
