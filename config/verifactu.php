<?php

return [
    'enabled' => true,
    'default_currency' => 'EUR',
    
    'issuer' => [
        'name' => env('VERIFACTU_ISSUER_NAME', ''),
        'vat' => env('VERIFACTU_ISSUER_VAT', ''),
    ],
    
    // 🔒 CONFIGURACIÓN AEAT
    'aeat' => [
        'cert_path' => env('VERIFACTU_CERT_PATH', storage_path('certificates/aeat.pem')),
        'cert_password' => env('VERIFACTU_CERT_PASSWORD'),
        
        // ⚠️ PRODUCCIÓN DESHABILITADA
        // El sistema está configurado para SOLO usar entorno de PRUEBAS de AEAT
        // Esta configuración se ignora actualmente en AeatClient.php
        // Se habilitará cuando se indique expresamente
        'production' => false, // SIEMPRE FALSE - No cambiar
    ],
    
    // Otros parámetros de configuración...
]; 