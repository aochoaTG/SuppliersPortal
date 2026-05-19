# Generador de CFDI de Prueba — Spec de Diseño

**Fecha:** 2026-05-19  
**Alcance:** Sección exclusiva para superadmin que genera XML CFDI 4.0 y PDF estilo PAC descargables, sin persistencia en base de datos.

---

## 1. Objetivo

Permitir al superadmin generar pares de archivos (XML CFDI 4.0 + PDF representación impresa) con datos controlados, para usarlos como fixtures de prueba al subir facturas al portal. Los archivos se generan en memoria y se descargan directamente, sin guardarse en disco ni en base de datos.

---

## 2. Arquitectura

### Rutas

Grupo nuevo bajo middleware `['auth', 'lock', 'role:superadmin']`, prefijo `/tools`, name prefix `tools.`:

| Método | URI                              | Controlador                        | Nombre              |
|--------|----------------------------------|------------------------------------|---------------------|
| GET    | /tools/cfdi-generator            | CfdiGeneratorController@form       | tools.cfdi.form     |
| POST   | /tools/cfdi-generator/xml        | CfdiGeneratorController@downloadXml| tools.cfdi.xml      |
| POST   | /tools/cfdi-generator/pdf        | CfdiGeneratorController@downloadPdf| tools.cfdi.pdf      |

### Archivos nuevos

```
app/Http/Controllers/Tools/CfdiGeneratorController.php
app/Services/CfdiGeneratorService.php
resources/views/tools/cfdi-generator.blade.php
resources/views/tools/cfdi-pdf.blade.php
```

### Flujo

```
Usuario llena formulario
  → click "Descargar XML"  → POST /tools/cfdi-generator/xml
      → CfdiGeneratorController::downloadXml()
          → CfdiGeneratorService::buildXml($data)  → DOMDocument → string
          → Response con Content-Type: application/xml
  → click "Descargar PDF"  → POST /tools/cfdi-generator/pdf
      → CfdiGeneratorController::downloadPdf()
          → CfdiGeneratorService::buildPdf($data)  → Dompdf (blade: cfdi-pdf.blade.php)
          → Response con Content-Type: application/pdf
```

No se persiste nada. Los dos botones son forms independientes con el mismo conjunto de campos hidden.

---

## 3. Campos del formulario

### Bloque 1 — Emisor

| Campo            | Tipo              | Fuente / Notas                                      |
|------------------|-------------------|-----------------------------------------------------|
| `rfcEmisor`      | select            | Catálogo `suppliers` (RFC + company_name)           |
| `nombreEmisor`   | text (auto-fill)  | Se rellena con JS al seleccionar proveedor          |
| `regimenFiscal`  | select            | Catálogo SAT de regímenes fiscales                  |

### Bloque 2 — Receptor

| Campo                    | Tipo              | Fuente / Notas                              |
|--------------------------|-------------------|---------------------------------------------|
| `rfcReceptor`            | select            | Catálogo `companies` (RFC + name)           |
| `nombreReceptor`         | text (auto-fill)  | Se rellena con JS al seleccionar compañía   |
| `domicilioFiscalReceptor`| text              | Código postal                               |
| `regimenFiscalReceptor`  | select            | Catálogo SAT de regímenes fiscales          |
| `usoCFDI`                | select            | Catálogo SAT (G01, G03, I01, etc.)          |

### Bloque 3 — Datos del comprobante

| Campo              | Tipo           | Notas                                                  |
|--------------------|----------------|--------------------------------------------------------|
| `serie`            | text           | Libre                                                  |
| `folio`            | text           | Libre                                                  |
| `fecha`            | datetime-local | Default: ahora                                         |
| `formaPago`        | select         | Catálogo SAT (01-Efectivo, 03-Transferencia, 99, etc.) |
| `metodoPago`       | select         | PUE / PPD                                              |
| `moneda`           | select         | MXN / USD                                              |
| `tipoDeComprobante`| hidden         | Fijo `I` (Ingreso)                                     |

### Bloque 4 — Concepto, Traslados y Retenciones

| Campo            | Tipo    | Notas                                              |
|------------------|---------|----------------------------------------------------|
| `claveProdServ`  | text    | Clave SAT del producto/servicio (ej. 84111506)     |
| `claveUnidad`    | text    | Clave SAT de unidad (ej. E48)                      |
| `descripcion`    | text    | Descripción del concepto                           |
| `cantidad`       | number  | Cantidad                                           |
| `valorUnitario`  | number  | Precio unitario; subtotal = cantidad × valorUnitario|
| `tasaIVA`        | select  | 0%, 8%, 16%                                        |
| `subtotal`       | number  | Calculado con JS (editable como override)          |
| `iva`            | number  | Calculado con JS                                   |
| `retenciones[]`  | checkbox múltiple | Ver tabla de retenciones abajo              |
| `total`          | number  | Calculado: subtotal + IVA - retenciones            |

#### Retenciones disponibles

| Clave     | Impuesto SAT | Porcentaje               | Porcentaje fijo |
|-----------|-------------|--------------------------|-----------------|
| ISR-ARR   | 001 (ISR)   | 10%                      | Sí              |
| ISR-DIV   | 001 (ISR)   | 10%                      | Sí              |
| ISR-EXT   | 001 (ISR)   | 25% (variable por tratado)| No — editable  |
| ISR-HON   | 001 (ISR)   | 10%                      | Sí              |
| ISR-INT   | 001 (ISR)   | 0.15% anual s/saldo prom.| No — editable  |
| ISR-RES   | 001 (ISR)   | 1.25%                    | Sí              |
| ISR-SUE   | 001 (ISR)   | Variable Art. 96         | No — editable  |
| IVA-ARR   | 002 (IVA)   | 10.6667%                 | Sí              |
| IVA-COM   | 002 (IVA)   | 10.6667%                 | Sí              |
| IVA-DES   | 002 (IVA)   | 16%                      | Sí              |
| IVA-DIG   | 002 (IVA)   | Variable 1.4%–16%        | No — editable  |
| IVA-ESP   | 002 (IVA)   | 6%                       | Sí              |
| IVA-HON   | 002 (IVA)   | 10.6667%                 | Sí              |
| IVA-TRA   | 002 (IVA)   | 4%                       | Sí              |

Las retenciones con porcentaje variable muestran un input numérico editable al seleccionarlas. ISR-SUE incluirá advertencia visual: "Este concepto no genera CFDI de Retenciones según el SAT".

#### Cálculo de totales (JS en tiempo real)
```
subtotal       = cantidad × valorUnitario
iva            = subtotal × tasaIVA
retIsr         = subtotal × suma(porcentajes ISR seleccionados)
retIva         = subtotal × suma(porcentajes IVA seleccionados)
total          = subtotal + iva - retIsr - retIva
```

---

## 4. Estructura XML CFDI 4.0

```xml
<?xml version="1.0" encoding="UTF-8"?>
<cfdi:Comprobante
  xmlns:cfdi="http://www.sat.gob.mx/cfd/4"
  xmlns:tfd="http://www.sat.gob.mx/TimbreFiscalDigital"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 cfdv40.xsd
                      http://www.sat.gob.mx/TimbreFiscalDigital
                      http://www.sat.gob.mx/sitio_internet/cfd/TimbreFiscalDigital/TimbreFiscalDigitalv11.xsd"
  Version="4.0"
  Serie="{serie}" Folio="{folio}" Fecha="{fecha}"
  FormaPago="{formaPago}" MetodoPago="{metodoPago}" Moneda="{moneda}"
  SubTotal="{subtotal}" Total="{total}"
  TipoDeComprobante="I" Exportacion="01"
  NoCertificado="00000000000000000000" Certificado="" Sello="">

  <cfdi:Emisor Rfc="{rfcEmisor}" Nombre="{nombreEmisor}" RegimenFiscal="{regimenFiscal}"/>

  <cfdi:Receptor
    Rfc="{rfcReceptor}" Nombre="{nombreReceptor}"
    DomicilioFiscalReceptor="{cp}" RegimenFiscalReceptor="{regimenFiscalReceptor}"
    UsoCFDI="{usoCFDI}"/>

  <cfdi:Conceptos>
    <cfdi:Concepto
      ClaveProdServ="{claveProdServ}" ClaveUnidad="{claveUnidad}"
      Cantidad="{cantidad}" Descripcion="{descripcion}"
      ValorUnitario="{valorUnitario}" Importe="{subtotal}">
      <cfdi:Impuestos>
        <cfdi:Traslados>
          <cfdi:Traslado Base="{subtotal}" Impuesto="002"
            TipoFactor="Tasa" TasaOCuota="{tasaIVA}" Importe="{iva}"/>
        </cfdi:Traslados>
        <!-- cfdi:Retenciones solo si hay retenciones seleccionadas -->
        <cfdi:Retenciones>
          <!-- por cada retención: -->
          <cfdi:Retencion Base="{subtotal}" Impuesto="{001|002}"
            TipoFactor="Tasa" TasaOCuota="{tasa}" Importe="{importe}"/>
        </cfdi:Retenciones>
      </cfdi:Impuestos>
    </cfdi:Concepto>
  </cfdi:Conceptos>

  <cfdi:Impuestos
    TotalImpuestosTrasladados="{iva}"
    TotalImpuestosRetenidos="{totalRetenciones}">
    <cfdi:Traslados>
      <cfdi:Traslado Base="{subtotal}" Impuesto="002"
        TipoFactor="Tasa" TasaOCuota="{tasaIVA}" Importe="{iva}"/>
    </cfdi:Traslados>
    <cfdi:Retenciones>
      <!-- Agrupadas por Impuesto (001 / 002) con importe sumado -->
      <cfdi:Retencion Impuesto="{001|002}" Importe="{importeTotal}"/>
    </cfdi:Retenciones>
  </cfdi:Impuestos>

  <cfdi:Complemento>
    <tfd:TimbreFiscalDigital
      Version="1.1"
      UUID="{Str::uuid()}"
      FechaTimbrado="{fecha}"
      RfcProvCertif="SAT970701NN3"
      NoCertificadoSAT="00000000000000000000"
      SelloSAT="" SelloCFD=""/>
  </cfdi:Complemento>
</cfdi:Comprobante>
```

- `Sello`, `Certificado`, `SelloSAT`, `SelloCFD` → vacíos (mock)
- `UUID` → `Str::uuid()` generado en el servidor al momento del request
- `cfdi:Retenciones` a nivel concepto y a nivel `cfdi:Impuestos` se omiten si no hay retenciones seleccionadas
- `TotalImpuestosRetenidos` se omite del nodo `cfdi:Impuestos` si no hay retenciones

---

## 5. PDF (representación impresa estilo PAC)

Generado con `barryvdh/laravel-dompdf` a partir de `resources/views/tools/cfdi-pdf.blade.php`.

Layout en una sola página A4:

1. **Encabezado:** nombre emisor, RFC emisor, régimen fiscal
2. **Datos fiscales del receptor:** RFC, nombre, CP, régimen, uso CFDI
3. **Datos del comprobante:** serie/folio, fecha, forma/método de pago, moneda
4. **Tabla de conceptos:** ClaveProdServ, ClaveUnidad, cantidad, descripción, valor unitario, importe
5. **Tabla de totales:** subtotal, IVA, retenciones (desglosadas), **total**
6. **Datos de timbre:** UUID, fecha timbrado, RFC PAC (mock)
7. **Marca de agua diagonal:** "DOCUMENTO DE PRUEBA — NO VÁLIDO FISCALMENTE" en rojo semitransparente

---

## 6. Validación del formulario

Validación server-side en el controlador antes de generar:

- `rfcEmisor`: required, existe en `suppliers.rfc`
- `rfcReceptor`: required, existe en `companies.rfc`
- `regimenFiscal`, `regimenFiscalReceptor`: required, string
- `usoCFDI`: required, string
- `domicilioFiscalReceptor`: required, string, max 5
- `serie`: nullable, string, max 25
- `folio`: nullable, string, max 40
- `fecha`: required, date
- `formaPago`: required, string
- `metodoPago`: required, in:PUE,PPD
- `moneda`: required, in:MXN,USD
- `claveProdServ`: required, string, max 8
- `claveUnidad`: required, string, max 3
- `descripcion`: required, string, max 1000
- `cantidad`: required, numeric, min:0.001
- `valorUnitario`: required, numeric, min:0
- `tasaIVA`: required, in:0,0.08,0.16
- `retenciones`: nullable, array
- `retenciones.*.clave`: required_with:retenciones, string
- `retenciones.*.tasa`: required_with:retenciones, numeric

---

## 7. Navegación

Agregar enlace en `layouts/navigation.blade.php` visible solo para `superadmin`:

```blade
@role('superadmin')
<x-nav-link :href="route('tools.cfdi.form')" :active="request()->routeIs('tools.*')">
    Herramientas
</x-nav-link>
@endrole
```

---

## 8. Decisiones excluidas (YAGNI)

- No se guarda historial de CFDIs generados
- No se valida que el RFC exista en el SAT (EFOS)
- No se soporta más de un concepto por CFDI en esta versión
- No se soportan complementos de pago (PPD con complemento)
- No se genera cadena original ni sello real
