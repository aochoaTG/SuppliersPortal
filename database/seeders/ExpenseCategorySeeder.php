<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $createdBy = 6; // Usuario administrador que crea las categorías

        $categories = [
            // ==========================================
            // CATEGORÍAS BASE (7)
            // ==========================================
            [
                'code' => 'MAT',
                'name' => 'Materiales',
                'description' => 'Insumos y materias primas necesarios para operaciones',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SER',
                'name' => 'Servicios',
                'description' => 'Servicios profesionales y técnicos contratados',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'VIA',
                'name' => 'Viáticos',
                'description' => 'Gastos de viaje, hospedaje y representación',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MAN',
                'name' => 'Mantenimiento',
                'description' => 'Mantenimiento preventivo y correctivo de equipos e instalaciones',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAP',
                'name' => 'Capacitación',
                'description' => 'Programas de desarrollo, entrenamientos y certificaciones del personal',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'TEC',
                'name' => 'Tecnología',
                'description' => 'Software, hardware, licencias y servicios de tecnología e información',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'OTR',
                'name' => 'Otros Gastos',
                'description' => 'Gastos diversos no clasificados en las categorías anteriores',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // OPERACIÓN GASOLINERA - COMBUSTIBLES Y PRODUCTOS (5)
            // ==========================================
            [
                'code' => 'COM',
                'name' => 'Combustibles',
                'description' => 'Compra de gasolina, diésel y combustibles para reventa',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ADB',
                'name' => 'Aditivos',
                'description' => 'Aditivos y mejoradores de combustible',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LUB',
                'name' => 'Lubricantes',
                'description' => 'Aceites, grasas y lubricantes para venta',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'TDA',
                'name' => 'Tienda',
                'description' => 'Productos de conveniencia para tienda/minisuper',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CAR',
                'name' => 'Car Wash',
                'description' => 'Insumos para lavado de autos (jabones, ceras, etc)',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // OPERACIÓN GASOLINERA - MANTENIMIENTO ESPECÍFICO (5)
            // ==========================================
            [
                'code' => 'MDS',
                'name' => 'Mant. Dispensarios',
                'description' => 'Mantenimiento y reparación de dispensarios/bombas',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MTQ',
                'name' => 'Mant. Tanques',
                'description' => 'Inspección y mantenimiento de tanques subterráneos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MSI',
                'name' => 'Mant. Sistemas',
                'description' => 'Mantenimiento de sistemas de control y medición',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MEL',
                'name' => 'Mant. Eléctrico',
                'description' => 'Mantenimiento eléctrico de instalaciones',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MED',
                'name' => 'Mant. Edificio',
                'description' => 'Mantenimiento de edificios, baños y áreas comunes',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // OPERACIÓN GASOLINERA - SEGURIDAD Y CUMPLIMIENTO (4)
            // ==========================================
            [
                'code' => 'SEG',
                'name' => 'Seguridad',
                'description' => 'Equipos de seguridad, extintores, señalización',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'AMB',
                'name' => 'Ambiental',
                'description' => 'Cumplimiento ambiental, análisis de suelos, remediación',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CER',
                'name' => 'Certificaciones',
                'description' => 'Certificaciones oficiales y permisos (ASEA, CRE)',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SEI',
                'name' => 'Seguridad Industrial',
                'description' => 'EPP, capacitación en seguridad, equipo de protección',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // OPERACIÓN GASOLINERA - SERVICIOS OPERATIVOS (6)
            // ==========================================
            [
                'code' => 'ENE',
                'name' => 'Energía Eléctrica',
                'description' => 'Consumo de electricidad',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'AGU',
                'name' => 'Agua',
                'description' => 'Consumo de agua potable',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LIM',
                'name' => 'Limpieza',
                'description' => 'Servicios de limpieza y sanitización',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'VIG',
                'name' => 'Vigilancia',
                'description' => 'Servicios de seguridad privada y monitoreo',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'JAR',
                'name' => 'Jardinería',
                'description' => 'Mantenimiento de áreas verdes',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'RES',
                'name' => 'Residuos',
                'description' => 'Recolección y manejo de residuos peligrosos y no peligrosos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // CORPORATIVO - RECURSOS HUMANOS (4)
            // ==========================================
            [
                'code' => 'NOM',
                'name' => 'Nómina',
                'description' => 'Sueldos y salarios del personal',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'PRE',
                'name' => 'Prestaciones',
                'description' => 'Prestaciones sociales, seguros, fondo de ahorro',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'REC',
                'name' => 'Reclutamiento',
                'description' => 'Servicios de reclutamiento y selección',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EVA',
                'name' => 'Evaluaciones',
                'description' => 'Evaluaciones médicas, psicométricas y de desempeño',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // CORPORATIVO - ADMINISTRACIÓN (7)
            // ==========================================
            [
                'code' => 'PAP',
                'name' => 'Papelería',
                'description' => 'Material de oficina y suministros administrativos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'ARR',
                'name' => 'Arrendamiento',
                'description' => 'Rentas de inmuebles y locales',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SEL',
                'name' => 'Seguros',
                'description' => 'Pólizas de seguros (responsabilidad, incendio)',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LEG',
                'name' => 'Legal',
                'description' => 'Servicios legales, notariales y jurídicos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CON',
                'name' => 'Contabilidad',
                'description' => 'Servicios contables y auditoría externa',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FIS',
                'name' => 'Fiscal',
                'description' => 'Asesoría fiscal y trámites ante SAT',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'BAN',
                'name' => 'Bancarios',
                'description' => 'Comisiones bancarias y servicios financieros',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // CORPORATIVO - COMUNICACIÓN Y MARKETING (4)
            // ==========================================
            [
                'code' => 'TEL',
                'name' => 'Telefonía',
                'description' => 'Servicios de telefonía fija y móvil',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'INT',
                'name' => 'Internet',
                'description' => 'Servicios de internet y conectividad',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MKT',
                'name' => 'Marketing',
                'description' => 'Publicidad, promociones y marketing',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'IMA',
                'name' => 'Imagen',
                'description' => 'Diseño gráfico, branding y material POP',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // FLOTILLA Y TRANSPORTE (8)
            // ==========================================
            [
                'code' => 'VEH',
                'name' => 'Vehículos',
                'description' => 'Compra y arrendamiento de vehículos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CGB',
                'name' => 'Combustible Flota',
                'description' => 'Gasolina/diésel para vehículos de la empresa',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MVE',
                'name' => 'Mant. Vehícular',
                'description' => 'Mantenimiento y reparación de vehículos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'SVE',
                'name' => 'Seguros Vehiculares',
                'description' => 'Seguros de vehículos y responsabilidad civil',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'TEN',
                'name' => 'Tenencias',
                'description' => 'Tenencias, verificaciones y trámites vehiculares',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'REF',
                'name' => 'Refacciones',
                'description' => 'Refacciones y partes para vehículos',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'LLA',
                'name' => 'Llantas',
                'description' => 'Compra de llantas y servicios de llantera',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'FLE',
                'name' => 'Fletes',
                'description' => 'Servicios de transporte y logística',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // INFRAESTRUCTURA Y PROYECTOS (5)
            // ==========================================
            [
                'code' => 'OBR',
                'name' => 'Obras',
                'description' => 'Construcción y remodelación de instalaciones',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EQP',
                'name' => 'Equipamiento',
                'description' => 'Compra de equipo y maquinaria',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'MOB',
                'name' => 'Mobiliario',
                'description' => 'Mobiliario y equipo de oficina',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'INV',
                'name' => 'Inversiones',
                'description' => 'Inversiones de capital y proyectos especiales',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CNS',
                'name' => 'Construcción',
                'description' => 'Servicios de construcción y obra civil',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // ==========================================
            // FINANCIEROS Y ESPECIALES (5)
            // ==========================================
            [
                'code' => 'IFI',
                'name' => 'Intereses',
                'description' => 'Intereses financieros y comisiones',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'IMP',
                'name' => 'Impuestos',
                'description' => 'Impuestos y derechos (predial, agua)',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'DON',
                'name' => 'Donaciones',
                'description' => 'Donativos y responsabilidad social',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'EVE',
                'name' => 'Eventos',
                'description' => 'Eventos corporativos, convenciones y conferencias',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CTG',
                'name' => 'Contingencias',
                'description' => 'Gastos imprevistos y contingencias',
                'status' => 'ACTIVO',
                'created_by' => $createdBy,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insertar todas las categorías
        DB::table('expense_categories')->insert($categories);

        $this->command->info('✅ Se han insertado ' . count($categories) . ' categorías de gasto correctamente.');
    }
}
