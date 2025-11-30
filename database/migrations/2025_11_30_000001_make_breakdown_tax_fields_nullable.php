<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hace nullable los campos tax_rate y tax_amount en breakdowns.
 * 
 * MOTIVO:
 * Para operaciones N1/N2 (no sujetas) y E1-E6 (exentas),
 * la AEAT NO permite informar TipoImpositivo ni CuotaRepercutida.
 * Estos campos deben ser NULL en la BD para estas operaciones.
 * 
 * @see AeatClient.php - lógica de omisión de campos
 * @see InvoiceOrchestrator.php - processBreakdownsTaxType()
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('breakdowns', function (Blueprint $table) {
            $table->decimal('tax_rate', 6, 2)->nullable()->change();
            $table->decimal('tax_amount', 15, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('breakdowns', function (Blueprint $table) {
            $table->decimal('tax_rate', 6, 2)->nullable(false)->change();
            $table->decimal('tax_amount', 15, 2)->nullable(false)->change();
        });
    }
};

