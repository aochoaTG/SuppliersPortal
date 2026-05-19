<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CfdiGeneratorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (['superadmin', 'accounting'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');
        return $user;
    }

    private function basePost(): array
    {
        return [
            'rfcEmisor'               => 'AAA010101AAA',
            'nombreEmisor'            => 'Proveedor SA',
            'regimenFiscal'           => '601',
            'rfcReceptor'             => 'TGT010101TGT',
            'nombreReceptor'          => 'Total Gas',
            'domicilioFiscalReceptor' => '64000',
            'regimenFiscalReceptor'   => '601',
            'usoCFDI'                 => 'G03',
            'serie'                   => 'A',
            'folio'                   => '1',
            'fecha'                   => '2026-05-19T10:00',
            'formaPago'               => '03',
            'metodoPago'              => 'PUE',
            'moneda'                  => 'MXN',
            'claveProdServ'           => '84111506',
            'claveUnidad'             => 'E48',
            'descripcion'             => 'Servicios',
            'cantidad'                => 1,
            'valorUnitario'           => 1000,
            'tasaIVA'                 => '0.16',
            'subtotal'                => 1000,
            'iva'                     => 160,
            'total'                   => 1160,
        ];
    }

    public function test_form_accessible_by_superadmin(): void
    {
        Supplier::factory()->create(['rfc' => 'AAA010101AAA', 'company_name' => 'Proveedor SA']);
        Company::factory()->create(['rfc' => 'TGT010101TGT', 'is_active' => true]);

        $response = $this->actingAs($this->superadmin())->get(route('tools.cfdi.form'));

        $response->assertOk();
    }

    public function test_form_denied_for_non_superadmin(): void
    {
        $user = User::factory()->create();
        $user->assignRole('accounting');

        $response = $this->actingAs($user)->get(route('tools.cfdi.form'));

        $response->assertForbidden();
    }

    public function test_download_xml_returns_xml_content_type(): void
    {
        Supplier::factory()->create(['rfc' => 'AAA010101AAA', 'company_name' => 'Proveedor SA']);
        Company::factory()->create(['rfc' => 'TGT010101TGT', 'is_active' => true]);

        $response = $this->actingAs($this->superadmin())
            ->post(route('tools.cfdi.xml'), $this->basePost());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_download_pdf_returns_pdf_content_type(): void
    {
        Supplier::factory()->create(['rfc' => 'AAA010101AAA', 'company_name' => 'Proveedor SA']);
        Company::factory()->create(['rfc' => 'TGT010101TGT', 'is_active' => true]);

        $response = $this->actingAs($this->superadmin())
            ->post(route('tools.cfdi.pdf'), $this->basePost());

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_download_xml_fails_validation_without_required_fields(): void
    {
        $response = $this->actingAs($this->superadmin())
            ->post(route('tools.cfdi.xml'), []);

        $response->assertSessionHasErrors(['rfcEmisor', 'rfcReceptor', 'fecha']);
    }
}
