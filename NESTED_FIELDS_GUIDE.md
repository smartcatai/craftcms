# Nested Fields - Complete Recursion Guide

## Overview

Matrix and Neo fields **export the same complete structure** whether they're at the top level or nested within other fields. There is **no loss of information** due to nesting.

## Nesting Scenarios

| Outer Field | Inner Field | Full Info Exported? |
|-------------|-------------|---------------------|
| Neo | Matrix | ✅ Yes - `matrixFieldInfo` |
| Neo | Neo | ✅ Yes - `neoFieldInfo` |
| Matrix | Neo | ✅ Yes - `neoFieldInfo` |
| Matrix | Matrix | ✅ Yes - `matrixFieldInfo` |

## Example 1: Matrix Field Inside Neo Block

### Structure
```
Neo Field: pageBuilder
└── Block Type: contentSection
    └── Matrix Field: columnContent
        ├── Entry Type: textBlock
        └── Entry Type: imageBlock
```

### API Response
```json
{
  "fieldName": "pageBuilder",
  "type": "neo",
  "neoFieldInfo": {
    "blockTypes": [
      {
        "typeHandle": "contentSection",
        "typeName": "Content Section",
        "gqlTypeName": "pageBuilder_contentSection_BlockType",
        "childFields": [
          {
            "fieldType": "string",
            "fieldName": "sectionTitle",
            "displayName": "Section Title",
            "isLocalizable": true
          },
          {
            "fieldType": "matrix",
            "fieldName": "columnContent",
            "displayName": "Column Content",
            "isLocalizable": true,
            "typeIds": ["textBlock", "imageBlock"],
            
            // ✅ Full Matrix field info - same as top-level Matrix fields!
            "matrixFieldInfo": {
              "fieldInfo": {
                "childFields": [
                  {
                    "fieldType": "entryType",
                    "fieldName": "textBlock",
                    "displayName": "Text Block",
                    "typeIds": ["textBlock"]
                  },
                  {
                    "fieldType": "entryType",
                    "fieldName": "imageBlock",
                    "displayName": "Image Block",
                    "typeIds": ["imageBlock"]
                  }
                ]
              },
              "nestedTypes": [
                {
                  "typeHandle": "textBlock",
                  "typeName": "Text Block",
                  "typeId": 15,
                  "childFields": [
                    {
                      "fieldType": "richtext",
                      "fieldName": "text",
                      "displayName": "Text",
                      "isLocalizable": true
                    }
                  ]
                },
                {
                  "typeHandle": "imageBlock",
                  "typeName": "Image Block",
                  "typeId": 16,
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
                  ]
                }
              ],
              "debug": [
                "Matrix field ID: 8",
                "Matrix field handle: columnContent",
                "getEntryTypes() returned 2 entry types"
              ]
            }
          }
        ]
      }
    ]
  }
}
```

**Key Points:**
- ✅ Nested Matrix field has complete `matrixFieldInfo`
- ✅ All entry types are listed
- ✅ All fields within each entry type are shown
- ✅ Same structure as top-level Matrix fields

## Example 2: Neo Field Inside Neo Block

### Structure
```
Neo Field: pageLayout
└── Block Type: section
    └── Neo Field: sectionBlocks
        ├── Block Type: textBlock
        └── Block Type: galleryBlock
```

### API Response
```json
{
  "fieldName": "pageLayout",
  "type": "neo",
  "neoFieldInfo": {
    "blockTypes": [
      {
        "typeHandle": "section",
        "typeName": "Section",
        "gqlTypeName": "pageLayout_section_BlockType",
        "childFields": [
          {
            "fieldType": "string",
            "fieldName": "sectionTitle",
            "displayName": "Section Title",
            "isLocalizable": true
          },
          {
            "fieldType": "neo",
            "fieldName": "sectionBlocks",
            "displayName": "Section Blocks",
            "isLocalizable": false,
            "typeIds": ["textBlock", "galleryBlock"],
            
            // ✅ Full Neo field info - same as top-level Neo fields!
            "neoFieldInfo": {
              "fieldInfo": {
                "childFields": [
                  {
                    "fieldType": "blockType",
                    "fieldName": "textBlock",
                    "displayName": "Text Block",
                    "typeIds": ["textBlock"],
                    "gqlTypeName": "sectionBlocks_textBlock_BlockType"
                  },
                  {
                    "fieldType": "blockType",
                    "fieldName": "galleryBlock",
                    "displayName": "Gallery Block",
                    "typeIds": ["galleryBlock"],
                    "gqlTypeName": "sectionBlocks_galleryBlock_BlockType"
                  }
                ]
              },
              "blockTypes": [
                {
                  "typeHandle": "textBlock",
                  "typeName": "Text Block",
                  "typeId": 20,
                  "gqlTypeName": "sectionBlocks_textBlock_BlockType",
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
                    "childBlocks": false,
                    "topLevel": true
                  }
                },
                {
                  "typeHandle": "galleryBlock",
                  "typeName": "Gallery Block",
                  "typeId": 21,
                  "gqlTypeName": "sectionBlocks_galleryBlock_BlockType",
                  "childFields": [
                    {
                      "fieldType": "assets",
                      "fieldName": "images",
                      "displayName": "Images",
                      "isLocalizable": false
                    }
                  ],
                  "metadata": {
                    "enabled": true,
                    "childBlocks": false,
                    "topLevel": true
                  }
                }
              ],
              "debug": [
                "Neo field ID: 12",
                "Neo field handle: sectionBlocks",
                "getBlockTypes() returned 2 block types"
              ]
            }
          }
        ]
      }
    ]
  }
}
```

**Key Points:**
- ✅ Nested Neo field has complete `neoFieldInfo`
- ✅ All block types with full metadata
- ✅ GraphQL type names for nested blocks
- ✅ Same structure as top-level Neo fields

## Example 3: Three Levels Deep (Neo → Neo → Matrix)

### Structure
```
Neo Field: pageContent
└── Block Type: section
    └── Neo Field: columns
        └── Block Type: column
            └── Matrix Field: columnContent
                ├── Entry Type: text
                └── Entry Type: image
```

### API Response (Simplified)
```json
{
  "fieldName": "pageContent",
  "type": "neo",
  "neoFieldInfo": {
    "blockTypes": [
      {
        "typeHandle": "section",
        "childFields": [
          {
            "fieldName": "columns",
            "fieldType": "neo",
            "neoFieldInfo": {
              "blockTypes": [
                {
                  "typeHandle": "column",
                  "childFields": [
                    {
                      "fieldName": "columnContent",
                      "fieldType": "matrix",
                      
                      // ✅ Full Matrix info at 3rd level!
                      "matrixFieldInfo": {
                        "fieldInfo": { /* ... */ },
                        "nestedTypes": [
                          {
                            "typeHandle": "text",
                            "childFields": [ /* ... */ ]
                          },
                          {
                            "typeHandle": "image",
                            "childFields": [ /* ... */ ]
                          }
                        ]
                      }
                    }
                  ]
                }
              ]
            }
          }
        ]
      }
    ]
  }
}
```

**Key Points:**
- ✅ Works at any depth level
- ✅ Each field type exports its full structure
- ✅ No information loss

## Using Nested Field Information

### 1. Traverse Nested Structure

```javascript
function findAllMatrixFields(neoField, path = []) {
  const matrixFields = [];
  
  for (const blockType of neoField.neoFieldInfo.blockTypes) {
    const currentPath = [...path, blockType.typeHandle];
    
    for (const field of blockType.childFields) {
      if (field.fieldType === 'matrix' && field.matrixFieldInfo) {
        matrixFields.push({
          path: currentPath.join(' → '),
          fieldName: field.fieldName,
          fieldInfo: field.matrixFieldInfo
        });
      }
      
      // Recursively check nested Neo fields
      if (field.fieldType === 'neo' && field.neoFieldInfo) {
        matrixFields.push(
          ...findAllMatrixFields(field, currentPath)
        );
      }
    }
  }
  
  return matrixFields;
}

// Usage
const allMatrixFields = findAllMatrixFields(pageBuilderData);
console.log('Found Matrix fields:', allMatrixFields);
// [
//   {
//     path: 'contentSection',
//     fieldName: 'columnContent',
//     fieldInfo: { ... }
//   }
// ]
```

### 2. Extract All Translatable Fields (Recursive)

```javascript
function extractTranslatableFields(field, prefix = '') {
  const translatableFields = [];
  
  // Handle Neo fields
  if (field.neoFieldInfo) {
    for (const blockType of field.neoFieldInfo.blockTypes) {
      const blockPrefix = `${prefix}[${blockType.typeHandle}]`;
      
      for (const childField of blockType.childFields) {
        const fieldPath = `${blockPrefix}.${childField.fieldName}`;
        
        if (childField.isLocalizable) {
          translatableFields.push({
            path: fieldPath,
            name: childField.displayName,
            type: childField.fieldType
          });
        }
        
        // Recurse into nested Matrix/Neo fields
        if (childField.matrixFieldInfo || childField.neoFieldInfo) {
          translatableFields.push(
            ...extractTranslatableFields(childField, fieldPath)
          );
        }
      }
    }
  }
  
  // Handle Matrix fields
  if (field.matrixFieldInfo) {
    for (const entryType of field.matrixFieldInfo.nestedTypes) {
      const typePrefix = `${prefix}[${entryType.typeHandle}]`;
      
      for (const childField of entryType.childFields) {
        const fieldPath = `${typePrefix}.${childField.fieldName}`;
        
        if (childField.isLocalizable) {
          translatableFields.push({
            path: fieldPath,
            name: childField.displayName,
            type: childField.fieldType
          });
        }
        
        // Recurse if this field has nested info
        if (childField.matrixFieldInfo || childField.neoFieldInfo) {
          translatableFields.push(
            ...extractTranslatableFields(childField, fieldPath)
          );
        }
      }
    }
  }
  
  return translatableFields;
}

// Usage
const translatable = extractTranslatableFields(neoFieldData, 'pageBuilder');
console.log('Translatable fields:', translatable);
// [
//   { path: 'pageBuilder[section].title', name: 'Title', type: 'string' },
//   { path: 'pageBuilder[section].columnContent[text].content', name: 'Content', type: 'richtext' }
// ]
```

### 3. Generate GraphQL Queries (Recursive)

```javascript
function generateNeoGraphQL(field, indent = '      ') {
  let query = '';
  
  if (field.neoFieldInfo) {
    for (const blockType of field.neoFieldInfo.blockTypes) {
      query += `${indent}... on ${blockType.gqlTypeName} {\n`;
      query += `${indent}  __typename\n`;
      
      for (const childField of blockType.childFields) {
        if (childField.matrixFieldInfo) {
          // Nested Matrix field
          query += `${indent}  ${childField.fieldName} {\n`;
          query += generateMatrixGraphQL(childField, indent + '    ');
          query += `${indent}  }\n`;
        } else if (childField.neoFieldInfo) {
          // Nested Neo field
          query += `${indent}  ${childField.fieldName} {\n`;
          query += generateNeoGraphQL(childField, indent + '    ');
          query += `${indent}  }\n`;
        } else {
          // Regular field
          query += `${indent}  ${childField.fieldName}\n`;
        }
      }
      
      query += `${indent}}\n`;
    }
  }
  
  return query;
}

function generateMatrixGraphQL(field, indent = '      ') {
  let query = '';
  
  if (field.matrixFieldInfo) {
    for (const entryType of field.matrixFieldInfo.nestedTypes) {
      query += `${indent}... on ${entryType.typeHandle}_EntryType {\n`;
      query += `${indent}  __typename\n`;
      
      for (const childField of entryType.childFields) {
        // Handle nested fields recursively
        if (childField.neoFieldInfo) {
          query += `${indent}  ${childField.fieldName} {\n`;
          query += generateNeoGraphQL(childField, indent + '    ');
          query += `${indent}  }\n`;
        } else {
          query += `${indent}  ${childField.fieldName}\n`;
        }
      }
      
      query += `${indent}}\n`;
    }
  }
  
  return query;
}
```

### 4. Build Content Structure Map

```javascript
function buildStructureMap(field, depth = 0) {
  const structure = {
    name: field.fieldName || 'root',
    type: field.fieldType || field.type,
    depth: depth,
    children: []
  };
  
  // Process Neo fields
  if (field.neoFieldInfo) {
    for (const blockType of field.neoFieldInfo.blockTypes) {
      const blockNode = {
        name: blockType.typeHandle,
        type: 'neo-block',
        displayName: blockType.typeName,
        gqlTypeName: blockType.gqlTypeName,
        depth: depth + 1,
        children: []
      };
      
      for (const childField of blockType.childFields) {
        if (childField.neoFieldInfo || childField.matrixFieldInfo) {
          blockNode.children.push(buildStructureMap(childField, depth + 2));
        } else {
          blockNode.children.push({
            name: childField.fieldName,
            type: childField.fieldType,
            displayName: childField.displayName,
            depth: depth + 2,
            children: []
          });
        }
      }
      
      structure.children.push(blockNode);
    }
  }
  
  // Process Matrix fields
  if (field.matrixFieldInfo) {
    for (const entryType of field.matrixFieldInfo.nestedTypes) {
      const entryNode = {
        name: entryType.typeHandle,
        type: 'matrix-entry',
        displayName: entryType.typeName,
        depth: depth + 1,
        children: []
      };
      
      for (const childField of entryType.childFields) {
        if (childField.neoFieldInfo || childField.matrixFieldInfo) {
          entryNode.children.push(buildStructureMap(childField, depth + 2));
        } else {
          entryNode.children.push({
            name: childField.fieldName,
            type: childField.fieldType,
            displayName: childField.displayName,
            depth: depth + 2,
            children: []
          });
        }
      }
      
      structure.children.push(entryNode);
    }
  }
  
  return structure;
}

// Usage
const structure = buildStructureMap(neoFieldData);
console.log(JSON.stringify(structure, null, 2));
```

## Benefits of Full Recursion

### 1. Complete Information
- ✅ No guessing about nested field structure
- ✅ All block types and entry types are known
- ✅ All fields at every level are documented

### 2. Translation Management
- ✅ Can identify all translatable fields at any depth
- ✅ Know exact paths to nested content
- ✅ Understand content hierarchy

### 3. Content Migration
- ✅ Can replicate entire structure in target system
- ✅ Know all field dependencies
- ✅ Validate structure completeness

### 4. Type Safety
- ✅ Generate complete TypeScript types
- ✅ Cover all nested structures
- ✅ No "any" types for nested fields

### 5. GraphQL Integration
- ✅ Generate complete queries
- ✅ Handle any nesting depth
- ✅ Know all GraphQL type names

## Performance Considerations

### Impact
- **Minimal** - Nested fields are processed once during API call
- **Cached** - Uses Craft's field layout caching
- **No extra queries** - All data already loaded

### Depth Limits
- No artificial depth limits
- Neo/Matrix can nest as deep as configured
- Recursive processing handles any depth

### Response Size
- Nested structures increase response size
- Still manageable (typically <100KB per field)
- Can be cached on client side

## Troubleshooting

### Missing matrixFieldInfo in nested Matrix field

**Check:**
1. Is it actually a Matrix field? Look at `fieldType`
2. Check `debugInfo.isMatrixField` should be `true`
3. Look for errors in `matrixFieldInfo.debug` array

### Missing neoFieldInfo in nested Neo field

**Check:**
1. Is it actually a Neo field? Look at `fieldType`
2. Check `debugInfo.isNeoField` should be `true`
3. Look for errors in `neoFieldInfo.debug` array

### Infinite recursion concerns

**Not an issue:**
- Fields can only nest based on how they're configured
- Craft CMS prevents circular references
- Each field is processed once per occurrence

## Summary

✅ **Matrix fields inside Neo** → Full `matrixFieldInfo` exported  
✅ **Neo fields inside Neo** → Full `neoFieldInfo` exported  
✅ **Neo fields inside Matrix** → Full `neoFieldInfo` exported  
✅ **Matrix fields inside Matrix** → Full `matrixFieldInfo` exported  
✅ **Any depth** → Complete recursion supported  
✅ **No information loss** → Same structure as top-level fields  

**The rule is simple:** Every Matrix/Neo field exports its complete structure, regardless of where it appears in the hierarchy.




