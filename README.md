# Smartcat Craft CMS Integration API

This plugin provides REST API endpoints to retrieve information about Craft CMS fields, sections, entry types, sites, and users. It's specifically designed to work with **Craft CMS 5.x** and handles the new matrix field structure (Entry fields).

## Overview

The API provides structured information about your Craft CMS installation, making it easy to integrate with external services like translation management systems.

## Installation

1. Place the plugin files in your Craft CMS `vendor/smartcat-ai/craft-smartcat-integration/` directory
2. The API endpoints will be available at `/actions/smartcat-integration/api/[endpoint]`

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

### 5. Get Users

**Endpoint:** `GET /actions/smartcat-integration/api/users`

**Description:** Returns a list of all users in the system.

**Example Response:**
```json
[
  {
    "id": 1,
    "name": "Admin User"
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
| Table | table |

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