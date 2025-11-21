<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para agregar campo CSV a la tabla invoices.
 * 
 * CSV = Código Seguro de Verificación devuelto por AEAT cuando acepta una factura.
 * 
 * Razones para agregar este campo:
 * - Auditoría: Prueba de que AEAT aceptó la factura
 * - Trazabilidad: Permite demostrar el envío exitoso
 * - Consultas: Generar URL de consulta en portal AEAT
 * - Servicio profesional: El cliente puede consultar su CSV
 * 
 * Política de retención actualizada:
 * - Las facturas exitosas YA NO se eliminan, se conservan con el CSV
 * - Esto permite consultas posteriores y auditoría completa
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // CSV: Código de 16 caracteres devuelto por AEAT
            // Ejemplo: "A-YDMH8YKB3VJXAZ"
            $table->string('csv', 16)
                ->nullable()
                ->after('hash')
                ->index()
                ->comment('Código Seguro de Verificación de AEAT');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['csv']);
            $table->dropColumn('csv');
        });
    }
};

