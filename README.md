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

**Endpoint:** `GET /actions/smartcat-integration/api/fields`

**Parameters:**
- `sectionHandle` (required) - The handle of the section
- `typeHandle` (required) - The handle of the entry type
- `sectionId` (optional) - The ID of the section for optimization

**Description:** Returns detailed information about all fields for a specific section and entry type, including matrix field structures.

**Example Request:**
```
GET /actions/smartcat-integration/api/fields?sectionHandle=test_section_structure&typeHandle=testTypeSimple
```

**Example Response:**
```json
[
  {
    "fieldName": "test_content",
    "displayName": "Test content field",
    "isLocalizable": true,
    "type": "richtext",
    "section": "Test section structure",
    "sectionHandle": "test_section_structure",
    "sectionId": 4,
    "entryType": "test_type_simple",
    "entryTypeHandle": "testTypeSimple",
    "entryTypeId": 4,
    "debugInfo": {
      "fieldClass": "craft\\ckeditor\\Field",
      "isMatrixField": false,
      "fieldHandle": "test_content"
    }
  },
  {
    "fieldName": "testMatrixField",
    "displayName": "test matrix field",
    "isLocalizable": true,
    "type": "matrix",
    "section": "Test section structure",
    "sectionHandle": "test_section_structure",
    "sectionId": 4,
    "entryType": "test_type_simple",
    "entryTypeHandle": "testTypeSimple",
    "entryTypeId": 4,
    "debugInfo": {
      "fieldClass": "craft\\fields\\Matrix",
      "isMatrixField": true,
      "fieldHandle": "testMatrixField"
    },
    "matrixFieldInfo": {
      "fieldInfo": {
        "childFields": [
          {
            "fieldType": "entryType",
            "fieldName": "testTypeNested",
            "displayName": "test type nested",
            "typeIds": ["testTypeNested"]
          },
          {
            "fieldType": "entryType",
            "fieldName": "testTypeSimple",
            "displayName": "test_type_simple",
            "typeIds": ["testTypeSimple"]
          }
        ]
      },
      "nestedTypes": [
        {
          "typeHandle": "testTypeNested",
          "typeName": "test type nested",
          "typeId": 5,
          "childFields": [
            {
              "fieldType": "richtext",
              "fieldName": "test_content",
              "displayName": "Test content field",
              "isLocalizable": true
            }
          ]
        },
        {
          "typeHandle": "testTypeSimple",
          "typeName": "test_type_simple",
          "typeId": 4,
          "childFields": [
            {
              "fieldType": "richtext",
              "fieldName": "test_content",
              "displayName": "Test content field",
              "isLocalizable": true
            },
            {
              "fieldType": "matrix",
              "fieldName": "testMatrixField",
              "displayName": "test matrix field",
              "isLocalizable": true,
              "typeIds": ["testTypeNested", "testTypeSimple"]
            }
          ]
        }
      ]
    }
  }
]
```

### 2. Get Sections

**Endpoint:** `GET /actions/smartcat-integration/api/sections`

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

**Endpoint:** `GET /actions/smartcat-integration/api/types`

**Parameters:**
- `sectionHandle` (optional) - The handle of the section
- `sectionId` (optional) - The ID of the section

**Note:** Either `sectionHandle` or `sectionId` is required.

**Description:** Returns all entry types for a specific section.

**Example Request:**
```
GET /actions/smartcat-integration/api/types?sectionHandle=test_section_structure
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

**Endpoint:** `GET /actions/smartcat-integration/api/sites`

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

### What are Neo Fields?

Neo is a powerful third-party plugin for Craft CMS that provides a more advanced Matrix-like field type with additional features:

- **Hierarchical blocks** - Blocks can be nested within other blocks
- **Block type groups** - Organize block types into logical groups
- **Conditional blocks** - Show/hide block types based on conditions
- **Rich metadata** - Additional configuration options per block type

### Neo Field Detection

The API automatically detects Neo fields and exports detailed information about:

- All block types defined for the Neo field
- All fields within each block type
- Nested Neo or Matrix fields within block types
- Block type metadata (enabled status, description, child block settings, etc.)

### Neo Field Response Structure

When a Neo field is detected, the API adds a `neoFieldInfo` object containing:

#### `fieldInfo.childFields`
Lists all available block types for this Neo field:
```json
{
  "fieldType": "blockType",
  "fieldName": "blockTypeHandle",
  "displayName": "Block Type Name",
  "typeIds": ["blockTypeHandle"]
}
```

#### `blockTypes`
Detailed information about each block type, including their fields and metadata:
```json
{
  "typeHandle": "blockTypeHandle",
  "typeName": "Block Type Name",
  "typeId": 123,
  "gqlTypeName": "neoFieldHandle_blockTypeHandle_BlockType",
  "childFields": [
    {
      "fieldType": "richtext",
      "fieldName": "content",
      "displayName": "Content",
      "isLocalizable": true
    }
  ],
  "metadata": {
    "enabled": true,
    "description": "Block description",
    "childBlocks": ["allowedHandle1", "allowedHandle2"],
    "topLevel": true,
    "groupChildBlockTypes": true,
    "minBlocks": 0,
    "maxBlocks": 0,
    "minChildBlocks": 0,
    "maxChildBlocks": 0,
    "minSiblingBlocks": 0,
    "maxSiblingBlocks": 0
  }
}
```

### GraphQL Type Names

Each Neo block type includes its GraphQL type identifier in the `gqlTypeName` field. The format is:

```
{fieldHandle}_{blockTypeHandle}_BlockType
```

**Example:**
- Field handle: `pageContentAll`
- Block type handle: `heroHeader`
- GraphQL type: `pageContentAll_heroHeader_BlockType`

This allows you to map block types to their GraphQL schema types for queries and integrations.

### Example Neo Field Response

```json
{
  "fieldName": "neoContent",
  "displayName": "Neo Content",
  "isLocalizable": true,
  "type": "neo",
  "section": "Pages",
  "sectionHandle": "pages",
  "sectionId": 1,
  "entryType": "Default",
  "entryTypeHandle": "default",
  "entryTypeId": 1,
  "debugInfo": {
    "fieldClass": "benf\\neo\\Field",
    "isMatrixField": false,
    "isNeoField": true,
    "fieldHandle": "neoContent"
  },
  "neoFieldInfo": {
    "fieldInfo": {
      "childFields": [
        {
          "fieldType": "blockType",
          "fieldName": "textBlock",
          "displayName": "Text Block",
          "typeIds": ["textBlock"]
        },
        {
          "fieldType": "blockType",
          "fieldName": "imageBlock",
          "displayName": "Image Block",
          "typeIds": ["imageBlock"]
        }
      ]
    },
    "blockTypes": [
      {
        "typeHandle": "textBlock",
        "typeName": "Text Block",
        "typeId": 1,
        "childFields": [
          {
            "fieldType": "richtext",
            "fieldName": "text",
            "displayName": "Text",
            "isLocalizable": true
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "",
          "childBlocks": true,
          "topLevel": true,
          "minBlocks": 0,
          "maxBlocks": 0
        }
      },
      {
        "typeHandle": "imageBlock",
        "typeName": "Image Block",
        "typeId": 2,
        "childFields": [
          {
            "fieldType": "assets",
            "fieldName": "image",
            "displayName": "Image",
            "isLocalizable": false
          },
          {
            "fieldType": "string",
            "fieldName": "caption",
            "displayName": "Caption",
            "isLocalizable": true
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "",
          "childBlocks": false,
          "topLevel": true,
          "minBlocks": 0,
          "maxBlocks": 0
        }
      }
    ],
    "debug": [
      "Neo field ID: 5",
      "Neo field handle: neoContent",
      "Field class: benf\\neo\\Field",
      "getBlockTypes() returned 2 block types",
      "Final block types count: 2"
    ]
  }
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
GET /actions/smartcat-integration/api/fields?sectionHandle=test_section_structure&typeHandle=testTypeSimple
```

## Contributing

When contributing to this project:

1. **Test with Craft CMS 5** - Ensure compatibility with the latest Craft CMS version
2. **Handle matrix fields properly** - Use `getEntryTypes()` instead of deprecated `getBlockTypes()`
3. **Include error handling** - Add appropriate try/catch blocks for new functionality
4. **Update documentation** - Keep this README updated with any new features or changes

## License

This project is licensed under the terms specified in the LICENSE file. 