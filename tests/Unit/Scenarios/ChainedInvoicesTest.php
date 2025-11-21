<?php

declare(strict_types=1);

namespace Tests\Unit\Scenarios;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;

/**
 * Test para encadenamiento de facturas (Blockchain)
 * 
 * Caso de uso: Facturas encadenadas para trazabilidad
 * Cada factura referencia la anterior mediante su hash
 */
class ChainedInvoicesTest extends TestCase
{
    /** @test */
    public function it_marks_first_invoice_correctly()
    {
        $firstInvoice = Invoice::factory()->create([
            'number' => 'F-2025-001',
            'type' => 'F1',
            'is_first_invoice' => true,
            'previous_invoice_number' => null,
            'previous_invoice_date' => null,
            'previous_invoice_hash' => null,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $firstInvoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
        ]);

        // Assert
        $this->assertTrue($firstInvoice->is_first_invoice);
        $this->assertNull($firstInvoice->previous_invoice_number);
        $this->assertNull($firstInvoice->previous_invoice_date);
        $this->assertNull($firstInvoice->previous_invoice_hash);
        $this->assertNotNull($firstInvoice->hash); // Genera su propio hash
    }

    /** @test */
    public function it_chains_second_invoice_to_first()
    {
        // Primera factura
        $firstInvoice = Invoice::factory()->create([
            'number' => 'F-2025-001',
            'date' => now()->subDay(),
            'issuer_tax_id' => 'B12345678',
            'type' => 'F1',
            'is_first_invoice' => true,
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $firstInvoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
        ]);

        // Segunda factura (encadenada)
        $secondInvoice = Invoice::factory()->create([
            'number' => 'F-2025-002',
            'date' => now(),
            'issuer_tax_id' => 'B12345678',
            'type' => 'F1',
            'is_first_invoice' => false,
            'previous_invoice_number' => $firstInvoice->number,
            'previous_invoice_date' => $firstInvoice->date,
            'previous_invoice_hash' => $firstInvoice->hash,
            'tax' => 42.00,
            'total' => 242.00,
        ]);

        Breakdown::factory()->create([
            'invoice_id' => $secondInvoice->id,
            'tax_type' => '01', // IVA
            'regime_type' => '01', // General
            'operation_type' => 'S1',
        ]);

        // Assert
        $this->assertFalse($secondInvoice->is_first_invoice);
        $this->assertEquals('F-2025-001', $secondInvoice->previous_invoice_number);
        $this->assertEquals($firstInvoice->hash, $secondInvoice->previous_invoice_hash);
        $this->assertNotNull($secondInvoice->hash);
        $this->assertNotEquals($firstInvoice->hash, $secondInvoice->hash);
    }

    /** @test */
    public function it_maintains_chain_integrity()
    {
        $invoices = [];
        
        // Crear cadena de 5 facturas
        for ($i = 1; $i <= 5; $i++) {
            $isFirst = ($i === 1);
            $previous = $isFirst ? null : $invoices[$i - 2];

            $invoice = Invoice::factory()->create([
                'number' => sprintf('F-2025-%03d', $i),
                'issuer_tax_id' => 'B12345678',
                'type' => 'F1',
                'is_first_invoice' => $isFirst,
                'previous_invoice_number' => $previous?->number,
                'previous_invoice_date' => $previous?->date,
                'previous_invoice_hash' => $previous?->hash,
            ]);

            Breakdown::factory()->create([
                'invoice_id' => $invoice->id,
                'tax_type' => '01', // IVA
                'regime_type' => '01', // General
                'operation_type' => 'S1',
            ]);

            $invoices[] = $invoice;
        }

        // Assert: Verificar integridad de la cadena
        $this->assertTrue($invoices[0]->is_first_invoice);
        
        for ($i = 1; $i < 5; $i++) {
            $this->assertFalse($invoices[$i]->is_first_invoice);
            $this->assertEquals($invoices[$i - 1]->number, $invoices[$i]->previous_invoice_number);
            $this->assertEquals($invoices[$i - 1]->hash, $invoices[$i]->previous_invoice_hash);
        }
    }

    /** @test */
    public function it_includes_previous_hash_in_current_hash_calculation()
    {
        $firstInvoice = Invoice::factory()->create([
            'number' => 'F-2025-001',
            'issuer_tax_id' => 'B12345678',
            'type' => 'F1',
            'is_first_invoice' => true,
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        $secondInvoice = Invoice::factory()->create([
            'number' => 'F-2025-002',
            'issuer_tax_id' => 'B12345678',
            'type' => 'F1',
            'is_first_invoice' => false,
            'previous_invoice_hash' => $firstInvoice->hash,
            'tax' => 21.00,
            'total' => 121.00,
        ]);

        // El hash debe ser diferente aunque los datos sean iguales
        // porque incluye el previous_hash
        $this->assertNotEquals($firstInvoice->hash, $secondInvoice->hash);
    }
}

