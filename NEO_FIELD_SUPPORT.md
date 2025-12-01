# Neo Field Support - Implementation Summary

## Overview

This document describes the implementation of Neo field support in the Smartcat Craft CMS Integration API. Neo fields are now fully exported with all nested block types and their fields.

## Changes Made

### 1. ApiController.php - New Methods

#### `isNeoField($field)`
Detects if a field is a Neo field by checking its class name for `benf\neo\Field`.

```php
private function isNeoField($field): bool
```

#### `getNeoFieldInfo($neoField)`
Extracts complete information about a Neo field including:
- All block types
- Fields within each block type
- Debug information for troubleshooting

```php
private function getNeoFieldInfo($neoField): array
```

#### `processNeoBlockType($blockType)`
Processes an individual Neo block type to extract:
- Block type metadata (handle, name, ID)
- Configuration metadata (enabled, description, child blocks, etc.)
- All fields within the block type
- Nested Neo/Matrix field type IDs

```php
private function processNeoBlockType($blockType): ?array
```

### 2. Field Type Detection Updates

#### Updated `getFieldTypeString($field)`
- Now checks for Neo fields first
- Returns 'neo' for Neo fields
- Maintains backward compatibility with all existing field types

#### Updated `getFieldsForSectionAndType()`
- Added Neo field detection alongside Matrix detection
- Exports `neoFieldInfo` when a Neo field is detected
- Includes debug information showing field type detection

### 3. Nested Field Support

The implementation handles complex nested structures:

- **Neo within Neo**: Detects nested Neo fields and exports their block type handles
- **Matrix within Neo**: Detects Matrix fields within Neo blocks
- **Neo within Matrix**: Detects Neo fields within Matrix blocks (via updated `getMatrixFieldInfoSimple`)

### 4. Documentation Updates

#### README.md Additions:
- Comprehensive Neo Fields section
- Example Neo field response structure
- Plugin Compatibility section
- Updated field type mapping table

## Neo Field Response Structure

When a Neo field is detected, the API response includes:

```json
{
  "fieldName": "neoFieldHandle",
  "displayName": "Neo Field Display Name",
  "isLocalizable": true,
  "type": "neo",
  "debugInfo": {
    "fieldClass": "benf\\neo\\Field",
    "isMatrixField": false,
    "isNeoField": true,
    "fieldHandle": "neoFieldHandle"
  },
  "neoFieldInfo": {
    "fieldInfo": {
      "childFields": [
        {
          "fieldType": "blockType",
          "fieldName": "blockTypeHandle",
          "displayName": "Block Type Name",
          "typeIds": ["blockTypeHandle"]
        }
      ]
    },
    "blockTypes": [
      {
        "typeHandle": "blockTypeHandle",
        "typeName": "Block Type Name",
        "typeId": 1,
        "childFields": [
          {
            "fieldType": "richtext",
            "fieldName": "fieldHandle",
            "displayName": "Field Name",
            "isLocalizable": true
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "Block description",
          "childBlocks": true,
          "topLevel": true,
          "minBlocks": 0,
          "maxBlocks": 0
        }
      }
    ],
    "debug": [
      "Neo field ID: 5",
      "Neo field handle: neoFieldHandle",
      "Field class: benf\\neo\\Field",
      "getBlockTypes() returned 2 block types",
      "Final block types count: 2"
    ]
  }
}
```

## Block Type Metadata

For each Neo block type, the following metadata is exported when available:

### Basic Settings
- `enabled` - Whether the block type is enabled
- `description` - Block type description
- `topLevel` - Whether this block type can be at the top level

### Child Block Configuration
- `childBlocks` - Which block types can be children:
  - `true` or `"*"` - All block types can be children
  - `["handle1", "handle2"]` - Only specific block types can be children (array of handles)
  - `false` or `null` - No child blocks allowed
- `groupChildBlockTypes` - Whether to group child block types in the UI

### Block Count Constraints
- `minBlocks` - Minimum number of this block type allowed
- `maxBlocks` - Maximum number of this block type allowed
- `minChildBlocks` - Minimum number of child blocks required
- `maxChildBlocks` - Maximum number of child blocks allowed
- `minSiblingBlocks` - Minimum number of sibling blocks at the same level
- `maxSiblingBlocks` - Maximum number of sibling blocks at the same level

## Debugging Information

Each Neo field export includes a `debug` array with:

- Neo field ID and handle
- Field class name
- Method used to retrieve block types
- Count of block types found
- Any errors encountered during processing

## Error Handling

The implementation includes comprehensive error handling:

1. **Field-level errors**: If Neo field processing fails, an error message is included in the response
2. **Block type errors**: Individual block type errors don't break the entire export
3. **Nested field errors**: Errors in nested field detection are handled silently
4. **Missing plugin**: If Neo plugin is not installed, no errors occur - fields are simply not detected as Neo

## Testing

To test Neo field support:

1. Create a Neo field with multiple block types
2. Add various field types to each block type
3. Create nested Neo or Matrix fields within block types
4. Call the API endpoint:
   ```
   GET /actions/smartcat-integration/api/fields?sectionHandle=yourSection&typeHandle=yourType
   ```
5. Verify the `neoFieldInfo` object contains all block types and fields

## Backward Compatibility

All changes are backward compatible:

- Existing Matrix field export remains unchanged
- Non-Neo fields continue to work as before
- If Neo plugin is not installed, no errors occur
- All existing API endpoints maintain their contracts

## Future Enhancements

Potential improvements for future versions:

1. **Block type groups**: Export Neo block type group information
2. **Conditional rules**: Export conditions for block type visibility
3. **Child block restrictions**: Export which block types can be children of others
4. **Recursive depth limiting**: Add configurable depth limits for deeply nested structures
5. **Performance optimization**: Cache block type information for repeated requests

## Files Modified

1. `src/controllers/ApiController.php` - Added Neo field detection and export methods
2. `README.md` - Added comprehensive Neo field documentation
3. `NEO_FIELD_SUPPORT.md` - This implementation summary document

## Compatibility

- **Craft CMS**: 5.x (tested with 5.7.7)
- **Neo Plugin**: Compatible with all recent versions (uses standard Neo API)
- **PHP**: 8.0+ (as required by Craft CMS 5)

