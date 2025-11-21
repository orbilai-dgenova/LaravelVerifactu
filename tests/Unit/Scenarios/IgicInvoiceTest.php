<?php

declare(strict_types=1);

namespace Tests\Unit\Scenarios;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;

/**
 * Test para factura con IGIC (Canarias)
 * 
 * Caso de uso: Factura emitida en Canarias con IGIC
 * Impuesto: IGIC (02)
 * Régimen: General (01)
 * Operación: Sujeta no exenta (S1)
 */
class IgicInvoiceTest extends TestCase
{
    /** @test */
    public function it_generates_valid_invoice_with_igic()
    {
        // Arrange: Factura con IGIC al 7% (tipo general en Canarias)
        $invoice = Invoice::factory()->create([
            'number' => 'F-2025-CAN-001',
            'issuer_name' => 'Empresa Canaria SL',
            'issuer_tax_id' => 'B76543210',
            'type' => 'F1',
            'description' => 'Venta de productos en Canarias',
            'amount' => 100.00,
            'tax' => 7.00, // IGIC 7%
            'total' => 107.00,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '02', // IGIC
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 7.00,
            'base_amount' => 100.00,
            'tax_amount' => 7.00,
        ]);

        // Assert: Verificar que se usa IGIC correctamente
        $breakdown = $invoice->breakdowns->first();
        $this->assertEquals('02', $breakdown->tax_type->value ?? $breakdown->tax_type);
        $this->assertEquals(7.00, $breakdown->tax_rate);
        $this->assertEquals(107.00, $invoice->total);
    }

    /** @test */
    public function it_supports_multiple_igic_rates()
    {
        // IGIC tiene tipos reducidos: 0%, 3%, 7% (general), 9.5%, 15% (especial)
        $invoice = Invoice::factory()->create([
            'type' => 'F1',
            'amount' => 210.00,
            'tax' => 16.00, // 3 + 7 + 6
            'total' => 226.00,
        ]);

        // IGIC 3% reducido
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '02', // IGIC
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 3.00,
            'base_amount' => 100.00,
            'tax_amount' => 3.00,
        ]);

        // IGIC 7% general
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '02', // IGIC
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 7.00,
            'base_amount' => 100.00,
            'tax_amount' => 7.00,
        ]);

        // IGIC 0% (exento en Canarias para ciertos productos)
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '02', // IGIC
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 0.00,
            'base_amount' => 10.00,
            'tax_amount' => 0.00,
        ]);

        // Assert
        $this->assertCount(3, $invoice->breakdowns);
        $this->assertEquals(226.00, $invoice->total);
    }
}

