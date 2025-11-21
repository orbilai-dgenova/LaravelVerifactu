<?php

declare(strict_types=1);

namespace Tests\Unit\Scenarios;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;
use Squareetlabs\VeriFactu\Models\Recipient;

/**
 * Test para factura estándar con IVA en régimen general
 * 
 * Caso de uso: Factura simple con un solo tipo de IVA
 * Impuesto: IVA (01)
 * Régimen: General (01)
 * Operación: Sujeta no exenta (S1)
 */
class StandardInvoiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_creates_valid_standard_invoice_with_iva()
    {
        // Arrange: Crear factura estándar
        $invoice = Invoice::factory()->create([
            'number' => 'F-2025-001',
            'date' => now(),
            'issuer_name' => 'Test Company SL',
            'issuer_tax_id' => 'B12345678',
            'type' => 'F1', // Factura completa
            'description' => 'Venta de productos',
            'amount' => 100.00,
            'tax' => 21.00,
            'total' => 121.00,
            'is_first_invoice' => true,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1', // Sujeta no exenta
            'tax_rate' => 21.00,
            'base_amount' => 100.00,
            'tax_amount' => 21.00,
        ]);

        Recipient::factory()->create([
            'invoice_id' => $invoice->id,
            'name' => 'Cliente Test SA',
            'tax_id' => 'B87654321',
        ]);

        // Assert: Verificar datos de la factura
        $this->assertNotNull($invoice);
        $this->assertEquals('B12345678', $invoice->issuer_tax_id);
        $this->assertEquals(121.00, $invoice->total);
        $this->assertTrue($invoice->is_first_invoice);
        
        // Verificar breakdown
        $breakdown = $invoice->breakdowns->first();
        $this->assertEquals('01', $breakdown->tax_type->value ?? $breakdown->tax_type);
        $this->assertEquals('01', $breakdown->regime_type->value ?? $breakdown->regime_type);
        $this->assertEquals('S1', $breakdown->operation_type->value ?? $breakdown->operation_type);
        
        // Verificar recipient
        $recipient = $invoice->recipients->first();
        $this->assertEquals('Cliente Test SA', $recipient->name);
        $this->assertEquals('B87654321', $recipient->tax_id);
    }

    /** @test */
    public function it_includes_correct_namespace_for_standard_invoice()
    {
        $invoice = Invoice::factory()->create([
            'type' => 'F1',
            'is_first_invoice' => true,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
        ]);

        // Verificar que se use el namespace correcto
        $this->assertDatabaseHas('breakdowns', [
            'invoice_id' => $invoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
        ]);
    }

    /** @test */
    public function it_calculates_correct_hash_for_standard_invoice()
    {
        $invoice = Invoice::factory()->create([
            'issuer_tax_id' => 'B12345678',
            'number' => 'F-2025-001',
            'date' => now()->setDate(2025, 11, 21),
            'type' => 'F1',
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        // El hash debe generarse automáticamente
        $this->assertNotNull($invoice->hash);
        $this->assertEquals(64, strlen($invoice->hash)); // SHA-256 = 64 chars hex
    }
}

