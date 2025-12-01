# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [2.0.0] - 2025-12-01

### Añadido

#### Cliente AEAT
- ✅ Cliente AEAT con comunicación SOAP/XML completa
- ✅ Validación de respuestas AEAT (EstadoEnvio + EstadoRegistro + CSV)
- ✅ Manejo de errores de conexión, timeouts y SOAP Faults
- ✅ Soporte para modo producción y pruebas (sandbox)
- ✅ Extracción automática del código CSV de verificación

#### Tipos de Impuestos
- ✅ IVA península (21%, 10%, 4%, 0%)
- ✅ IGIC Canarias (7%, 3%, 0%)
- ✅ IPSI Ceuta y Melilla (10%, 4%, 1%, 0%)

#### Regímenes Especiales
- ✅ Régimen OSS (One Stop Shop) para ventas intracomunitarias B2C
- ✅ Recargo de equivalencia (5.2%, 1.4%, 0.5%)
- ✅ Criterio de caja
- ✅ Régimen especial agrícola REAGYP

#### Tipos de Operación
- ✅ Operaciones sujetas (S1, S2)
- ✅ Operaciones no sujetas (N1, N2)
- ✅ Operaciones exentas (E1-E6): educación, sanidad, exportaciones, etc.
- ✅ Inversión del sujeto pasivo: construcción, oro, chatarra, electrónica

#### Tipos de Factura
- ✅ Facturas estándar (F1)
- ✅ Facturas simplificadas (F2) sin destinatario obligatorio
- ✅ Facturas de sustitución (F3)
- ✅ Facturas rectificativas (R1-R5) por diferencia y sustitución
- ✅ Soporte para múltiples facturas rectificadas

#### Funcionalidades Avanzadas
- ✅ Encadenamiento blockchain de facturas (`previous_invoice_*`, `is_first_invoice`)
- ✅ Subsanación de facturas rechazadas (`is_subsanacion`, `rejected_invoice_number`)
- ✅ Campo `csv` para almacenar código de verificación AEAT
- ✅ Campo `operation_date` para fecha de operación distinta a expedición
- ✅ Campos de estado AEAT (`aeat_estado_registro`, `aeat_codigo_error`, `aeat_descripcion_error`)
- ✅ Soporte para destinatarios extranjeros con `IDOtro` (NIF-IVA, pasaporte, etc.)

#### Configuración
- ✅ Configuración `sistema_informatico` completa en `config/verifactu.php`
- ✅ Soporte para Representante (modelo SaaS)
- ✅ Campo `numero_instalacion` por cliente/instalación

#### Tests
- ✅ 99 tests unitarios con SQLite in-memory
- ✅ 291 assertions
- ✅ Tests de escenarios: estándar, simplificadas, IGIC, IPSI, rectificativas, encadenadas, OSS, subsanación
- ✅ Tests de operaciones: exportaciones, exentas, inversión sujeto pasivo, recargo equivalencia
- ✅ Tests de validación de respuestas AEAT
- ✅ Tests de validación XML contra XSD oficiales
- ✅ Tests de orden de elementos XML (cumplimiento XSD estricto)

#### Documentación
- ✅ Esquemas XSD oficiales AEAT incluidos en `docs/aeat-schemas/`
- ✅ Documentación completa de tests en `tests/README.md`
- ✅ README actualizado con ejemplos de todos los tipos de facturas
- ✅ Fixtures para datos de prueba

### Cambiado
- 🔄 `AeatClient` refactorizado para usar Laravel HTTP Client
- 🔄 Validación de respuestas AEAT mejorada con detección de todos los estados posibles
- 🔄 Dependencia `XadesSignatureInterface` ahora opcional (modo VERIFACTU online no requiere firma XAdES)
- 🔄 Migraciones actualizadas para soportar campos avanzados y multitenancy
- 🔄 Orden de elementos XML corregido según XSD AEAT (crítico para aceptación)

### Corregido
- 🐛 Validación correcta de respuestas AEAT (HTTP 200 no garantiza aceptación)
- 🐛 Generación de hash compatible con encadenamiento
- 🐛 Manejo de errores de conexión y timeouts
- 🐛 Orden de elementos en `DetalleDesglose` según XSD (evita error 4102)
- 🐛 Exclusión mutua de `CalificacionOperacion` y `OperacionExenta`

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

[2.0.0]: https://github.com/squareetlabs/LaravelVerifactu/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/squareetlabs/LaravelVerifactu/releases/tag/v1.0.0

