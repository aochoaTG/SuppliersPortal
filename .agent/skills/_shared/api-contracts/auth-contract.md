# API Contract: Authentication System

## Endpoints

### 1. Login
**Endpoint**: `POST /login`
**Descripción**: Autentica al usuario.

#### Request Body
```json
{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```

#### Validations
- `email`: required, email
- `password`: required, string

---

### 2. Registro
**Endpoint**: `POST /register`
**Descripción**: Crea una nueva cuenta de usuario.

#### Request Body
```json
{
  "name": "Nombre Usuario",
  "email": "nuevo@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

#### Validations
- `name`: required, string, max:255
- `email`: required, email, unique:users
- `password`: required, min:8, confirmed

---

### 3. Recuperación de Contraseña
**Endpoint**: `POST /password/email`
**Descripción**: Envía link de recuperación por correo.

#### Request Body
```json
{
  "email": "user@example.com"
}
```

---

## 🎨 Especificaciones Visuales

### Fondo: Galaxia Estelar
- Capas: 3 niveles de estrellas con diferentes tamaños y velocidades.
- Movimiento: `animation: parallax 100s linear infinite`.
- Colores: Azul profundo, violeta sutil y blanco puro para estrellas.

### Tarjeta: Glassmorphism
- `background: rgba(255, 255, 255, 0.1);`
- `backdrop-filter: blur(10px);`
- `border: 1px solid rgba(255, 255, 255, 0.2);`
- `box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);`

### Tipografía
- `font-family: 'Outfit', sans-serif;` (Google Fonts)
- Colores: Texto principal blanco, acentos en cyan/celeste neón.
