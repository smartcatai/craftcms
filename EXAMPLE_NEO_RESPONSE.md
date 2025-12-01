# Neo Field Response Examples

## Before: Neo Field Not Detected

Previously, Neo fields were exported with minimal information:

```json
{
  "fieldName": "contentBlocks",
  "displayName": "Content Blocks",
  "isLocalizable": true,
  "type": "field",
  "section": "Pages",
  "sectionHandle": "pages",
  "sectionId": 1,
  "entryType": "Default",
  "entryTypeHandle": "default",
  "entryTypeId": 1,
  "debugInfo": {
    "fieldClass": "benf\\neo\\Field",
    "isMatrixField": false,
    "fieldHandle": "contentBlocks"
  }
}
```

**Problems:**
- No information about block types
- No information about nested fields
- Cannot determine what fields are available in the Neo blocks
- Field type shown as generic "field" instead of "neo"

## After: Complete Neo Field Information

Now Neo fields are fully exported with all nested information:

```json
{
  "fieldName": "contentBlocks",
  "displayName": "Content Blocks",
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
    "fieldHandle": "contentBlocks"
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
        },
        {
          "fieldType": "blockType",
          "fieldName": "galleryBlock",
          "displayName": "Gallery Block",
          "typeIds": ["galleryBlock"]
        }
      ]
    },
    "blockTypes": [
      {
        "typeHandle": "textBlock",
        "typeName": "Text Block",
        "typeId": 10,
        "gqlTypeName": "contentBlocks_textBlock_BlockType",
        "childFields": [
          {
            "fieldType": "richtext",
            "fieldName": "heading",
            "displayName": "Heading",
            "isLocalizable": true
          },
          {
            "fieldType": "richtext",
            "fieldName": "bodyText",
            "displayName": "Body Text",
            "isLocalizable": true
          },
          {
            "fieldType": "select",
            "fieldName": "alignment",
            "displayName": "Text Alignment",
            "isLocalizable": false
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "A simple text block with heading and body",
          "childBlocks": false,
          "topLevel": true,
          "groupChildBlockTypes": true,
          "minBlocks": 0,
          "maxBlocks": 0,
          "minChildBlocks": 0,
          "maxChildBlocks": 0,
          "minSiblingBlocks": 0,
          "maxSiblingBlocks": 0
        }
      },
      {
        "typeHandle": "imageBlock",
        "typeName": "Image Block",
        "typeId": 11,
        "gqlTypeName": "contentBlocks_imageBlock_BlockType",
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
          },
          {
            "fieldType": "string",
            "fieldName": "altText",
            "displayName": "Alt Text",
            "isLocalizable": true
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "Single image with caption",
          "childBlocks": false,
          "topLevel": true,
          "minBlocks": 0,
          "maxBlocks": 0
        }
      },
      {
        "typeHandle": "galleryBlock",
        "typeName": "Gallery Block",
        "typeId": 12,
        "gqlTypeName": "contentBlocks_galleryBlock_BlockType",
        "childFields": [
          {
            "fieldType": "assets",
            "fieldName": "images",
            "displayName": "Gallery Images",
            "isLocalizable": false
          },
          {
            "fieldType": "select",
            "fieldName": "layout",
            "displayName": "Gallery Layout",
            "isLocalizable": false
          },
          {
            "fieldType": "neo",
            "fieldName": "galleryItems",
            "displayName": "Gallery Items",
            "isLocalizable": false,
            "typeIds": ["galleryItem"]
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "Image gallery with nested items",
          "childBlocks": ["galleryItem", "textBlock"],
          "topLevel": true,
          "groupChildBlockTypes": true,
          "minBlocks": 0,
          "maxBlocks": 0,
          "minChildBlocks": 1,
          "maxChildBlocks": 10,
          "minSiblingBlocks": 0,
          "maxSiblingBlocks": 0
        }
      }
    ],
    "debug": [
      "Neo field ID: 5",
      "Neo field handle: contentBlocks",
      "Field class: benf\\neo\\Field",
      "getBlockTypes() returned 3 block types",
      "Final block types count: 3"
    ]
  }
}
```

**Benefits:**
- ✅ Complete list of all block types
- ✅ All fields within each block type
- ✅ Field localization settings
- ✅ Block type metadata (enabled, description, settings)
- ✅ Nested Neo field detection (see `galleryItems` field with `typeIds`)
- ✅ Debug information for troubleshooting
- ✅ Correct field type identification ("neo")

## Complex Example: Deeply Nested Structure

Here's an example with Neo fields nested within Neo blocks:

```json
{
  "fieldName": "pageBuilder",
  "displayName": "Page Builder",
  "isLocalizable": true,
  "type": "neo",
  "neoFieldInfo": {
    "fieldInfo": {
      "childFields": [
        {
          "fieldType": "blockType",
          "fieldName": "section",
          "displayName": "Section",
          "typeIds": ["section"]
        },
        {
          "fieldType": "blockType",
          "fieldName": "column",
          "displayName": "Column",
          "typeIds": ["column"]
        }
      ]
    },
    "blockTypes": [
      {
        "typeHandle": "section",
        "typeName": "Section",
        "typeId": 20,
        "childFields": [
          {
            "fieldType": "string",
            "fieldName": "sectionTitle",
            "displayName": "Section Title",
            "isLocalizable": true
          },
          {
            "fieldType": "neo",
            "fieldName": "columns",
            "displayName": "Columns",
            "isLocalizable": false,
            "typeIds": ["column"]
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "A section with nested columns",
          "childBlocks": true,
          "topLevel": true,
          "minBlocks": 0,
          "maxBlocks": 0
        }
      },
      {
        "typeHandle": "column",
        "typeName": "Column",
        "typeId": 21,
        "childFields": [
          {
            "fieldType": "select",
            "fieldName": "columnWidth",
            "displayName": "Column Width",
            "isLocalizable": false
          },
          {
            "fieldType": "matrix",
            "fieldName": "columnContent",
            "displayName": "Column Content",
            "isLocalizable": true,
            "typeIds": ["textBlock", "imageBlock"]
          }
        ],
        "metadata": {
          "enabled": true,
          "description": "A column with flexible content",
          "childBlocks": false,
          "topLevel": false,
          "minBlocks": 1,
          "maxBlocks": 4
        }
      }
    ],
    "debug": [
      "Neo field ID: 8",
      "Neo field handle: pageBuilder",
      "Field class: benf\\neo\\Field",
      "getBlockTypes() returned 2 block types",
      "Final block types count: 2"
    ]
  }
}
```

**Complex Structure Features:**
- Neo field "pageBuilder" contains "section" blocks
- "section" blocks contain nested Neo field "columns"
- "column" blocks contain Matrix field "columnContent"
- All type IDs are properly exported for nested fields
- Metadata shows `topLevel: false` for column (can only be nested)

## Use Cases

### Translation Management Systems
With complete Neo field structure, translation systems can:
1. Identify all translatable fields within nested blocks
2. Understand the hierarchy of content
3. Extract content from deeply nested structures
4. Maintain context based on block type names

### Content Migration
Migration tools can:
1. Map Neo field structures between installations
2. Verify all required block types exist
3. Transform content based on field metadata
4. Handle nested structures programmatically

### API Documentation Generation
Documentation tools can:
1. Auto-generate content structure documentation
2. Show available block types and their fields
3. Display field requirements and constraints
4. Visualize content hierarchy

### Validation and Testing
Testing tools can:
1. Verify all block types are properly configured
2. Check for missing or incorrect field configurations
3. Validate nested field relationships
4. Ensure localization settings are correct

## Understanding childBlocks Metadata

The `childBlocks` field in metadata defines which block types can be nested as children. It can have three different values:

### 1. All Blocks Allowed
```json
{
  "metadata": {
    "childBlocks": true
  }
}
```
or
```json
{
  "metadata": {
    "childBlocks": "*"
  }
}
```
**Meaning:** Any block type defined in the Neo field can be added as a child of this block.

### 2. Specific Blocks Allowed
```json
{
  "metadata": {
    "childBlocks": ["textBlock", "imageBlock", "galleryItem"]
  }
}
```
**Meaning:** Only blocks with handles `textBlock`, `imageBlock`, or `galleryItem` can be added as children.

**Use Case:** A "section" block that only allows specific content blocks as children, not other sections.

### 3. No Children Allowed
```json
{
  "metadata": {
    "childBlocks": false
  }
}
```
or
```json
{
  "metadata": {
    "childBlocks": null
  }
}
```
**Meaning:** This block type cannot have any child blocks.

**Use Case:** Leaf-level blocks like simple text or image blocks.

## Complete Metadata Example with Child Block Restrictions

```json
{
  "typeHandle": "contentSection",
  "typeName": "Content Section",
  "typeId": 15,
  "childFields": [...],
  "metadata": {
    "enabled": true,
    "description": "A content section that can contain text and image blocks",
    
    // Child block configuration
    "childBlocks": ["textBlock", "imageBlock"],
    "groupChildBlockTypes": true,
    "minChildBlocks": 1,
    "maxChildBlocks": 10,
    
    // Placement restrictions
    "topLevel": true,
    
    // Count constraints
    "minBlocks": 0,
    "maxBlocks": 5,
    "minSiblingBlocks": 0,
    "maxSiblingBlocks": 0
  }
}
```

**What this means:**
- ✅ This is a "Content Section" block
- ✅ It can be at the top level (`topLevel: true`)
- ✅ It can only have `textBlock` or `imageBlock` as children (not other sections)
- ✅ It must have at least 1 child block (`minChildBlocks: 1`)
- ✅ It can have up to 10 child blocks (`maxChildBlocks: 10`)
- ✅ You can have up to 5 of these section blocks in the field (`maxBlocks: 5`)
- ✅ Child block types are grouped in the UI (`groupChildBlockTypes: true`)

## GraphQL Integration

### GraphQL Type Names

Each block type includes a `gqlTypeName` field that corresponds to its GraphQL type in the schema. The format is:

```
{fieldHandle}_{blockTypeHandle}_BlockType
```

### Example GraphQL Usage

#### API Response
```json
{
  "typeHandle": "heroHeader",
  "typeName": "Hero Header",
  "gqlTypeName": "pageContentAll_heroHeader_BlockType",
  "childFields": [
    {
      "fieldType": "string",
      "fieldName": "headline",
      "displayName": "Headline"
    }
  ]
}
```

#### Corresponding GraphQL Query
```graphql
query {
  entry(id: 123) {
    ... on pages_default_Entry {
      pageContentAll {
        ... on pageContentAll_heroHeader_BlockType {
          headline
        }
      }
    }
  }
}
```

### Mapping API to GraphQL

Use the `gqlTypeName` to automatically generate GraphQL fragments:

```javascript
// Generate GraphQL fragment from API response
function generateGraphQLFragment(blockType) {
  const fields = blockType.childFields
    .map(f => `      ${f.fieldName}`)
    .join('\n');
  
  return `
    ... on ${blockType.gqlTypeName} {
${fields}
    }
  `;
}

// Usage
const blockTypes = neoField.neoFieldInfo.blockTypes;
const fragments = blockTypes.map(generateGraphQLFragment).join('\n');

const query = `
  query {
    entry(id: 123) {
      ... on pages_default_Entry {
        ${neoField.fieldName} {
${fragments}
        }
      }
    }
  }
`;
```

### Complete Example with Multiple Block Types

#### API Response
```json
{
  "fieldName": "pageContent",
  "neoFieldInfo": {
    "blockTypes": [
      {
        "typeHandle": "hero",
        "gqlTypeName": "pageContent_hero_BlockType",
        "childFields": [
          {"fieldName": "title", "fieldType": "string"},
          {"fieldName": "subtitle", "fieldType": "string"}
        ]
      },
      {
        "typeHandle": "textSection",
        "gqlTypeName": "pageContent_textSection_BlockType",
        "childFields": [
          {"fieldName": "heading", "fieldType": "string"},
          {"fieldName": "body", "fieldType": "richtext"}
        ]
      },
      {
        "typeHandle": "gallery",
        "gqlTypeName": "pageContent_gallery_BlockType",
        "childFields": [
          {"fieldName": "images", "fieldType": "assets"}
        ]
      }
    ]
  }
}
```

#### Generated GraphQL Query
```graphql
query GetPageContent($id: [QueryArgument]) {
  entry(id: $id) {
    ... on pages_default_Entry {
      pageContent {
        ... on pageContent_hero_BlockType {
          title
          subtitle
        }
        ... on pageContent_textSection_BlockType {
          heading
          body
        }
        ... on pageContent_gallery_BlockType {
          images {
            url
            title
          }
        }
      }
    }
  }
}
```

### TypeScript Type Generation

Use the API response to generate TypeScript types for GraphQL:

```typescript
interface BlockTypeInfo {
  typeHandle: string;
  typeName: string;
  gqlTypeName: string;
  childFields: Array<{
    fieldName: string;
    fieldType: string;
  }>;
}

function generateTypeScriptInterface(blockType: BlockTypeInfo): string {
  const fields = blockType.childFields
    .map(f => {
      const tsType = mapFieldTypeToTS(f.fieldType);
      return `  ${f.fieldName}: ${tsType};`;
    })
    .join('\n');
  
  return `
export interface ${blockType.gqlTypeName.replace(/-/g, '_')} {
  __typename: '${blockType.gqlTypeName}';
${fields}
}
  `.trim();
}

function mapFieldTypeToTS(fieldType: string): string {
  const typeMap: Record<string, string> = {
    'string': 'string',
    'richtext': 'string',
    'number': 'number',
    'boolean': 'boolean',
    'assets': 'Asset[]',
    'entries': 'Entry[]',
    'neo': 'BlockType[]'
  };
  return typeMap[fieldType] || 'any';
}
```

### GraphQL Schema Introspection

You can use the `gqlTypeName` to validate against your GraphQL schema:

```javascript
// Check if block type exists in GraphQL schema
async function validateBlockTypeInSchema(gqlTypeName, schemaUrl) {
  const introspectionQuery = `
    query {
      __type(name: "${gqlTypeName}") {
        name
        fields {
          name
          type {
            name
            kind
          }
        }
      }
    }
  `;
  
  const response = await fetch(schemaUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ query: introspectionQuery })
  });
  
  const data = await response.json();
  return data.data.__type !== null;
}
```

## Practical Example: Hierarchical Page Builder

Imagine a page builder with this structure:
- **Page Sections** (can contain columns)
  - **Columns** (can contain content blocks)
    - **Text Blocks** (no children)
    - **Image Blocks** (no children)
    - **Button Blocks** (no children)

The metadata would look like:

### Page Section Block
```json
{
  "typeHandle": "pageSection",
  "metadata": {
    "childBlocks": ["column"],
    "topLevel": true,
    "minChildBlocks": 1,
    "maxChildBlocks": 4
  }
}
```

### Column Block  
```json
{
  "typeHandle": "column",
  "metadata": {
    "childBlocks": ["textBlock", "imageBlock", "buttonBlock"],
    "topLevel": false,
    "minChildBlocks": 0,
    "maxChildBlocks": 10
  }
}
```

### Content Blocks
```json
{
  "typeHandle": "textBlock",
  "metadata": {
    "childBlocks": false,
    "topLevel": false
  }
}
```

This configuration ensures:
- ✅ Sections can only contain columns (not content blocks directly)
- ✅ Columns can only contain content blocks (not other columns or sections)
- ✅ Content blocks can't have children (leaf nodes)
- ✅ Only sections can be at the top level

