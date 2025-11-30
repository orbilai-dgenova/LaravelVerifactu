<?php

declare(strict_types=1);

namespace Squareetlabs\VeriFactu\Enums;

enum OperationType: string
{
    // Operaciones sujetas y no exentas
    case SUBJECT_NO_EXEMPT_NO_REVERSE = 'S1';
    case SUBJECT_NO_EXEMPT_REVERSE = 'S2';
    
    // Operaciones no sujetas
    case NOT_SUBJECT_ARTICLES = 'N1';
    case NOT_SUBJECT_LOCALIZATION = 'N2';
    
    // Operaciones exentas (Art. 20-25 LIVA) - Códigos oficiales AEAT
    case EXEMPT_ART_20 = 'E1';       // Art. 20 LIVA (exenciones interiores)
    case EXEMPT_ART_21 = 'E2';       // Art. 21 LIVA (exportaciones)
    case EXEMPT_ART_22 = 'E3';       // Art. 22 LIVA (operaciones asimiladas exportaciones)
    case EXEMPT_ART_23_24 = 'E4';    // Art. 23 y 24 LIVA (zonas francas, regímenes suspensivos)
    case EXEMPT_ART_25 = 'E5';       // Art. 25 LIVA (entregas intracomunitarias)
    case EXEMPT_OTHER = 'E6';        // Exenta por otras causas

    public function description(): string
    {
        return match($this) {
            self::SUBJECT_NO_EXEMPT_NO_REVERSE => 'Sujeta y no exenta - Sin inversión sujeto pasivo',
            self::SUBJECT_NO_EXEMPT_REVERSE => 'Sujeta y no exenta - Con inversión sujeto pasivo',
            self::NOT_SUBJECT_ARTICLES => 'No sujeta - Art. 7, 14 y otros',
            self::NOT_SUBJECT_LOCALIZATION => 'No sujeta por reglas de localización',
            self::EXEMPT_ART_20 => 'Exenta Art. 20 LIVA (exenciones interiores)',
            self::EXEMPT_ART_21 => 'Exenta Art. 21 LIVA (exportaciones)',
            self::EXEMPT_ART_22 => 'Exenta Art. 22 LIVA (asimiladas a exportaciones)',
            self::EXEMPT_ART_23_24 => 'Exenta Art. 23/24 LIVA (zonas francas)',
            self::EXEMPT_ART_25 => 'Exenta Art. 25 LIVA (intracomunitarias)',
            self::EXEMPT_OTHER => 'Exenta por otras causas',
        };
    }
    
    /**
     * Determina si esta operación es exenta o no sujeta (sin TipoImpositivo/CuotaRepercutida).
     */
    public function isExemptOrNotSubject(): bool
    {
        return in_array($this, [
            self::NOT_SUBJECT_ARTICLES,
            self::NOT_SUBJECT_LOCALIZATION,
            self::EXEMPT_ART_20,
            self::EXEMPT_ART_21,
            self::EXEMPT_ART_22,
            self::EXEMPT_ART_23_24,
            self::EXEMPT_ART_25,
            self::EXEMPT_OTHER,
        ]);
    }
} 