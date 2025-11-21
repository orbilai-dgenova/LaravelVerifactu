<?php

declare(strict_types=1);

namespace Tests\Unit\Scenarios;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;
use Squareetlabs\VeriFactu\Models\Recipient;

/**
 * Test para facturas con régimen OSS (One Stop Shop)
 * 
 * Caso de uso: Ventas intracomunitarias a consumidores finales
 * Régimen: OSS (17)
 * Se usa para e-commerce B2C en la UE
 */
class OssRegimeInvoiceTest extends TestCase
{
    /** @test */
    public function it_creates_invoice_with_oss_regime()
    {
        // Venta desde España a consumidor final en Francia
        $invoice = Invoice::factory()->create([
            'number' => 'F-2025-EU-001',
            'issuer_name' => 'Tienda Online España SL',
            'issuer_tax_id' => 'B12345678',
            'type' => 'F1',
            'description' => 'Venta online a cliente francés (OSS)',
            'amount' => 100.00,
            'tax' => 20.00, // IVA francés 20%
            'total' => 120.00,
        ]);

        // Destinatario en Francia (consumidor final, sin NIF empresarial)
        Recipient::factory()->create([
            'invoice_id' => $invoice->id,
            'name' => 'Jean Dupont',
            'tax_id' => 'FR123456789', // Número de identificación fiscal francés
            'country' => 'FR',
        ]);

        // Breakdown con régimen OSS
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '17', // OSS
            'operation_type' => 'S1',
            'tax_rate' => 20.00, // Tipo IVA del país de destino
            'base_amount' => 100.00,
            'tax_amount' => 20.00,
        ]);

        // Assert
        $breakdown = $invoice->breakdowns->first();
        $this->assertEquals('17', $breakdown->regime_type->value ?? $breakdown->regime_type);
        $this->assertEquals(20.00, $breakdown->tax_rate); // IVA del país destino
    }

    /** @test */
    public function it_supports_oss_with_multiple_countries()
    {
        // Venta a múltiples países UE en una sola factura
        $invoice = Invoice::factory()->create([
            'type' => 'F1',
            'amount' => 300.00,
            'tax' => 61.00, // 20 (FR) + 21 (ES) + 20 (IT)
            'total' => 361.00,
        ]);

        // Francia - IVA 20%
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '17', // OSS
            'operation_type' => 'S1',
            'tax_rate' => 20.00,
            'base_amount' => 100.00,
            'tax_amount' => 20.00,
        ]);

        // España - IVA 21%
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '17', // OSS
            'operation_type' => 'S1',
            'tax_rate' => 21.00,
            'base_amount' => 100.00,
            'tax_amount' => 21.00,
        ]);

        // Italia - IVA 20%
        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '17', // OSS
            'operation_type' => 'S1',
            'tax_rate' => 20.00,
            'base_amount' => 100.00,
            'tax_amount' => 20.00,
        ]);

        $this->assertCount(3, $invoice->breakdowns);
        $this->assertEquals(361.00, $invoice->total);
    }
}

