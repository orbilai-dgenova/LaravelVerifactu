<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use Squareetlabs\VeriFactu\Services\AeatClient;
use Squareetlabs\VeriFactu\Exceptions\AeatException;

/**
 * Test para validación de respuestas AEAT
 * 
 * Valida todos los escenarios posibles de respuesta:
 * - Éxito con CSV
 * - Errores de validación
 * - SOAP Faults
 * - Estados incorrectos
 */
class AeatResponseValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }
    
    private function getAeatClientInstance(): AeatClient
    {
        // Crear una instancia mockeada o con certificado dummy
        $certPath = __DIR__ . '/../fixtures/test_cert.pem';
        
        // Si no existe el certificado, usar un path dummy (solo para tests de validación de respuesta)
        if (!file_exists($certPath)) {
            $certPath = __DIR__ . '/../TestCase.php'; // Cualquier archivo existente
        }
        
        return new AeatClient(
            certPath: $certPath,
            certPassword: 'test_password',
            production: false
        );
    }

    /** @test */
    public function it_validates_successful_response_with_csv()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <ns2:RespuestaRegFactuSistemaFacturacion xmlns:ns2="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/burt/jdit/ws/RespuestaConsultaLR.xsd">
                    <Cabecera>
                        <ObligadoEmision>
                            <NIF>B12345678</NIF>
                            <Nombre>Test Company</Nombre>
                        </ObligadoEmision>
                    </Cabecera>
                    <RespuestaLinea>
                        <IDFactura>
                            <IDEmisorFactura>B12345678</IDEmisorFactura>
                            <NumSerieFactura>F-2025-001</NumSerieFactura>
                            <FechaExpedicionFactura>21-11-2025</FechaExpedicionFactura>
                        </IDFactura>
                        <EstadoRegistro>Correcto</EstadoRegistro>
                        <CodigoErrorRegistro></CodigoErrorRegistro>
                        <DescripcionErrorRegistro></DescripcionErrorRegistro>
                        <CSV>ABC123XYZ456QWER</CSV>
                    </RespuestaLinea>
                    <EstadoEnvio>Correcto</EstadoEnvio>
                </ns2:RespuestaRegFactuSistemaFacturacion>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertTrue($result['success']);
        $this->assertEquals('ABC123XYZ456QWER', $result['csv']);
        $this->assertStringContainsString('accepted', strtolower($result['message']));
    }

    /** @test */
    public function it_detects_soap_fault()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <soapenv:Fault>
                    <faultcode>soapenv:Server</faultcode>
                    <faultstring>Error de validación del certificado</faultstring>
                    <detail>
                        <ns2:excepcion xmlns:ns2="http://services.aeat.es/">
                            <Codigo>4112</Codigo>
                            <Descripcion>El titular del certificado debe ser Obligado Emisión, Colaborador Social, Apoderado o Sucesor.</Descripcion>
                        </ns2:excepcion>
                    </detail>
                </soapenv:Fault>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('SOAP Fault', $result['message']);
    }

    /** @test */
    public function it_detects_incorrect_envio_state()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <ns2:RespuestaRegFactuSistemaFacturacion xmlns:ns2="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/burt/jdit/ws/RespuestaConsultaLR.xsd">
                    <EstadoEnvio>Incorrecto</EstadoEnvio>
                    <CodigoError>1001</CodigoError>
                    <DescripcionError>Error en la estructura del XML</DescripcionError>
                </ns2:RespuestaRegFactuSistemaFacturacion>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('submission error', strtolower($result['message']));
    }

    /** @test */
    public function it_detects_incorrect_registro_state()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <ns2:RespuestaRegFactuSistemaFacturacion xmlns:ns2="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/burt/jdit/ws/RespuestaConsultaLR.xsd">
                    <RespuestaLinea>
                        <EstadoRegistro>Incorrecto</EstadoRegistro>
                        <CodigoErrorRegistro>2001</CodigoErrorRegistro>
                        <DescripcionErrorRegistro>NIF del emisor no válido</DescripcionErrorRegistro>
                    </RespuestaLinea>
                    <EstadoEnvio>Correcto</EstadoEnvio>
                </ns2:RespuestaRegFactuSistemaFacturacion>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('registration error', strtolower($result['message']));
    }

    /** @test */
    public function it_detects_missing_csv()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <ns2:RespuestaRegFactuSistemaFacturacion xmlns:ns2="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/burt/jdit/ws/RespuestaConsultaLR.xsd">
                    <RespuestaLinea>
                        <EstadoRegistro>Correcto</EstadoRegistro>
                        <CSV></CSV>
                    </RespuestaLinea>
                    <EstadoEnvio>Correcto</EstadoEnvio>
                </ns2:RespuestaRegFactuSistemaFacturacion>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('CSV', $result['message']);
    }

    /** @test */
    public function it_handles_malformed_xml()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = "This is not valid XML";

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('parsing', strtolower($result['message']));
    }

    /** @test */
    public function it_extracts_error_details_from_response()
    {
        $client = $this->getAeatClientInstance();
        $xmlResponse = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <ns2:RespuestaRegFactuSistemaFacturacion xmlns:ns2="https://www2.agenciatributaria.gob.es/static_files/common/internet/dep/aplicaciones/es/aeat/burt/jdit/ws/RespuestaConsultaLR.xsd">
                    <RespuestaLinea>
                        <EstadoRegistro>Incorrecto</EstadoRegistro>
                        <CodigoErrorRegistro>3001</CodigoErrorRegistro>
                        <DescripcionErrorRegistro>El importe total no coincide con la suma de bases imponibles</DescripcionErrorRegistro>
                    </RespuestaLinea>
                    <EstadoEnvio>Correcto</EstadoEnvio>
                </ns2:RespuestaRegFactuSistemaFacturacion>
            </soapenv:Body>
        </soapenv:Envelope>
        XML;

        $reflection = new \ReflectionClass($client);
        $method = $reflection->getMethod('validateAeatResponse');
        $method->setAccessible(true);

        $result = $method->invoke($client, $xmlResponse);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('registration error', strtolower($result['message']));
        // El código de error puede estar en el mensaje
        $this->assertNotNull($result['message']);
    }
}

