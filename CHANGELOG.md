# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [Unreleased]

### Añadido
- ✅ Cliente AEAT con comunicación XML y validación completa de respuestas
- ✅ Soporte para múltiples tipos de impuestos (IVA, IGIC, IPSI)
- ✅ Régimen OSS (One Stop Shop) para ventas intracomunitarias
- ✅ Encadenamiento blockchain de facturas (campos `previous_invoice_*`, `is_first_invoice`)
- ✅ Facturas rectificativas avanzadas (campos `rectificative_type`, `rectified_invoices`, `rectification_amount`)
- ✅ Subsanación de facturas rechazadas (campos `is_subsanacion`, `rejected_invoice_number`, `rejection_date`)
- ✅ Campo `csv` en invoices para almacenar el código de verificación AEAT
- ✅ Campo `operation_date` para fecha de operación distinta a fecha de expedición
- ✅ Campos dinámicos en breakdowns: `tax_type`, `regime_type`, `operation_type`
- ✅ Configuración `sistema_informatico` completa en `config/verifactu.php`
- ✅ 54 tests unitarios con SQLite in-memory
- ✅ Tests de escenarios: estándar, IGIC, rectificativas, encadenadas, OSS, subsanación
- ✅ Tests de validación de respuestas AEAT
- ✅ Tests de validación XML contra XSD oficiales
- ✅ Esquemas XSD oficiales AEAT incluidos en `docs/aeat-schemas/`
- ✅ Documentación completa de tests en `tests/README.md`
- ✅ Fixtures para datos de prueba

### Cambiado
- 🔄 `AeatClient` refactorizado para usar Laravel HTTP Client
- 🔄 Validación de respuestas AEAT mejorada (EstadoEnvio + EstadoRegistro + CSV)
- 🔄 Dependencia `XadesSignatureInterface` ahora opcional (mayor flexibilidad)
- 🔄 Migraciones actualizadas para soportar campos avanzados
- 🔄 README actualizado con ejemplos de todos los tipos de facturas
- 🔄 Configuración ampliada con `sistema_informatico`

### Corregido
- 🐛 Validación correcta de respuestas AEAT (HTTP 200 no garantiza aceptación)
- 🐛 Generación de hash compatible con encadenamiento
- 🐛 Manejo de errores de conexión y timeouts

## [1.0.0] - 2024-01-01

### Añadido
- Versión inicial del package
- Modelos Eloquent: Invoice, Breakdown, Recipient
- Enums: InvoiceType, TaxType, RegimeType, OperationType, ForeignIdType
- Helpers: HashHelper, DateTimeHelper, StringHelper
- Form Requests y API Resources
- Factories para testing
- Tests básicos

---

[Unreleased]: https://github.com/squareetlabs/LaravelVerifactu/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/squareetlabs/LaravelVerifactu/releases/tag/v1.0.0

