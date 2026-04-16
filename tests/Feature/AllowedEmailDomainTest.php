<?php

namespace Tests\Feature;

use App\Rules\AllowedEmailDomain;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AllowedEmailDomainTest extends TestCase
{
    private function validate(string $email): bool
    {
        return Validator::make(
            ['email' => $email],
            ['email' => [new AllowedEmailDomain]]
        )->passes();
    }

    public function test_allowed_domain_passes(): void
    {
        $this->assertTrue($this->validate('usuario@petrotal.com.mx'));
        $this->assertTrue($this->validate('nombre@totalgasolineras.com'));
        $this->assertTrue($this->validate('x@totalgasonline-ags.com'));
    }

    public function test_disallowed_domain_fails(): void
    {
        $this->assertFalse($this->validate('usuario@gmail.com'));
        $this->assertFalse($this->validate('nombre@hotmail.com'));
        $this->assertFalse($this->validate('test@empresa.com'));
    }

    public function test_validation_is_case_insensitive(): void
    {
        $this->assertTrue($this->validate('usuario@PETROTAL.COM.MX'));
        $this->assertTrue($this->validate('nombre@TotalGasolineras.COM'));
    }
}
