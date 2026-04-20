# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Support for Craft CMS 5.8+ `ContentBlock` field type. The field is detected as `typeName: "contentblock"` and carries a new `contentBlockFields` array on the field-type entry. Each item in that array is a **reference** to an underlying field (not a field type of its own) with the shape:
  - `handle` — reference handle (key used in the block's data payload)
  - `label` — reference label (display name for this occurrence)
  - `fieldHandle` — handle of the underlying field; look it up in the top-level `fieldTypes` collection for the field's `typeName`, `isLocalizable`, nested `matrixEntryTypes` / `neoBlockTypes` / `contentBlockFields`, etc.
  - `required` — whether this reference is marked required in the layout
  Because Craft 5 lets a `CustomField` layout element override the underlying field's handle and label, the same field referenced multiple times inside one ContentBlock produces one entry per reference under its own reference handle, all pointing to the same `fieldHandle`. Every underlying field touched by a ContentBlock is also registered in the top-level `fieldTypes` collection so reference lookups always resolve. Non-ContentBlock fields carry `contentBlockFields: []` for shape consistency. Works at the top level and recursively inside Matrix, Neo, and other ContentBlocks.

## [1.0.0] - 2024-01-01

### Added
- Initial release of Smartcat Integration plugin
- `/api/fields` endpoint for retrieving field information
- Support for multiple entity types:
  - Entries (with section and entry type context)
  - Categories (with category group context)
  - Assets (with volume context)
  - Users
  - Global Sets
- Field localization detection
- Standardized field type mapping
- Comprehensive error handling
- Anonymous access support for API endpoints
- Default field inclusion for all entity types
- Documentation and examples

### Features
- **Field Information API**: Complete field metadata retrieval
- **Multi-Entity Support**: Covers all major Craft CMS content types
- **Localization Detection**: Automatically identifies translatable fields
- **Type Standardization**: Maps Craft field types to consistent API types
- **Context Information**: Includes section, entry type, category group, and volume context where applicable

### Security
- Read-only API access
- No sensitive content exposure
- Configurable anonymous access 