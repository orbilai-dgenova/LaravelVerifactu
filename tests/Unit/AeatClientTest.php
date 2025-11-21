<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Squareetlabs\VeriFactu\Services\AeatClient;
use Squareetlabs\VeriFactu\Models\Invoice;
use Squareetlabs\VeriFactu\Models\Breakdown;
use Squareetlabs\VeriFactu\Models\Recipient;
use Squareetlabs\VeriFactu\Enums\InvoiceType;
use Squareetlabs\VeriFactu\Enums\TaxType;
use Squareetlabs\VeriFactu\Enums\RegimeType;
use Squareetlabs\VeriFactu\Enums\OperationType;

class AeatClientTest extends TestCase
{
    use RefreshDatabase;

    public function testAeatClientCanBeConfigured(): void
    {
        $client = new AeatClient('/path/to/cert.pem', 'password', false);
        $this->assertInstanceOf(AeatClient::class, $client);
    }

    public function testSendInvoiceWithoutXadesReturnsError(): void
    {
        // Prepara datos de test
        $invoice = Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'number' => 'TST-001',
            'date' => now(),
            'customer_name' => 'Test Customer',
            'customer_tax_id' => '12345678A',
            'issuer_name' => 'Issuer Test',
            'issuer_tax_id' => 'B12345678',
            'amount' => 100,
            'tax' => 21,
            'total' => 121,
            'type' => InvoiceType::STANDARD,
            'is_first_invoice' => true,
        ]);
        $invoice->breakdowns()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'tax_type' => TaxType::VAT,
            'regime_type' => RegimeType::GENERAL,
            'operation_type' => OperationType::SUBJECT_NO_EXEMPT_NO_REVERSE,
            'tax_rate' => 21,
            'base_amount' => 100,
            'tax_amount' => 21,
        ]);
        $invoice->recipients()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Customer',
            'tax_id' => '12345678A',
            'country' => 'ES',
        ]);

        // Client without XAdES service (should return error)
        $certPath = storage_path('certificates/mock-cert.pem');
        $certPassword = 'password';
        $production = false;
        $client = new AeatClient($certPath, $certPassword, $production); // No XAdES service

        $result = $client->sendInvoice($invoice);
        
        // Should return error due to missing XAdES service
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('error', $result['status']);
        $this->assertStringContainsString('XAdES', $result['message']);
    }

    public function testSendInvoiceWithMockedXadesAndHttpReturnsSuccess(): void
    {
        // Mock HTTP to avoid real AEAT calls
        Http::fake([
            '*' => Http::response('<?xml version="1.0" encoding="UTF-8"?>
                <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Body>
                        <RespuestaRegFactuSistemaFacturacion>
                            <Cabecera>
                                <EstadoEnvio>Correcto</EstadoEnvio>
                            </Cabecera>
                            <RegistroFacturacion>
                                <EstadoRegistro>Correcto</EstadoRegistro>
                                <CSV>ABC123XYZ456QWER</CSV>
                            </RegistroFacturacion>
                        </RespuestaRegFactuSistemaFacturacion>
                    </soapenv:Body>
                </soapenv:Envelope>', 200),
        ]);

        // Mock XAdES signature service
        $mockXadesService = new class {
            public function signXml(string $xml): string {
                return $xml; // Return same XML (no real signing)
            }
        };

        // Prepara datos de test
        $invoice = Invoice::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'number' => 'TST-002',
            'date' => now(),
            'customer_name' => 'Test Customer',
            'customer_tax_id' => '12345678A',
            'issuer_name' => 'Issuer Test',
            'issuer_tax_id' => 'B12345678',
            'amount' => 100,
            'tax' => 21,
            'total' => 121,
            'type' => InvoiceType::STANDARD,
            'is_first_invoice' => true,
        ]);
        $invoice->breakdowns()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'tax_type' => TaxType::VAT,
            'regime_type' => RegimeType::GENERAL,
            'operation_type' => OperationType::SUBJECT_NO_EXEMPT_NO_REVERSE,
            'tax_rate' => 21,
            'base_amount' => 100,
            'tax_amount' => 21,
        ]);
        $invoice->recipients()->create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'name' => 'Test Customer',
            'tax_id' => '12345678A',
            'country' => 'ES',
        ]);

        // Client with mocked XAdES service
        $certPath = storage_path('certificates/mock-cert.pem');
        $certPassword = 'password';
        $production = false;
        $client = new AeatClient($certPath, $certPassword, $production, $mockXadesService);

        $result = $client->sendInvoice($invoice);
        
        // Should return success (HTTP mocked + XAdES mocked)
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('request', $result);
        $this->assertArrayHasKey('response', $result);
        $this->assertArrayHasKey('csv', $result);
        $this->assertEquals('ABC123XYZ456QWER', $result['csv']);
        
        // Verify HTTP was called
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'aeat.es');
        });
    }
} 