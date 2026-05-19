<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Supplier;
use App\Services\CfdiGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CfdiGeneratorController extends Controller
{
    public function __construct(private CfdiGeneratorService $service) {}

    public function form(): View
    {
        $suppliers = Supplier::orderBy('company_name')->get(['id', 'company_name', 'rfc']);
        $companies = Company::where('is_active', true)->orderBy('name')->get(['id', 'name', 'rfc']);

        return view('tools.cfdi-generator', [
            'suppliers'        => $suppliers,
            'companies'        => $companies,
            'regimenes'        => $this->regimenesFiscales(),
            'usosCfdi'         => $this->usosCfdi(),
            'formasPago'       => $this->formasPago(),
            'retencionCatalog' => CfdiGeneratorService::RETENCIONES_CATALOG,
        ]);
    }

    public function downloadXml(Request $request): Response
    {
        $data     = $this->validated($request);
        $xml      = $this->service->buildXml($data);
        $filename = 'cfdi_prueba_' . now()->format('YmdHis') . '.xml';

        return response($xml, 200, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function downloadPdf(Request $request): Response
    {
        $data     = $this->validated($request);
        $pdf      = $this->service->buildPdf($data);
        $filename = 'cfdi_prueba_' . now()->format('YmdHis') . '.pdf';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'rfcEmisor'               => 'required|string|exists:suppliers,rfc',
            'nombreEmisor'            => 'required|string|max:300',
            'regimenFiscal'           => 'required|string',
            'rfcReceptor'             => 'required|string|exists:companies,rfc',
            'nombreReceptor'          => 'required|string|max:300',
            'domicilioFiscalReceptor' => 'required|string|max:5',
            'regimenFiscalReceptor'   => 'required|string',
            'usoCFDI'                 => 'required|string',
            'serie'                   => 'nullable|string|max:25',
            'folio'                   => 'nullable|string|max:40',
            'fecha'                   => 'required|date_format:Y-m-d\TH:i',
            'formaPago'               => 'required|string',
            'metodoPago'              => 'required|in:PUE,PPD',
            'moneda'                  => 'required|in:MXN,USD',
            'claveProdServ'           => 'required|string|max:8',
            'claveUnidad'             => 'required|string|max:3',
            'descripcion'             => 'required|string|max:1000',
            'cantidad'                => 'required|numeric|min:0.001',
            'valorUnitario'           => 'required|numeric|min:0',
            'tasaIVA'                 => 'required|string|in:0,0.08,0.16',
            'subtotal'                => 'required|numeric|min:0',
            'iva'                     => 'required|numeric|min:0',
            'total'                   => 'required|numeric|min:0',
            'ret_enabled'             => 'nullable|array',
            'ret_enabled.*'           => 'string',
            'ret_tasa'                => 'nullable|array',
            'ret_tasa.*'              => 'numeric|min:0|max:1',
            'ret_impuesto'            => 'nullable|array',
            'ret_impuesto.*'          => 'in:001,002',
        ]);
    }

    private function regimenesFiscales(): array
    {
        return [
            '601' => '601 - General de Ley Personas Morales',
            '603' => '603 - Personas Morales con Fines no Lucrativos',
            '605' => '605 - Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606' => '606 - Arrendamiento',
            '607' => '607 - Régimen de Enajenación o Adquisición de Bienes',
            '608' => '608 - Demás ingresos',
            '610' => '610 - Residentes en el Extranjero sin Establecimiento Permanente',
            '611' => '611 - Ingresos por Dividendos (socios y accionistas)',
            '612' => '612 - Personas Físicas con Actividades Empresariales y Profesionales',
            '614' => '614 - Ingresos por intereses',
            '615' => '615 - Régimen de los ingresos por obtención de premios',
            '616' => '616 - Sin obligaciones fiscales',
            '620' => '620 - Sociedades Cooperativas de Producción',
            '621' => '621 - Incorporación Fiscal',
            '622' => '622 - Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623' => '623 - Opcional para Grupos de Sociedades',
            '624' => '624 - Coordinados',
            '625' => '625 - Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626' => '626 - Régimen Simplificado de Confianza',
        ];
    }

    private function usosCfdi(): array
    {
        return [
            'G01'  => 'G01 - Adquisición de mercancias',
            'G02'  => 'G02 - Devoluciones, descuentos o bonificaciones',
            'G03'  => 'G03 - Gastos en general',
            'I01'  => 'I01 - Construcciones',
            'I02'  => 'I02 - Mobilario y equipo de oficina por inversiones',
            'I03'  => 'I03 - Equipo de transporte',
            'I04'  => 'I04 - Equipo de computo y accesorios',
            'I05'  => 'I05 - Dados, troqueles, moldes, matrices y herramental',
            'I06'  => 'I06 - Comunicaciones telefónicas',
            'I07'  => 'I07 - Comunicaciones satelitales',
            'I08'  => 'I08 - Otra maquinaria y equipo',
            'D01'  => 'D01 - Honorarios médicos, dentales y gastos hospitalarios',
            'D02'  => 'D02 - Gastos médicos por incapacidad o discapacidad',
            'D03'  => 'D03 - Gastos funerales',
            'D04'  => 'D04 - Donativos',
            'D05'  => 'D05 - Intereses reales efectivamente pagados por créditos hipotecarios',
            'D06'  => 'D06 - Aportaciones voluntarias al SAR',
            'D07'  => 'D07 - Primas por seguros de gastos médicos',
            'D08'  => 'D08 - Gastos de transportación escolar obligatoria',
            'D09'  => 'D09 - Depósitos en cuentas para el ahorro',
            'D10'  => 'D10 - Pagos por servicios educativos (colegiaturas)',
            'S01'  => 'S01 - Sin efectos fiscales',
            'CP01' => 'CP01 - Pagos',
            'CN01' => 'CN01 - Nómina',
        ];
    }

    private function formasPago(): array
    {
        return [
            '01' => '01 - Efectivo',
            '02' => '02 - Cheque nominativo',
            '03' => '03 - Transferencia electrónica de fondos',
            '04' => '04 - Tarjeta de crédito',
            '05' => '05 - Monedero electrónico',
            '06' => '06 - Dinero electrónico',
            '08' => '08 - Vales de despensa',
            '12' => '12 - Dación en pago',
            '13' => '13 - Pago por subrogación',
            '14' => '14 - Pago por consignación',
            '15' => '15 - Condonación',
            '17' => '17 - Compensación',
            '23' => '23 - Novación',
            '24' => '24 - Confusión',
            '25' => '25 - Remisión de deuda',
            '26' => '26 - Prescripción o caducidad',
            '27' => '27 - A satisfacción del acreedor',
            '28' => '28 - Tarjeta de débito',
            '29' => '29 - Tarjeta de servicios',
            '30' => '30 - Aplicación de anticipos',
            '31' => '31 - Intermediario pagos',
            '99' => '99 - Por definir',
        ];
    }
}
