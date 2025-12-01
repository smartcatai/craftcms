# Neo Child Blocks Configuration - Complete Guide

## What is Child Blocks Configuration?

In Neo, you can control **which block types** are allowed as children of a specific block type. This creates hierarchical content structures with enforced rules.

## How It's Exported

The `childBlocks` field in block type metadata shows which blocks can be children:

### Value Types

| Value | Meaning | Example Use Case |
|-------|---------|------------------|
| `true` or `"*"` | All block types can be children | Flexible container block |
| `["handle1", "handle2"]` | Only specific block types allowed | Section that only allows columns |
| `false` or `null` | No child blocks allowed | Leaf-level content blocks |

## Complete API Response

```json
{
  "typeHandle": "section",
  "typeName": "Section",
  "typeId": 10,
  "childFields": [...],
  "metadata": {
    "enabled": true,
    "description": "A content section",
    
    // Which blocks can be children
    "childBlocks": ["column", "textBlock"],
    
    // Child block constraints
    "minChildBlocks": 1,
    "maxChildBlocks": 4,
    "groupChildBlockTypes": true,
    
    // Block placement
    "topLevel": true,
    
    // Block count limits
    "minBlocks": 0,
    "maxBlocks": 0,
    "minSiblingBlocks": 0,
    "maxSiblingBlocks": 0
  }
}
```

## Related Metadata Fields

### Child Block Settings
- **`childBlocks`** - Which block types can be children
  - `true`/`"*"` = all blocks
  - `["handle1", "handle2"]` = specific blocks
  - `false`/`null` = no children
  
- **`minChildBlocks`** - Minimum number of child blocks required (integer)
- **`maxChildBlocks`** - Maximum number of child blocks allowed (integer)
- **`groupChildBlockTypes`** - Group child block types in UI (boolean)

### Placement Settings
- **`topLevel`** - Can this block be at the top level? (boolean)

### Count Constraints
- **`minBlocks`** - Minimum instances of this block type (integer)
- **`maxBlocks`** - Maximum instances of this block type (integer)
- **`minSiblingBlocks`** - Minimum sibling blocks at same level (integer)
- **`maxSiblingBlocks`** - Maximum sibling blocks at same level (integer)

## Practical Examples

### Example 1: Flexible Container

**Requirement:** A "section" block that can contain any other block type

```json
{
  "typeHandle": "section",
  "metadata": {
    "childBlocks": true,
    "topLevel": true,
    "minChildBlocks": 0,
    "maxChildBlocks": 0
  }
}
```

**Result:** 
- ✅ Can contain any block type as children
- ✅ Can be at top level
- ✅ No minimum or maximum child blocks

### Example 2: Restricted Container

**Requirement:** A "columns" block that only allows "column" blocks as children

```json
{
  "typeHandle": "columns",
  "metadata": {
    "childBlocks": ["column"],
    "topLevel": true,
    "minChildBlocks": 2,
    "maxChildBlocks": 4
  }
}
```

**Result:**
- ✅ Only "column" blocks can be children
- ✅ Must have 2-4 column blocks
- ✅ Can be at top level

### Example 3: Content Block (No Children)

**Requirement:** A "text" block that cannot have children

```json
{
  "typeHandle": "textBlock",
  "metadata": {
    "childBlocks": false,
    "topLevel": false,
    "minChildBlocks": 0,
    "maxChildBlocks": 0
  }
}
```

**Result:**
- ✅ Cannot have any child blocks
- ✅ Cannot be at top level (must be nested)
- ✅ This is a "leaf" block

### Example 4: Multi-Purpose Container

**Requirement:** A "feature" block that can contain text, images, or buttons

```json
{
  "typeHandle": "feature",
  "metadata": {
    "childBlocks": ["textBlock", "imageBlock", "buttonBlock"],
    "topLevel": true,
    "minChildBlocks": 1,
    "maxChildBlocks": 10,
    "groupChildBlockTypes": true
  }
}
```

**Result:**
- ✅ Can contain text, image, or button blocks only
- ✅ Must have at least 1 child
- ✅ Can have up to 10 children
- ✅ Child block types are grouped in UI

## Real-World Use Case: Page Builder

### Structure
```
Page
├── Hero Section (topLevel: true, childBlocks: false)
├── Content Section (topLevel: true, childBlocks: ["column"])
│   ├── Column (topLevel: false, childBlocks: ["textBlock", "imageBlock"])
│   │   ├── Text Block (topLevel: false, childBlocks: false)
│   │   └── Image Block (topLevel: false, childBlocks: false)
│   └── Column (topLevel: false, childBlocks: ["textBlock", "imageBlock"])
│       └── Text Block (topLevel: false, childBlocks: false)
└── Footer Section (topLevel: true, childBlocks: false)
```

### API Response

#### Hero Section
```json
{
  "typeHandle": "heroSection",
  "metadata": {
    "childBlocks": false,
    "topLevel": true,
    "maxBlocks": 1
  }
}
```

#### Content Section
```json
{
  "typeHandle": "contentSection",
  "metadata": {
    "childBlocks": ["column"],
    "topLevel": true,
    "minChildBlocks": 1,
    "maxChildBlocks": 4
  }
}
```

#### Column
```json
{
  "typeHandle": "column",
  "metadata": {
    "childBlocks": ["textBlock", "imageBlock", "videoBlock"],
    "topLevel": false,
    "minChildBlocks": 0,
    "maxChildBlocks": 10
  }
}
```

#### Content Blocks
```json
{
  "typeHandle": "textBlock",
  "metadata": {
    "childBlocks": false,
    "topLevel": false
  }
}
```

## Using childBlocks Data

### JavaScript Example: Validate Structure

```javascript
function canAddChildBlock(parentBlockType, childBlockHandle) {
  const childBlocks = parentBlockType.metadata.childBlocks;
  
  // No children allowed
  if (childBlocks === false || childBlocks === null) {
    return false;
  }
  
  // All children allowed
  if (childBlocks === true || childBlocks === '*') {
    return true;
  }
  
  // Specific children allowed
  if (Array.isArray(childBlocks)) {
    return childBlocks.includes(childBlockHandle);
  }
  
  return false;
}

// Usage
const sectionBlock = blockTypes.find(bt => bt.typeHandle === 'section');
console.log(canAddChildBlock(sectionBlock, 'column')); // true
console.log(canAddChildBlock(sectionBlock, 'hero')); // false
```

### PHP Example: Build Structure Map

```php
// Build a map of parent-child relationships
$structure = [];

foreach ($neoField['neoFieldInfo']['blockTypes'] as $blockType) {
    $handle = $blockType['typeHandle'];
    $childBlocks = $blockType['metadata']['childBlocks'];
    
    $structure[$handle] = [
        'name' => $blockType['typeName'],
        'topLevel' => $blockType['metadata']['topLevel'],
        'allowedChildren' => $childBlocks === true || $childBlocks === '*'
            ? 'all'
            : ($childBlocks ?: []),
        'minChildren' => $blockType['metadata']['minChildBlocks'] ?? 0,
        'maxChildren' => $blockType['metadata']['maxChildBlocks'] ?? null
    ];
}

// Check if a block can be nested in another
function canNest($parentHandle, $childHandle, $structure) {
    $parent = $structure[$parentHandle];
    
    if ($parent['allowedChildren'] === 'all') {
        return true;
    }
    
    return in_array($childHandle, $parent['allowedChildren']);
}
```

### TypeScript Example: Generate Documentation

```typescript
interface BlockTypeMetadata {
  childBlocks: boolean | string | string[];
  topLevel: boolean;
  minChildBlocks: number;
  maxChildBlocks: number;
}

function describeChildBlockRules(metadata: BlockTypeMetadata): string {
  const { childBlocks, minChildBlocks, maxChildBlocks } = metadata;
  
  if (childBlocks === false || childBlocks === null) {
    return "This block cannot have children.";
  }
  
  if (childBlocks === true || childBlocks === '*') {
    return `This block can contain any block type${
      minChildBlocks ? ` (minimum: ${minChildBlocks})` : ''
    }${
      maxChildBlocks ? ` (maximum: ${maxChildBlocks})` : ''
    }.`;
  }
  
  if (Array.isArray(childBlocks)) {
    return `This block can only contain: ${childBlocks.join(', ')}${
      minChildBlocks ? ` (minimum: ${minChildBlocks})` : ''
    }${
      maxChildBlocks ? ` (maximum: ${maxChildBlocks})` : ''
    }.`;
  }
  
  return "No child block rules defined.";
}
```

## Migration Scenarios

### Scenario 1: Importing Content Structure

When importing Neo content from another system, use `childBlocks` to validate structure:

```javascript
function validateNeoStructure(data, blockTypes) {
  for (const block of data.blocks) {
    const blockType = blockTypes.find(bt => bt.typeHandle === block.type);
    
    if (!blockType) {
      throw new Error(`Unknown block type: ${block.type}`);
    }
    
    // Check if block can be at top level
    if (block.level === 1 && !blockType.metadata.topLevel) {
      throw new Error(`${block.type} cannot be at top level`);
    }
    
    // Validate children
    if (block.children) {
      for (const child of block.children) {
        if (!canAddChildBlock(blockType, child.type)) {
          throw new Error(
            `${child.type} cannot be a child of ${block.type}`
          );
        }
      }
      
      // Check child count constraints
      const childCount = block.children.length;
      const { minChildBlocks, maxChildBlocks } = blockType.metadata;
      
      if (minChildBlocks && childCount < minChildBlocks) {
        throw new Error(
          `${block.type} requires at least ${minChildBlocks} children`
        );
      }
      
      if (maxChildBlocks && childCount > maxChildBlocks) {
        throw new Error(
          `${block.type} allows at most ${maxChildBlocks} children`
        );
      }
    }
  }
  
  return true;
}
```

### Scenario 2: UI Builder

Use `childBlocks` to show only valid block types when adding children:

```javascript
function getAvailableChildBlocks(parentBlockHandle, allBlockTypes) {
  const parentType = allBlockTypes.find(bt => bt.typeHandle === parentBlockHandle);
  
  if (!parentType) return [];
  
  const childBlocks = parentType.metadata.childBlocks;
  
  // All blocks available
  if (childBlocks === true || childBlocks === '*') {
    return allBlockTypes;
  }
  
  // No blocks available
  if (!childBlocks) {
    return [];
  }
  
  // Specific blocks available
  return allBlockTypes.filter(bt => childBlocks.includes(bt.typeHandle));
}
```

## Troubleshooting

### childBlocks is a string instead of array

Sometimes `childBlocks` might be JSON-encoded. The API automatically decodes it, but if you're working with raw data:

```javascript
let childBlocks = blockType.metadata.childBlocks;

if (typeof childBlocks === 'string') {
  try {
    childBlocks = JSON.parse(childBlocks);
  } catch (e) {
    // Handle as single value or boolean
    if (childBlocks === 'true' || childBlocks === '*') {
      childBlocks = true;
    }
  }
}
```

### Checking if a block can have any children

```javascript
function canHaveChildren(blockType) {
  const childBlocks = blockType.metadata.childBlocks;
  return childBlocks !== false && childBlocks !== null;
}
```

### Getting the count of allowed child types

```javascript
function getAllowedChildTypeCount(blockType) {
  const childBlocks = blockType.metadata.childBlocks;
  
  if (childBlocks === false || childBlocks === null) return 0;
  if (childBlocks === true || childBlocks === '*') return Infinity;
  if (Array.isArray(childBlocks)) return childBlocks.length;
  
  return 0;
}
```

## Summary

✅ **`childBlocks`** is fully exported in the API response  
✅ Shows which block types can be children: `true`, array of handles, or `false`  
✅ Additional metadata: `minChildBlocks`, `maxChildBlocks`, `groupChildBlockTypes`  
✅ Use it to validate structure, build UIs, and migrate content  
✅ Combined with `topLevel`, creates complete hierarchical rules  

For more examples, see `EXAMPLE_NEO_RESPONSE.md`.


