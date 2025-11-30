<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade índices optimizados para consultas multi-tenant.
 * 
 * En un contexto multi-cliente donde cada issuer_tax_id es un cliente diferente,
 * estas son las consultas más frecuentes:
 * 
 * 1. Obtener facturas de un cliente: WHERE issuer_tax_id = ?
 * 2. Obtener facturas pendientes de un cliente: WHERE issuer_tax_id = ? AND status = ?
 * 3. Obtener facturas por fecha de un cliente: WHERE issuer_tax_id = ? AND date BETWEEN ? AND ?
 * 4. Buscar factura rectificada: WHERE issuer_tax_id = ? AND number = ?
 * 
 * Los índices compuestos mejoran significativamente el rendimiento de estas consultas.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Índice para queries por emisor (cliente)
            $table->index('issuer_tax_id', 'invoices_issuer_tax_id_index');
            
            // Índice compuesto para "facturas de un cliente con estado X"
            // Muy usado en: dashboards, reportes, reintentos
            $table->index(['issuer_tax_id', 'status'], 'invoices_issuer_status_index');
            
            // Índice compuesto para "facturas de un cliente en rango de fechas"
            // Muy usado en: reportes mensuales, consultas históricas
            $table->index(['issuer_tax_id', 'date'], 'invoices_issuer_date_index');
            
            // Índice para búsqueda de encadenamiento (factura anterior)
            // El encadenamiento debe buscar solo facturas del mismo emisor
            $table->index(['issuer_tax_id', 'previous_invoice_number'], 'invoices_issuer_prev_number_index');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_issuer_tax_id_index');
            $table->dropIndex('invoices_issuer_status_index');
            $table->dropIndex('invoices_issuer_date_index');
            $table->dropIndex('invoices_issuer_prev_number_index');
        });
    }
};

