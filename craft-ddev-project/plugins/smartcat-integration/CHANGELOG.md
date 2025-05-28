# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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