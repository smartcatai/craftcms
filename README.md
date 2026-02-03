# Smartcat Craft CMS Integration API

This plugin provides REST API endpoints to retrieve information about Craft CMS fields, sections, entry types, sites, and users. It's specifically designed to work with **Craft CMS 5.x** and handles the new matrix field structure (Entry fields).

## Overview

The API provides structured information about your Craft CMS installation, making it easy to integrate with external services like translation management systems.

## Installation

composer require smartcat-ai/craft-smartcat-integration
php craft plugin/install smartcat-integration

## Authentication

All endpoints are configured to allow anonymous access for easy integration.

## API Endpoints

### 1. Get Fields Information

**Endpoint:** `GET /api/smartcat/fields`

**Parameters:**
- `sectionHandle` (required) - The handle of the section
- `typeHandle` (required) - The handle of the entry type
- `sectionId` (optional) - The ID of the section for optimization

**Description:** Returns entry types and field types for a specific section and entry type.

**Example Request:**
```
GET /api/smartcat/fields?sectionHandle=test_section_structure&typeHandle=testTypeSimple
```

**Example Response:**
```json
{
  "entryTypes": [
    {
      "typeHandle": "testTypeSimple",
      "typeName": "Test type simple",
      "displayName": "Test type simple",
      "typeId": 1,
      "fieldTypes": ["title", "content"]
    }
  ],
  "fieldTypes": [
    {
      "typeHandle": "content",
      "typeName": "richtext",
      "displayName": "Content",
      "typeId": 1,
      "isLocalizable": true,
      "graphQLMode": "raw",
      "matrixEntryTypes": [],
      "neoBlockTypes": {}
    },
    {
      "typeHandle": "neoField1",
      "typeName": "neo",
      "displayName": "Neo Field 1",
      "typeId": 12,
      "isLocalizable": true,
      "graphQLMode": null,
      "matrixEntryTypes": [],
      "neoBlockTypes": {
        "block1": ["block1", "myType2"],
        "myType2": []
      }
    }
  ]
}
```

### 2. Get Sections

**Endpoint:** `GET /api/smartcat/sections`

**Description:** Returns information about all sections in the Craft CMS installation.

**Example Response:**
```json
[
  {
    "id": 4,
    "handle": "test_section_structure",
    "name": "Test section structure",
    "type": "structure"
  }
]
```

### 3. Get Entry Types

**Endpoint:** `GET /api/smartcat/types`

**Parameters:**
- `sectionHandle` (optional) - The handle of the section
- `sectionId` (optional) - The ID of the section

**Note:** Either `sectionHandle` or `sectionId` is required.

**Description:** Returns all entry types for a specific section.

**Example Request:**
```
GET /api/smartcat/types?sectionHandle=test_section_structure
```

**Example Response:**
```json
[
  {
    "id": 4,
    "handle": "testTypeSimple",
    "name": "test_type_simple",
    "sectionId": 4,
    "sectionHandle": "test_section_structure",
    "sectionName": "Test section structure",
    "hasTitleField": true,
    "titleTranslationMethod": "site",
    "titleTranslationKeyFormat": null,
    "titleFormat": null,
    "fieldsCount": 2
  }
]
```

### 4. Get Sites

**Endpoint:** `GET /api/smartcat/sites`

**Description:** Returns information about all sites configured in Craft CMS.

**Example Response:**
```json
[
  {
    "id": 1,
    "handle": "default",
    "name": "Default Site",
    "language": "en-US",
    "primary": true,
    "enabled": true,
    "baseUrl": "@web/",
    "hasUrls": true
  }
]
```

### 5. Get Full Meta

**Endpoint:** `GET /api/smartcat/meta`

**Description:** Returns the same contract as `fields`, but for all entry types and all fields in the system.

## Field Types

The API automatically maps Craft CMS field types to more readable names:

| Craft CMS Field Type | API Field Type |
|---------------------|----------------|
| PlainText | string |
| Textarea | text |
| RichText | richtext |
| CKEditor Field | richtext |
| Number | number |
| Email | email |
| Url | url |
| Date | date |
| Lightswitch | boolean |
| Dropdown | select |
| Checkboxes | multiselect |
| RadioButtons | radio |
| Entries | entries |
| Categories | categories |
| Assets | assets |
| Users | users |
| Tags | tags |
| Matrix | matrix |
| Neo | neo |
| Table | table |

## Neo Fields

For Neo fields, `neoBlockTypes` returns a hierarchy object:

```json
{
  "parentHandle": ["childHandle1", "childHandle2"],
  "childHandle1": []
}
```

### Block Type Metadata

Each Neo block type includes comprehensive metadata about its configuration:

#### Child Block Configuration
The `childBlocks` field defines which block types can be nested as children:

- **`true` or `"*"`** - All block types can be children
- **`["handle1", "handle2"]`** - Only specific block types can be children (array of handles)
- **`false` or `null`** - No child blocks allowed

Example:
```json
{
  "metadata": {
    "childBlocks": ["textBlock", "imageBlock"],
    "minChildBlocks": 1,
    "maxChildBlocks": 5
  }
}
```

This means:
- Only `textBlock` and `imageBlock` types can be added as children
- Must have at least 1 child block
- Can have up to 5 child blocks

#### All Metadata Fields
- `enabled` - Whether the block type is enabled
- `description` - Block type description
- `childBlocks` - Which block types can be children (see above)
- `topLevel` - Whether this block type can be at the top level
- `groupChildBlockTypes` - Whether to group child block types in the UI
- `minBlocks` - Minimum number of this block type allowed
- `maxBlocks` - Maximum number of this block type allowed
- `minChildBlocks` - Minimum number of child blocks required
- `maxChildBlocks` - Maximum number of child blocks allowed
- `minSiblingBlocks` - Minimum number of sibling blocks at the same level
- `maxSiblingBlocks` - Maximum number of sibling blocks at the same level

### Nested Neo and Matrix Fields

The API handles complex nested structures with **full recursion**:

1. **Neo within Neo** - Nested Neo fields export complete `neoFieldInfo` with all block types
2. **Matrix within Neo** - Nested Matrix fields export complete `matrixFieldInfo` with all entry/block types
3. **Neo within Matrix** - Nested Neo fields export complete `neoFieldInfo` with all block types
4. **Matrix within Matrix** - Nested Matrix fields export complete `matrixFieldInfo` (Craft 5)

**Important:** Nested fields export the **same complete structure** as top-level fields:
- Matrix fields always include `matrixFieldInfo` (whether nested or not)
- Neo fields always include `neoFieldInfo` (whether nested or not)
- All block types, entry types, and fields are fully exported
- No information is lost due to nesting

## Craft CMS 5 Matrix Fields

### Important Changes in Craft CMS 5

In Craft CMS 5, the matrix field system has been completely redesigned:

- **Matrix blocks are now entries** - What used to be matrix blocks are now regular entries
- **Block types are now entry types** - The old `getBlockTypes()` method no longer exists
- **Use `getEntryTypes()`** - Matrix fields now use `getEntryTypes()` to get available entry types
- **Nested structure** - Matrix fields can contain other matrix fields, creating complex nested structures

### Matrix Field Response Structure

When a matrix field is detected, the API adds a `matrixFieldInfo` object containing:

#### `fieldInfo.childFields`
Lists all available entry types for this matrix field:
```json
{
  "fieldType": "entryType",
  "fieldName": "entryTypeHandle",
  "displayName": "Entry Type Name",
  "typeIds": ["entryTypeHandle"]
}
```

#### `nestedTypes`
Detailed information about each entry type, including their fields:
```json
{
  "typeHandle": "entryTypeHandle",
  "typeName": "Entry Type Name",
  "typeId": 123,
  "childFields": [
    {
      "fieldType": "richtext",
      "fieldName": "content",
      "displayName": "Content",
      "isLocalizable": true
    }
  ]
}
```

### Nested Matrix Fields

The API handles nested matrix fields (matrix fields within matrix entry types) by:

1. **Detecting nested matrix fields** - Checks if any field within an entry type is also a matrix field
2. **Adding `typeIds` array** - Lists all available entry types for nested matrix fields
3. **Preventing infinite recursion** - Includes safety measures for complex nested structures

### Debugging Information

Each matrix field includes debug information to help troubleshoot:

```json
{
  "debug": [
    "Matrix field ID: 3",
    "Matrix field handle: testMatrixField",
    "Craft version: 5.7.7",
    "Field class: craft\\fields\\Matrix",
    "getEntryTypes() returned 2 entry types",
    "Final entry types count: 2",
    "Entry type testTypeNested has 1 custom fields",
    "Entry type testTypeSimple has 2 custom fields",
    "Nested matrix field testMatrixField has 2 entry types"
  ]
}
```

## Error Handling

The API includes comprehensive error handling:

- **Missing parameters** - Returns 400 Bad Request with descriptive error messages
- **Invalid section/type handles** - Returns 400 Bad Request if sections or entry types don't exist
- **Matrix field processing errors** - Returns error information within the matrix field response
- **Field processing errors** - Individual field errors don't break the entire response

## Version Compatibility

- **Craft CMS 5.x** - Fully supported with new matrix field structure
- **Craft CMS 4.x and below** - May require modifications for matrix field handling

## Plugin Compatibility

### Required Plugins

No plugins are required for basic functionality.

### Supported Plugins

#### Neo Plugin (by Spicy Web)

- **Plugin Handle**: `neo`
- **Namespace**: `benf\neo`
- **What it provides**: Advanced Matrix-like field with hierarchical blocks, groups, and conditions
- **API Support**: Full support for Neo fields including:
  - All block types and their configurations
  - Nested fields within blocks
  - Block type metadata (enabled, description, child blocks settings, etc.)
  - Nested Neo and Matrix fields within Neo blocks

If the Neo plugin is not installed, Neo field detection will simply not trigger and the fields will be reported as their base type.

## Development and Testing

### Local Development Script

Use the included `fetch-local.ps1` PowerShell script to test the API locally:

```powershell
./fetch-local.ps1
```

This script:
1. Copies the source files to your local Craft CMS installation
2. Makes a test API request
3. Displays the formatted response

### Example Test Request

The script tests the fields endpoint with:
```
GET /api/smartcat/fields?sectionHandle=test_section_structure&typeHandle=testTypeSimple
```

## Contributing

When contributing to this project:

1. **Test with Craft CMS 5** - Ensure compatibility with the latest Craft CMS version
2. **Handle matrix fields properly** - Use `getEntryTypes()` instead of deprecated `getBlockTypes()`
3. **Include error handling** - Add appropriate try/catch blocks for new functionality
4. **Update documentation** - Keep this README updated with any new features or changes

## License

This project is licensed under the terms specified in the LICENSE file. 