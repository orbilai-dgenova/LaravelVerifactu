# Tests - LaravelVerifactu

Este directorio contiene todos los tests unitarios del package.

## Estructura

```
tests/
├── TestCase.php                          # Clase base para todos los tests
├── fixtures/                             # Archivos de prueba (certificados dummy)
└── Unit/
    ├── Scenarios/                        # Tests de casos de uso reales
    │   ├── StandardInvoiceTest.php       # Factura estándar con IVA
    │   ├── IgicInvoiceTest.php           # Facturas con IGIC (Canarias)
    │   ├── RectificativeInvoiceTest.php  # Facturas rectificativas (Notas crédito)
    │   ├── ChainedInvoicesTest.php       # Encadenamiento (Blockchain)
    │   ├── OssRegimeInvoiceTest.php      # Régimen OSS (One Stop Shop UE)
    │   └── SubsanacionInvoiceTest.php    # Reenvío tras rechazo AEAT
    ├── AeatResponseValidationTest.php    # Validación respuestas AEAT
    ├── XmlValidationTest.php             # Validación XML contra XSD
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

## Casos de Uso Cubiertos

### ✅ Implementados

1. **Factura Estándar (StandardInvoiceTest)**
   - IVA régimen general
   - Un solo tipo impositivo
   - Con destinatario

2. **IGIC Canarias (IgicInvoiceTest)**
   - Impuesto canario
   - Múltiples tipos (0%, 3%, 7%)

3. **Facturas Rectificativas (RectificativeInvoiceTest)**
   - Por diferencia (devolución parcial)
   - Por sustitución (anula completa)
   - Múltiples facturas rectificadas

4. **Encadenamiento (ChainedInvoicesTest)**
   - Primera factura (PrimerRegistro)
   - Facturas encadenadas (RegistroAnterior)
   - Integridad de cadena (hash)

5. **Régimen OSS (OssRegimeInvoiceTest)**
   - Ventas UE a consumidores finales
   - Múltiples países en una factura

6. **Subsanación (SubsanacionInvoiceTest)**
   - Reenvío tras rechazo AEAT
   - Marca correcta (Subsanacion=S)

7. **Validación Respuestas AEAT (AeatResponseValidationTest)**
   - Respuesta exitosa con CSV
   - SOAP Faults
   - Estados incorrectos
   - Errores de validación

8. **Validación XML (XmlValidationTest)**
   - Namespaces correctos
   - Estructura válida
   - Campos obligatorios
   - Formato de fechas
   - Escape de caracteres especiales

### 🔜 Próximos Tests

- Facturas sin destinatario (exportaciones)
- IPSI (Ceuta y Melilla)
- Régimen de agencias de viajes
- Régimen especial de recargo de equivalencia
- Operaciones intracomunitarias
- Inversión del sujeto pasivo
- Facturas simplificadas
- Facturas con retenciones

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

