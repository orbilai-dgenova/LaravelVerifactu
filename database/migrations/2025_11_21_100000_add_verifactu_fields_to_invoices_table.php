<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // CSV de AEAT
            $table->string('csv', 16)->nullable()->index()->after('hash');
            
            // Encadenamiento de facturas
            $table->string('previous_invoice_number', 60)->nullable()->after('csv');
            $table->date('previous_invoice_date')->nullable()->after('previous_invoice_number');
            $table->string('previous_invoice_hash', 64)->nullable()->after('previous_invoice_date');
            $table->boolean('is_first_invoice')->default(true)->after('previous_invoice_hash');
            
            // Facturas rectificativas
            $table->string('rectificative_type', 1)->nullable()->after('is_first_invoice');
            $table->json('rectified_invoices')->nullable()->after('rectificative_type');
            $table->json('rectification_amount')->nullable()->after('rectified_invoices');
            
            // Campos opcionales AEAT
            $table->date('operation_date')->nullable()->after('rectification_amount');
            $table->boolean('is_subsanacion')->default(false)->after('operation_date');
            $table->string('rejected_invoice_number', 60)->nullable()->after('is_subsanacion');
            $table->date('rejection_date')->nullable()->after('rejected_invoice_number');
            
            // Índices
            $table->index('previous_invoice_number');
            $table->index('is_first_invoice');
            $table->index('rectificative_type');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['previous_invoice_number']);
            $table->dropIndex(['is_first_invoice']);
            $table->dropIndex(['rectificative_type']);
            $table->dropIndex(['csv']);
            
            $table->dropColumn([
                'csv',
                'previous_invoice_number',
                'previous_invoice_date',
                'previous_invoice_hash',
                'is_first_invoice',
                'rectificative_type',
                'rectified_invoices',
                'rectification_amount',
                'operation_date',
                'is_subsanacion',
                'rejected_invoice_number',
                'rejection_date',
            ]);
        });
    }
};

