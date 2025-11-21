<?php

declare(strict_types=1);

namespace Tests\Unit\Scenarios;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;

/**
 * Test para facturas rectificativas (Notas de crédito)
 * 
 * Caso de uso: Factura rectificativa por devolución/error
 * Tipos: I (Por diferencia), S (Por sustitución)
 */
class RectificativeInvoiceTest extends TestCase
{
    /** @test */
    public function it_creates_rectificative_invoice_by_difference()
    {
        // Factura original
        $originalInvoice = Invoice::factory()->create([
            'number' => 'F-2025-100',
            'date' => now()->subDays(10),
            'type' => 'F1',
            'amount' => 100.00,
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        // Factura rectificativa por diferencia (devolución parcial)
        $rectificative = Invoice::factory()->create([
            'number' => 'F-2025-100-R1',
            'type' => 'R1', // Rectificativa
            'rectificative_type' => 'I', // Por diferencia
            'rectified_invoices' => [
                [
                    'issuer_tax_id' => $originalInvoice->issuer_tax_id,
                    'number' => $originalInvoice->number,
                    'date' => $originalInvoice->date->format('d-m-Y'),
                ]
            ],
            'rectification_amount' => [
                'base' => -50.00,
                'tax' => -10.50,
                'total' => -60.50,
            ],
            'amount' => -50.00,
            'tax' => -10.50,
            'total' => -60.50,
            'description' => 'Devolución parcial por productos defectuosos',
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $rectificative->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 21.00,
            'base_amount' => -50.00,
            'tax_amount' => -10.50,
        ]);

        // Assert
        $this->assertEquals('R1', $rectificative->type->value);
        $this->assertEquals('I', $rectificative->rectificative_type);
        $this->assertNotNull($rectificative->rectified_invoices);
        $this->assertCount(1, $rectificative->rectified_invoices);
        $this->assertEquals(-60.50, $rectificative->total);
        $this->assertNotNull($rectificative->rectification_amount);
    }

    /** @test */
    public function it_creates_rectificative_invoice_by_substitution()
    {
        // Factura rectificativa por sustitución (anula completamente la anterior)
        $rectificative = Invoice::factory()->create([
            'number' => 'F-2025-200-R1',
            'type' => 'R1',
            'rectificative_type' => 'S', // Por sustitución
            'rectified_invoices' => [
                [
                    'issuer_tax_id' => 'B12345678',
                    'number' => 'F-2025-200',
                    'date' => '01-11-2025',
                ]
            ],
            'amount' => 150.00, // Nueva cantidad correcta
            'tax' => 31.50,
            'total' => 181.50,
            'description' => 'Sustitución de factura errónea',
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $rectificative->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 21.00,
            'base_amount' => 150.00,
            'tax_amount' => 31.50,
        ]);

        // Assert
        $this->assertEquals('S', $rectificative->rectificative_type);
        $this->assertEquals(181.50, $rectificative->total);
        $this->assertNull($rectificative->rectification_amount); // No se usa en sustitución
    }

    /** @test */
    public function it_can_rectify_multiple_invoices()
    {
        $rectificative = Invoice::factory()->create([
            'type' => 'R1',
            'rectificative_type' => 'I',
            'rectified_invoices' => [
                [
                    'issuer_tax_id' => 'B12345678',
                    'number' => 'F-2025-100',
                    'date' => '01-11-2025',
                ],
                [
                    'issuer_tax_id' => 'B12345678',
                    'number' => 'F-2025-101',
                    'date' => '02-11-2025',
                ],
                [
                    'issuer_tax_id' => 'B12345678',
                    'number' => 'F-2025-102',
                    'date' => '03-11-2025',
                ]
            ],
            'rectification_amount' => [
                'base' => -300.00,
                'tax' => -63.00,
                'total' => -363.00,
            ],
        ]);

        // Assert: Puede rectificar hasta 1000 facturas según XSD
        $this->assertCount(3, $rectificative->rectified_invoices);
    }
}

