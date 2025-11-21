<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            \Squareetlabs\VeriFactu\Providers\VeriFactuServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configurar SQLite en memoria para tests
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Configuración del package para tests
        $app['config']->set('verifactu.issuer', [
            'name' => 'Test Company SL',
            'vat' => 'B12345678',
        ]);

        $app['config']->set('verifactu.aeat', [
            'cert_path' => __DIR__ . '/fixtures/test_cert.pem',
            'cert_password' => null,
            'production' => false,
        ]);

        $app['config']->set('verifactu.sistema_informatico', [
            'name' => 'LaravelVerifactu Test',
            'nif' => 'B12345678',
            'software_name' => 'LaravelVerifactu',
            'software_id' => 'TEST001',
            'version' => '1.0.0',
            'installation_number' => '001',
            'solo_verifactu' => true,
            'multi_ot' => false,
            'multi_ot_indicator' => false,
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        // Ejecutar migraciones del package
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
} 