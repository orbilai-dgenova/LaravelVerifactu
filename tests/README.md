# Tests - LaravelVerifactu

Este directorio contiene todos los tests unitarios del package.

## Estadísticas

- **99 tests unitarios**
- **291 assertions**
- **100% cobertura de escenarios fiscales españoles**

## Estructura

```
tests/
├── TestCase.php                          # Clase base para todos los tests
├── fixtures/                             # Archivos de prueba (certificados dummy)
└── Unit/
    ├── Scenarios/                        # Tests de casos de uso reales
    │   ├── StandardInvoiceTest.php       # Factura estándar con IVA
    │   ├── SimplifiedInvoiceTest.php     # Facturas simplificadas (sin destinatario)
    │   ├── SubstituteInvoiceTest.php     # Facturas de sustitución
    │   ├── IgicInvoiceTest.php           # Facturas con IGIC (Canarias)
    │   ├── IpsiInvoiceTest.php           # Facturas con IPSI (Ceuta/Melilla)
    │   ├── RectificativeInvoiceTest.php  # Facturas rectificativas (Notas crédito)
    │   ├── ChainedInvoicesTest.php       # Encadenamiento (Blockchain)
    │   ├── OssRegimeInvoiceTest.php      # Régimen OSS (One Stop Shop UE)
    │   ├── SubsanacionInvoiceTest.php    # Reenvío tras rechazo AEAT
    │   ├── ExportOperationsTest.php      # Exportaciones fuera UE
    │   ├── ExemptOperationsTest.php      # Operaciones exentas (E1-E6)
    │   ├── ReverseChargeTest.php         # Inversión del sujeto pasivo
    │   ├── EquivalenceSurchargeTest.php  # Recargo de equivalencia
    │   ├── CashCriterionTest.php         # Criterio de caja
    │   └── ReagypRegimeTest.php          # Régimen REAGYP (agrícola)
    ├── AeatClientTest.php                # Tests del cliente AEAT
    ├── AeatResponseValidationTest.php    # Validación respuestas AEAT
    ├── XmlValidationTest.php             # Validación XML contra XSD
    ├── XmlElementOrderTest.php           # Orden de elementos XSD
    ├── InvoiceModelTest.php              # Tests del modelo Invoice
    ├── BreakdownModelTest.php            # Tests del modelo Breakdown
    ├── RecipientModelTest.php            # Tests del modelo Recipient
    ├── HashHelperTest.php                # Tests del helper de hash
    ├── HashHelperAeatComplianceTest.php  # Cumplimiento hash AEAT
    ├── DateTimeHelperTest.php            # Tests de formato de fechas
    └── StringHelperTest.php              # Tests de utilidades string
```

## Configuración

### Base de Datos

Los tests usan **SQLite en memoria** (`:memory:`):
- ✅ No requiere configuración adicional
- ✅ Rápido y aislado
- ✅ Se crea y destruye automáticamente
- ✅ No afecta a ninguna base de datos real

La configuración está en `phpunit.xml`:
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### AEAT

Los tests **NO interactúan con AEAT real**:
- Usan certificados dummy en `fixtures/`
- Mockean respuestas HTTP cuando es necesario
- Validan solo estructura de datos y XML

## Ejecutar Tests

### Todos los tests
```bash
vendor/bin/phpunit
```

### Solo tests de escenarios
```bash
vendor/bin/phpunit --testsuite Unit --filter Scenarios
```

### Un test específico
```bash
vendor/bin/phpunit --filter it_creates_valid_standard_invoice_with_iva
```

### Con coverage (requiere Xdebug)
```bash
vendor/bin/phpunit --coverage-html coverage
```

### Con output detallado
```bash
vendor/bin/phpunit --testdox
```

## Casos de Uso Cubiertos

### Tipos de Factura

| Test | Descripción |
|------|-------------|
| **StandardInvoiceTest** | Factura estándar con IVA régimen general |
| **SimplifiedInvoiceTest** | Facturas simplificadas sin destinatario obligatorio |
| **SubstituteInvoiceTest** | Facturas de sustitución (F3) |
| **RectificativeInvoiceTest** | Notas de crédito por diferencia y sustitución |

### Impuestos Territoriales

| Test | Descripción |
|------|-------------|
| **StandardInvoiceTest** | IVA península (21%, 10%, 4%) |
| **IgicInvoiceTest** | IGIC Canarias (7%, 3%, 0%) |
| **IpsiInvoiceTest** | IPSI Ceuta y Melilla (10%, 4%, 1%) |

### Regímenes Especiales

| Test | Descripción |
|------|-------------|
| **OssRegimeInvoiceTest** | One Stop Shop para ventas UE B2C |
| **EquivalenceSurchargeTest** | Recargo de equivalencia (5.2%, 1.4%, 0.5%) |
| **CashCriterionTest** | Régimen especial de criterio de caja |
| **ReagypRegimeTest** | Régimen especial agrícola (REAGYP) |

### Operaciones Especiales

| Test | Descripción |
|------|-------------|
| **ExportOperationsTest** | Exportaciones fuera UE (N1) |
| **ExemptOperationsTest** | Operaciones exentas (E1-E6): educación, sanidad, etc. |
| **ReverseChargeTest** | Inversión del sujeto pasivo: construcción, oro, chatarra |

### Funcionalidades Avanzadas

| Test | Descripción |
|------|-------------|
| **ChainedInvoicesTest** | Encadenamiento blockchain de facturas |
| **SubsanacionInvoiceTest** | Reenvío tras rechazo AEAT |
| **AeatResponseValidationTest** | Validación de respuestas AEAT (CSV, errores) |
| **XmlValidationTest** | Estructura XML válida según XSD |
| **XmlElementOrderTest** | Orden estricto de elementos según XSD AEAT |

### Modelos y Helpers

| Test | Descripción |
|------|-------------|
| **InvoiceModelTest** | CRUD y relaciones del modelo Invoice |
| **BreakdownModelTest** | Desgloses impositivos |
| **RecipientModelTest** | Destinatarios nacionales y extranjeros |
| **HashHelperTest** | Generación de hash SHA-256 |
| **HashHelperAeatComplianceTest** | Cumplimiento especificación hash AEAT |
| **DateTimeHelperTest** | Formato de fechas ISO 8601 y dd-mm-yyyy |
| **StringHelperTest** | Sanitización y escape XML |

## Buenas Prácticas

1. **Usa factories** para crear datos de prueba
2. **Aísla cada test** - no dependas de orden de ejecución
3. **Nombres descriptivos** - `it_creates_valid_standard_invoice_with_iva`
4. **Arrange-Act-Assert** - estructura clara
5. **SQLite en memoria** - rápido y sin efectos secundarios

## Debugging

Si un test falla:

1. **Ver el SQL generado:**
   ```php
   \DB::enableQueryLog();
   // ... código del test
   dd(\DB::getQueryLog());
   ```

2. **Inspeccionar modelos:**
   ```php
   dd($invoice->toArray());
   ```

3. **Ver XML generado:**
   ```php
   echo $xml;
   exit;
   ```

## Contribuir

Al añadir nuevos tests:
1. Sigue la estructura existente
2. Añade comentarios explicativos
3. Usa valores realistas (NIFs válidos en formato)
4. Documenta el caso de uso en el docblock
5. Verifica el orden de elementos XML según XSD

