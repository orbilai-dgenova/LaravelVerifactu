<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrige el índice único de facturas.
 * 
 * PROBLEMA:
 * El índice único original era solo por `number`, lo cual impedía que
 * diferentes emisores (CIFs) tuvieran el mismo número de factura.
 * 
 * SOLUCIÓN:
 * El índice único debe ser por par (issuer_tax_id, number) porque:
 * - Un mismo CIF NO puede tener números de factura duplicados
 * - Diferentes CIFs SÍ pueden tener el mismo número de factura
 * 
 * Esto es correcto para un conector multi-tenant donde cada cliente
 * tiene su propia numeración de facturas.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Eliminar el índice único solo por number
            $table->dropUnique(['number']);
            
            // Crear índice único compuesto (issuer_tax_id + number)
            $table->unique(['issuer_tax_id', 'number'], 'invoices_issuer_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Revertir: eliminar índice compuesto
            $table->dropUnique('invoices_issuer_number_unique');
            
            // Restaurar índice único solo por number
            $table->unique('number');
        });
    }
};

