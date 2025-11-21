<?php

declare(strict_types=1);

namespace Tests\Unit\Scenarios;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;

/**
 * Test para facturas de subsanación
 * 
 * Caso de uso: Reenvío de factura previamente rechazada por AEAT
 * Marca: Subsanacion = 'S', RechazoPrevio = 'S'
 */
class SubsanacionInvoiceTest extends TestCase
{
    /** @test */
    public function it_creates_subsanacion_invoice_after_rejection()
    {
        // Factura rechazada previamente
        $rejectedInvoice = Invoice::factory()->create([
            'number' => 'F-2025-100-REJ',
            'date' => now()->subDays(5),
            'type' => 'F1',
            'status' => 'rejected',
            'amount' => 100.00,
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        // Subsanación de la factura rechazada (nuevo número)
        $subsanacion = Invoice::factory()->create([
            'number' => 'F-2025-100', // Nuevo número para la subsanación
            'date' => now(),
            'type' => 'F1',
            'is_subsanacion' => true,
            'rejected_invoice_number' => $rejectedInvoice->number,
            'rejection_date' => $rejectedInvoice->date,
            'description' => 'Reenvío tras corrección de errores',
            'amount' => 100.00,
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $subsanacion->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
            'tax_rate' => 21.00,
            'base_amount' => 100.00,
            'tax_amount' => 21.00,
        ]);

        // Assert
        $this->assertTrue($subsanacion->is_subsanacion);
        $this->assertEquals($rejectedInvoice->number, $subsanacion->rejected_invoice_number);
        $this->assertNotNull($subsanacion->rejection_date);
        // La subsanación puede tener un número diferente al rechazado
        $this->assertEquals('F-2025-100', $subsanacion->number);
    }

    /** @test */
    public function subsanacion_invoice_must_have_rejected_reference()
    {
        $subsanacion = Invoice::factory()->create([
            'is_subsanacion' => true,
            'rejected_invoice_number' => 'F-2025-REJECTED',
            'rejection_date' => now()->subWeek(),
        ]);

        // Si es subsanación, debe tener referencia al rechazado
        $this->assertTrue($subsanacion->is_subsanacion);
        $this->assertNotNull($subsanacion->rejected_invoice_number);
        $this->assertNotNull($subsanacion->rejection_date);
    }

    /** @test */
    public function normal_invoice_is_not_subsanacion()
    {
        $invoice = Invoice::factory()->create([
            'is_subsanacion' => false,
            'rejected_invoice_number' => null,
            'rejection_date' => null,
        ]);

        $this->assertFalse($invoice->is_subsanacion);
        $this->assertNull($invoice->rejected_invoice_number);
    }
}

