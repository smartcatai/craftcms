# Neo Fields - GraphQL Integration Guide

## Overview

The API exports GraphQL type identifiers for all Neo block types, allowing you to automatically map block types to your GraphQL schema.

## GraphQL Type Name Format

Each Neo block type has a GraphQL type name following this pattern:

```
{fieldHandle}_{blockTypeHandle}_BlockType
```

### Examples

| Field Handle | Block Type Handle | GraphQL Type Name |
|--------------|-------------------|-------------------|
| `pageContentAll` | `heroHeader` | `pageContentAll_heroHeader_BlockType` |
| `contentBlocks` | `textBlock` | `contentBlocks_textBlock_BlockType` |
| `pageBuilder` | `section` | `pageBuilder_section_BlockType` |

## API Response

The `gqlTypeName` is included in both places:

### 1. In `fieldInfo.childFields`
```json
{
  "fieldInfo": {
    "childFields": [
      {
        "fieldType": "blockType",
        "fieldName": "heroHeader",
        "displayName": "Hero Header",
        "typeIds": ["heroHeader"],
        "gqlTypeName": "pageContentAll_heroHeader_BlockType"
      }
    ]
  }
}
```

### 2. In `blockTypes` array
```json
{
  "blockTypes": [
    {
      "typeHandle": "heroHeader",
      "typeName": "Hero Header",
      "typeId": 10,
      "gqlTypeName": "pageContentAll_heroHeader_BlockType",
      "childFields": [...]
    }
  ]
}
```

## Using GraphQL Type Names

### 1. Generate GraphQL Queries

Automatically create GraphQL fragments for each block type:

```javascript
function generateNeoFieldQuery(neoField) {
  const fieldHandle = neoField.fieldName;
  const fragments = neoField.neoFieldInfo.blockTypes
    .map(blockType => {
      const fields = blockType.childFields
        .map(field => `          ${field.fieldName}`)
        .join('\n');
      
      return `        ... on ${blockType.gqlTypeName} {
${fields}
        }`;
    })
    .join('\n');
  
  return `      ${fieldHandle} {
${fragments}
      }`;
}

// Usage
const query = `
  query GetEntry($id: [QueryArgument]) {
    entry(id: $id) {
      ... on pages_default_Entry {
${generateNeoFieldQuery(neoFieldData)}
      }
    }
  }
`;
```

### 2. TypeScript Type Generation

Create type-safe GraphQL types from the API response:

```typescript
interface NeoBlockType {
  typeHandle: string;
  typeName: string;
  gqlTypeName: string;
  childFields: Array<{
    fieldName: string;
    fieldType: string;
    isLocalizable: boolean;
  }>;
}

function generateTypeScriptTypes(blockTypes: NeoBlockType[]): string {
  return blockTypes
    .map(bt => generateBlockTypeInterface(bt))
    .join('\n\n');
}

function generateBlockTypeInterface(blockType: NeoBlockType): string {
  const fields = blockType.childFields
    .map(field => {
      const tsType = mapToTypeScriptType(field.fieldType);
      return `  ${field.fieldName}: ${tsType};`;
    })
    .join('\n');
  
  const interfaceName = blockType.gqlTypeName
    .replace(/-/g, '_')
    .replace(/BlockType$/, 'Block');
  
  return `
/**
 * ${blockType.typeName}
 * GraphQL Type: ${blockType.gqlTypeName}
 */
export interface ${interfaceName} {
  __typename: '${blockType.gqlTypeName}';
${fields}
}
  `.trim();
}

function mapToTypeScriptType(fieldType: string): string {
  const typeMap: Record<string, string> = {
    'string': 'string',
    'text': 'string',
    'richtext': 'string',
    'number': 'number',
    'boolean': 'boolean',
    'date': 'string | Date',
    'email': 'string',
    'url': 'string',
    'assets': 'Asset[]',
    'entries': 'Entry[]',
    'categories': 'Category[]',
    'tags': 'Tag[]',
    'users': 'User[]',
    'matrix': 'MatrixBlock[]',
    'neo': 'NeoBlock[]',
    'select': 'string',
    'multiselect': 'string[]',
    'radio': 'string',
  };
  
  return typeMap[fieldType] || 'any';
}
```

**Example Output:**
```typescript
/**
 * Hero Header
 * GraphQL Type: pageContentAll_heroHeader_BlockType
 */
export interface pageContentAll_heroHeader_Block {
  __typename: 'pageContentAll_heroHeader_BlockType';
  headline: string;
  subheadline: string;
  ctaText: string;
  ctaLink: string;
}

/**
 * Text Section
 * GraphQL Type: pageContentAll_textSection_BlockType
 */
export interface pageContentAll_textSection_Block {
  __typename: 'pageContentAll_textSection_BlockType';
  heading: string;
  body: string;
  alignment: string;
}
```

### 3. GraphQL Code Generator Configuration

Use with [GraphQL Code Generator](https://the-guild.dev/graphql/codegen):

```typescript
// codegen.ts
import type { CodegenConfig } from '@graphql-codegen/cli';

const config: CodegenConfig = {
  schema: 'https://your-site.com/api',
  documents: ['src/**/*.graphql'],
  generates: {
    './src/gql/': {
      preset: 'client',
      config: {
        // Use the API to know which Neo block types exist
        scalars: {
          // Map based on API field types
        }
      }
    }
  }
};

export default config;
```

### 4. Validate GraphQL Types

Check if block types exist in your GraphQL schema:

```typescript
async function validateNeoBlockTypes(
  neoField: any,
  graphqlEndpoint: string
) {
  const blockTypes = neoField.neoFieldInfo.blockTypes;
  const results = [];
  
  for (const blockType of blockTypes) {
    const exists = await checkTypeInSchema(
      blockType.gqlTypeName,
      graphqlEndpoint
    );
    
    results.push({
      blockType: blockType.typeHandle,
      gqlTypeName: blockType.gqlTypeName,
      existsInSchema: exists
    });
  }
  
  return results;
}

async function checkTypeInSchema(
  typeName: string,
  endpoint: string
): Promise<boolean> {
  const query = `
    query {
      __type(name: "${typeName}") {
        name
      }
    }
  `;
  
  const response = await fetch(endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ query })
  });
  
  const data = await response.json();
  return data.data?.__type?.name === typeName;
}
```

### 5. Dynamic Query Builder

Build GraphQL queries dynamically based on API response:

```javascript
class NeoGraphQLBuilder {
  constructor(apiData) {
    this.field = apiData;
    this.blockTypes = apiData.neoFieldInfo.blockTypes;
  }
  
  buildQuery(entryTypeHandle = 'default', siteHandle = 'default') {
    const fragments = this.buildFragments();
    
    return `
      query GetNeoContent($id: [QueryArgument]) {
        entry(id: $id) {
          ... on ${siteHandle}_${entryTypeHandle}_Entry {
            ${this.field.fieldName} {
${fragments}
            }
          }
        }
      }
    `;
  }
  
  buildFragments() {
    return this.blockTypes
      .map(bt => this.buildBlockTypeFragment(bt))
      .join('\n');
  }
  
  buildBlockTypeFragment(blockType) {
    const fields = this.buildFieldsList(blockType.childFields);
    
    return `              ... on ${blockType.gqlTypeName} {
                __typename
${fields}
              }`;
  }
  
  buildFieldsList(fields, indent = '                ') {
    return fields
      .map(field => {
        // Handle different field types
        if (field.fieldType === 'assets') {
          return `${indent}${field.fieldName} {
${indent}  url
${indent}  title
${indent}  alt
${indent}}`;
        }
        
        if (field.fieldType === 'neo' || field.fieldType === 'matrix') {
          return `${indent}${field.fieldName} {
${indent}  __typename
${indent}  # Add nested block fields here
${indent}}`;
        }
        
        return `${indent}${field.fieldName}`;
      })
      .join('\n');
  }
}

// Usage
const apiData = await fetchNeoFieldData();
const builder = new NeoGraphQLBuilder(apiData);
const query = builder.buildQuery('pages', 'default');
console.log(query);
```

### 6. React/Apollo Integration

Use with Apollo Client in React:

```typescript
import { gql, useQuery } from '@apollo/client';

// Generate query from API data
function useNeoField(entryId: string, fieldData: any) {
  const QUERY = gql`
    query GetNeoField($id: [QueryArgument]) {
      entry(id: $id) {
        ... on pages_default_Entry {
          ${fieldData.fieldName} {
            ${generateFragments(fieldData.neoFieldInfo.blockTypes)}
          }
        }
      }
    }
  `;
  
  return useQuery(QUERY, {
    variables: { id: [entryId] }
  });
}

function generateFragments(blockTypes: any[]) {
  return blockTypes
    .map(bt => `
      ... on ${bt.gqlTypeName} {
        __typename
        ${bt.childFields.map((f: any) => f.fieldName).join('\n        ')}
      }
    `)
    .join('');
}

// Component
function NeoFieldRenderer({ entryId, fieldConfig }) {
  const { data, loading, error } = useNeoField(entryId, fieldConfig);
  
  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error.message}</div>;
  
  const blocks = data.entry[fieldConfig.fieldName];
  
  return (
    <div>
      {blocks.map((block, index) => (
        <BlockRenderer 
          key={index}
          block={block}
          blockType={fieldConfig.neoFieldInfo.blockTypes.find(
            bt => bt.gqlTypeName === block.__typename
          )}
        />
      ))}
    </div>
  );
}
```

## Complete Example

### API Response
```json
{
  "fieldName": "pageBuilder",
  "type": "neo",
  "neoFieldInfo": {
    "blockTypes": [
      {
        "typeHandle": "hero",
        "gqlTypeName": "pageBuilder_hero_BlockType",
        "childFields": [
          {"fieldName": "title", "fieldType": "string"},
          {"fieldName": "backgroundImage", "fieldType": "assets"}
        ]
      },
      {
        "typeHandle": "contentSection",
        "gqlTypeName": "pageBuilder_contentSection_BlockType",
        "childFields": [
          {"fieldName": "heading", "fieldType": "string"},
          {"fieldName": "content", "fieldType": "richtext"}
        ]
      }
    ]
  }
}
```

### Generated GraphQL Query
```graphql
query GetPageBuilder($id: [QueryArgument]) {
  entry(id: $id) {
    ... on pages_default_Entry {
      pageBuilder {
        ... on pageBuilder_hero_BlockType {
          __typename
          title
          backgroundImage {
            url
            title
            alt
          }
        }
        ... on pageBuilder_contentSection_BlockType {
          __typename
          heading
          content
        }
      }
    }
  }
}
```

### Generated TypeScript Types
```typescript
export type PageBuilderBlock = 
  | pageBuilder_hero_Block
  | pageBuilder_contentSection_Block;

export interface pageBuilder_hero_Block {
  __typename: 'pageBuilder_hero_BlockType';
  title: string;
  backgroundImage: Asset[];
}

export interface pageBuilder_contentSection_Block {
  __typename: 'pageBuilder_contentSection_BlockType';
  heading: string;
  content: string;
}
```

## Best Practices

1. **Cache the API response** - Fetch field structure once and cache it
2. **Generate types at build time** - Run code generation as part of your build process
3. **Validate schema** - Check that GraphQL types match your API data
4. **Version control** - Commit generated types to track schema changes
5. **Document mappings** - Keep a record of field handle to GraphQL type mappings

## Troubleshooting

### Type name doesn't match GraphQL schema

The API generates names based on Neo's pattern. If your schema uses custom names:

```javascript
function mapToCustomTypeName(gqlTypeName, customMappings) {
  return customMappings[gqlTypeName] || gqlTypeName;
}
```

### Field names differ between API and GraphQL

Create a mapping file:

```typescript
const fieldMappings: Record<string, string> = {
  'pageContentAll': 'pageContent',
  // Add more mappings as needed
};
```

### Nested Neo fields

For nested Neo fields, use the `typeIds` array to get child block types:

```javascript
function generateNestedFragment(field, allBlockTypes) {
  if (field.typeIds && field.typeIds.length > 0) {
    // Generate fragments for nested block types
    const nestedFragments = field.typeIds
      .map(typeId => allBlockTypes.find(bt => bt.typeHandle === typeId))
      .filter(Boolean)
      .map(bt => generateFragment(bt))
      .join('\n');
    
    return `${field.fieldName} {
${nestedFragments}
    }`;
  }
  
  return field.fieldName;
}
```

## Summary

✅ **GraphQL type names** are exported in `gqlTypeName` field  
✅ Format: `{fieldHandle}_{blockTypeHandle}_BlockType`  
✅ Available in both `fieldInfo` and `blockTypes` arrays  
✅ Use for query generation, type generation, and validation  
✅ Compatible with Apollo, Relay, and GraphQL Code Generator  

For more information, see:
- `README.md` - GraphQL Type Names section
- `EXAMPLE_NEO_RESPONSE.md` - GraphQL Integration examples
- `NEO_FIELD_SUPPORT.md` - Technical implementation


