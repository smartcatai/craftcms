<?php

namespace smartcat\smartcatintegration\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use yii\web\BadRequestHttpException;

/**
 * API Controller
 *
 * @author Smartcat <support@smartcat.com>
 * @since 1.0.0
 */
class ApiController extends Controller
{
    /**
     * @inheritdoc
     */
    public $defaultAction = 'fields';

    /**
     * @inheritdoc
     */
    protected array|bool|int $allowAnonymous = ['fields', 'sites', 'sections', 'types'];

    /**
     * Returns field information for the specified section and entry type
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionFields(): Response
    {
        $request = Craft::$app->getRequest();
        $sectionHandle = $request->getQueryParam('sectionHandle');
        $typeHandle = $request->getQueryParam('typeHandle');
        $sectionId = $request->getQueryParam('sectionId');

        if (!$sectionHandle) {
            throw new BadRequestHttpException('The "sectionHandle" parameter is required.');
        }

        if (!$typeHandle) {
            throw new BadRequestHttpException('The "typeHandle" parameter is required.');
        }

        $fields = $this->getFieldsForSectionAndType($sectionHandle, $typeHandle, $sectionId);

        return $this->asJson($fields);
    }

    /**
     * Returns a list of all sections
     *
     * @return Response
     */
    public function actionSections(): Response
    {
        $sections = $this->getAllSections();
        return $this->asJson($sections);
    }

    /**
     * Returns a list of entry types for a specific section
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionTypes(): Response
    {
        $request = Craft::$app->getRequest();
        $sectionHandle = $request->getQueryParam('sectionHandle');
        $sectionId = $request->getQueryParam('sectionId');

        if (!$sectionHandle && !$sectionId) {
            throw new BadRequestHttpException('Either "sectionHandle" or "sectionId" parameter is required.');
        }

        $types = $this->getTypesForSection($sectionHandle, $sectionId);
        return $this->asJson($types);
    }

    /**
     * Returns a list of available sites
     *
     * @return Response
     */
    public function actionSites(): Response
    {
        $sites = $this->getSites();
        return $this->asJson($sites);
    }



    /**
     * Get all sections
     *
     * @return array
     */
    private function getAllSections(): array
    {
        $sections = [];
        $entriesService = Craft::$app->getEntries();
        $allSections = $entriesService->getAllSections();
        
        foreach ($allSections as $section) {
            $sections[] = [
                'id' => $section->id,
                'handle' => $section->handle,
                'name' => $section->name,
                'type' => $section->type,
            ];
        }
        
        return $sections;
    }

    /**
     * Get entry types for a specific section
     *
     * @param string|null $sectionHandle
     * @param int|null $sectionId
     * @return array
     * @throws BadRequestHttpException
     */
    private function getTypesForSection(?string $sectionHandle = null, ?int $sectionId = null): array
    {
        $entriesService = Craft::$app->getEntries();
        
        // Get section by ID or handle
        if ($sectionId) {
            $section = $entriesService->getSectionById($sectionId);
            if (!$section) {
                throw new BadRequestHttpException("Section not found with ID: {$sectionId}");
            }
            // If both ID and handle are provided, verify they match
            if ($sectionHandle && $section->handle !== $sectionHandle) {
                throw new BadRequestHttpException("Section handle mismatch for ID {$sectionId}. Expected: {$sectionHandle}, Found: {$section->handle}");
            }
        } else {
            $section = $entriesService->getSectionByHandle($sectionHandle);
            if (!$section) {
                throw new BadRequestHttpException("Section not found with handle: {$sectionHandle}");
            }
        }
        
        $types = [];
        $entryTypes = $section->getEntryTypes();
        
        foreach ($entryTypes as $entryType) {
            $types[] = [
                'id' => $entryType->id,
                'handle' => $entryType->handle,
                'name' => $entryType->name,
                'sectionId' => $section->id,
                'sectionHandle' => $section->handle,
                'sectionName' => $section->name,
                'hasTitleField' => $entryType->hasTitleField,
                'titleTranslationMethod' => $entryType->titleTranslationMethod,
                'titleTranslationKeyFormat' => $entryType->titleTranslationKeyFormat,
                'titleFormat' => $entryType->titleFormat,
                'fieldsCount' => count($entryType->getFieldLayout()->getCustomFields() ?? [])
            ];
        }
        
        return $types;
    }

    /**
     * Get section site settings
     *
     * @param mixed $section
     * @return array
     */
    private function getSectionSiteSettings($section): array
    {
        $siteSettings = [];
        $allSiteSettings = $section->getSiteSettings();
        
        foreach ($allSiteSettings as $siteSetting) {
            $siteSettings[] = [
                'siteId' => $siteSetting->siteId,
                'hasUrls' => $siteSetting->hasUrls,
                'uriFormat' => $siteSetting->uriFormat,
                'template' => $siteSetting->template,
                'enabledByDefault' => $siteSetting->enabledByDefault
            ];
        }
        
        return $siteSettings;
    }

    /**
     * Get available sites
     *
     * @return array
     */
    private function getSites(): array
    {
        $sites = [];
        $allSites = Craft::$app->getSites()->getAllSites();
        
        foreach ($allSites as $site) {
            $sites[] = [
                'id' => $site->id,
                'handle' => $site->handle,
                'name' => $site->name,
                'language' => $site->language,
                'primary' => $site->primary,
                'enabled' => $site->enabled,
                'baseUrl' => $site->baseUrl,
                'hasUrls' => $site->hasUrls
            ];
        }
        
        return $sites;
    }



    /**
     * Get fields for the specified section and entry type
     *
     * @param string $sectionHandle
     * @param string $typeHandle
     * @param int|null $sectionId
     * @return array
     * @throws BadRequestHttpException
     */
    private function getFieldsForSectionAndType(string $sectionHandle, string $typeHandle, ?int $sectionId = null): array
    {
        $fields = [];
        $entriesService = Craft::$app->getEntries();
        
        // If sectionId is provided, get section by ID for optimization
        if ($sectionId) {
            $section = $entriesService->getSectionById($sectionId);
            // Verify the handle matches for security
            if (!$section || $section->handle !== $sectionHandle) {
                throw new BadRequestHttpException("Section not found or handle mismatch: {$sectionHandle}");
            }
        } else {
            $section = $entriesService->getSectionByHandle($sectionHandle);
            if (!$section) {
                throw new BadRequestHttpException("Section not found: {$sectionHandle}");
            }
        }
        
        // Find the specific entry type
        $entryTypes = $section->getEntryTypes();
        $targetEntryType = null;
        
        foreach ($entryTypes as $entryType) {
            if ($entryType->handle === $typeHandle) {
                $targetEntryType = $entryType;
                break;
            }
        }
        
        if (!$targetEntryType) {
            throw new BadRequestHttpException("Entry type not found: {$typeHandle} in section {$sectionHandle}");
        }
        
        // Add title field if the entry type has one
        if ($targetEntryType->hasTitleField) {
            $titleField = [
                'fieldName' => 'title',
                'displayName' => 'Title',
                'isLocalizable' => $this->getTitleLocalizationStatus($targetEntryType),
                'type' => 'string',
                'section' => $section->name,
                'sectionHandle' => $section->handle,
                'sectionId' => $section->id,
                'entryType' => $targetEntryType->name,
                'entryTypeHandle' => $targetEntryType->handle,
                'entryTypeId' => $targetEntryType->id,
                'debugInfo' => [
                    'fieldClass' => 'Title Field',
                    'isMatrixField' => false,
                    'fieldHandle' => 'title'
                ]
            ];
            
            $fields[] = $titleField;
        }
        
        // Get fields for the specific entry type
        $fieldLayout = $targetEntryType->getFieldLayout();
        if ($fieldLayout) {
            $customFields = $fieldLayout->getCustomFields();
            
            foreach ($customFields as $field) {
                $fieldInfo = $this->formatFieldInfo($field);
                $fieldInfo['section'] = $section->name;
                $fieldInfo['sectionHandle'] = $section->handle;
                $fieldInfo['sectionId'] = $section->id;
                $fieldInfo['entryType'] = $targetEntryType->name;
                $fieldInfo['entryTypeHandle'] = $targetEntryType->handle;
                $fieldInfo['entryTypeId'] = $targetEntryType->id;
                
                // Add matrix/neo field information if applicable
                $fieldClassName = get_class($field);
                $isMatrix = $this->isMatrixField($field);
                $isNeo = $this->isNeoField($field);
                
                $fieldInfo['debugInfo'] = [
                    'fieldClass' => $fieldClassName,
                    'isMatrixField' => $isMatrix,
                    'isNeoField' => $isNeo,
                    'fieldHandle' => $field->handle ?? 'unknown'
                ];
                
                if ($isMatrix) {
                    $fieldInfo['matrixFieldInfo'] = $this->getMatrixFieldInfoSimple($field);
                } elseif ($isNeo) {
                    $fieldInfo['neoFieldInfo'] = $this->getNeoFieldInfo($field);
                }
                
                $fields[] = $fieldInfo;
            }
        }
        
        return $fields;
    }

    /**
     * Format field information
     *
     * @param mixed $field
     * @return array
     */
    private function formatFieldInfo($field): array
    {
        try {
            return [
                'fieldName' => $field->handle ?? 'unknown',
                'displayName' => $field->name ?? 'Unknown Field',
                'isLocalizable' => $this->getFieldLocalizationStatus($field),
                'type' => $this->getFieldTypeString($field)
            ];
        } catch (\Exception $e) {
            // If there's any error processing the field, return a safe default
            return [
                'fieldName' => $field->handle ?? 'unknown',
                'displayName' => $field->name ?? 'Unknown Field',
                'isLocalizable' => false,
                'type' => 'unknown'
            ];
        }
    }

    /**
     * Get field localization status safely
     *
     * @param mixed $field
     * @return bool
     */
    private function getFieldLocalizationStatus($field): bool
    {
        try {
            if (property_exists($field, 'translationMethod')) {
                return (bool) $field->translationMethod !== 'none';
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get title field localization status based on entry type settings
     *
     * @param mixed $entryType
     * @return bool
     */
    private function getTitleLocalizationStatus($entryType): bool
    {
        try {
            if (property_exists($entryType, 'titleTranslationMethod')) {
                return (bool) $entryType->titleTranslationMethod !== 'none';
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get field type as string
     *
     * @param mixed $field
     * @return string
     */
    private function getFieldTypeString($field): string
    {
        try {
            $className = get_class($field);
            
            // Check for Neo field first (full class name check)
            if ($this->isNeoField($field)) {
                return 'neo';
            }
            
            $parts = explode('\\', $className);
            $fieldType = end($parts);
            
            // Convert common field types to more readable names
            $typeMap = [
                'PlainText' => 'string',
                'Textarea' => 'text',
                'RichText' => 'richtext',
                'Field' => 'richtext', // CKEditor field
                'Number' => 'number',
                'Email' => 'email',
                'Url' => 'url',
                'Date' => 'date',
                'Lightswitch' => 'boolean',
                'Dropdown' => 'select',
                'Checkboxes' => 'multiselect',
                'RadioButtons' => 'radio',
                'Entries' => 'entries',
                'Categories' => 'categories',
                'Assets' => 'assets',
                'Users' => 'users',
                'Tags' => 'tags',
                'Matrix' => 'matrix',
                'Table' => 'table'
            ];

            return $typeMap[$fieldType] ?? strtolower($fieldType);
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Check if field is a matrix field
     *
     * @param mixed $field
     * @return bool
     */
    private function isMatrixField($field): bool
    {
        try {
            $className = get_class($field);
            return strpos($className, 'Matrix') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if field is a Neo field
     *
     * @param mixed $field
     * @return bool
     */
    private function isNeoField($field): bool
    {
        try {
            $className = get_class($field);
            return strpos($className, 'benf\\neo\\Field') !== false || strpos($className, 'benf\neo\Field') !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get matrix field information - simple version
     *
     * @param mixed $matrixField
     * @return array
     */
    private function getMatrixFieldInfoSimple($matrixField): array
    {
        $result = [
            'fieldInfo' => ['childFields' => []],
            'nestedTypes' => [],
            'debug' => []
        ];
        
        try {
            $result['debug'][] = 'Matrix field ID: ' . ($matrixField->id ?? 'unknown');
            $result['debug'][] = 'Matrix field handle: ' . ($matrixField->handle ?? 'unknown');
            $result['debug'][] = 'Craft version: ' . Craft::$app->getVersion();
            $result['debug'][] = 'Field class: ' . get_class($matrixField);
            
            // In Craft 5, Matrix fields are now Entry fields with nested entries
            // The entry types are accessed differently
            $entryTypes = [];
            
            // Method 1: Try getEntryTypes() - this is the Craft 5 way
            if (method_exists($matrixField, 'getEntryTypes')) {
                $entryTypes = $matrixField->getEntryTypes();
                $result['debug'][] = 'getEntryTypes() returned ' . count($entryTypes) . ' entry types';
            }
            
            // Method 2: Try property access
            if (empty($entryTypes) && property_exists($matrixField, 'entryTypes')) {
                $entryTypes = $matrixField->entryTypes;
                $result['debug'][] = 'entryTypes property returned ' . count($entryTypes ?? []) . ' entry types';
            }
            
            // Method 3: Check if there are specific entry types for this field
            if (empty($entryTypes)) {
                $result['debug'][] = 'Trying entries service to get entry types for this field';
                
                // Try to get entry types that are available for this field
                $entriesService = Craft::$app->getEntries();
                if (method_exists($entriesService, 'getAllEntryTypes')) {
                    $allEntryTypes = $entriesService->getAllEntryTypes();
                    $result['debug'][] = 'Found ' . count($allEntryTypes) . ' total entry types in system';
                    
                    // In Craft 5, we'd need to check which entry types are available for Matrix fields
                    // This is more complex as it depends on field configuration
                    foreach ($allEntryTypes as $entryType) {
                        if ($entryType->name && $entryType->handle) {
                            $entryTypes[] = $entryType;
                        }
                    }
                    $result['debug'][] = 'Using ' . count($entryTypes) . ' entry types';
                }
            }
            
            $result['debug'][] = 'Final entry types count: ' . count($entryTypes ?? []);
            
            if (!empty($entryTypes)) {
                foreach ($entryTypes as $entryType) {
                    $result['fieldInfo']['childFields'][] = [
                        'fieldType' => 'entryType',
                        'fieldName' => $entryType->handle ?? 'unknown',
                        'displayName' => $entryType->name ?? 'Unknown',
                        'typeIds' => [$entryType->handle ?? 'unknown']
                    ];
                    
                    // Get the fields for this entry type
                    $childFields = [];
                    try {
                        // Add title field if the entry type has one
                        if (property_exists($entryType, 'hasTitleField') && $entryType->hasTitleField) {
                            $childFields[] = [
                                'fieldType' => 'string',
                                'fieldName' => 'title',
                                'displayName' => 'Title',
                                'isLocalizable' => $this->getTitleLocalizationStatus($entryType)
                            ];
                            $result['debug'][] = 'Entry type ' . $entryType->handle . ' has title field';
                        }
                        
                        $fieldLayout = $entryType->getFieldLayout();
                        if ($fieldLayout) {
                            $customFields = $fieldLayout->getCustomFields();
                            $result['debug'][] = 'Entry type ' . $entryType->handle . ' has ' . count($customFields) . ' custom fields';
                            
                            foreach ($customFields as $field) {
                                $childFieldInfo = [
                                    'fieldType' => $this->getFieldTypeString($field),
                                    'fieldName' => $field->handle ?? 'unknown',
                                    'displayName' => $field->name ?? 'Unknown',
                                    'isLocalizable' => $this->getFieldLocalizationStatus($field)
                                ];
                                
                                // If this nested field is also a matrix, get its type IDs
                                if ($this->isMatrixField($field)) {
                                    $nestedEntryTypes = [];
                                    try {
                                        if (method_exists($field, 'getEntryTypes')) {
                                            $nestedEntryTypes = $field->getEntryTypes();
                                        }
                                        
                                        $typeIds = [];
                                        foreach ($nestedEntryTypes as $nestedEntryType) {
                                            $typeIds[] = $nestedEntryType->handle ?? 'unknown';
                                        }
                                        $childFieldInfo['typeIds'] = $typeIds;
                                        $result['debug'][] = 'Nested matrix field ' . $field->handle . ' has ' . count($typeIds) . ' entry types';
                                    } catch (\Exception $e) {
                                        $result['debug'][] = 'Error getting nested entry types: ' . $e->getMessage();
                                    }
                                } elseif ($this->isNeoField($field)) {
                                    // If this nested field is a Neo field, get its block types
                                    $nestedBlockTypes = [];
                                    try {
                                        if (method_exists($field, 'getBlockTypes')) {
                                            $nestedBlockTypes = $field->getBlockTypes();
                                        }
                                        
                                        $typeIds = [];
                                        foreach ($nestedBlockTypes as $nestedBlockType) {
                                            $typeIds[] = $nestedBlockType->handle ?? 'unknown';
                                        }
                                        $childFieldInfo['typeIds'] = $typeIds;
                                        $result['debug'][] = 'Nested Neo field ' . $field->handle . ' has ' . count($typeIds) . ' block types';
                                    } catch (\Exception $e) {
                                        $result['debug'][] = 'Error getting nested Neo block types: ' . $e->getMessage();
                                    }
                                }
                                
                                $childFields[] = $childFieldInfo;
                            }
                        } else {
                            $result['debug'][] = 'Entry type ' . $entryType->handle . ' has no field layout';
                        }
                    } catch (\Exception $e) {
                        $result['debug'][] = 'Error getting fields for entry type ' . $entryType->handle . ': ' . $e->getMessage();
                    }
                    
                    $result['nestedTypes'][] = [
                        'typeHandle' => $entryType->handle ?? 'unknown',
                        'typeName' => $entryType->name ?? 'Unknown',
                        'typeId' => $entryType->id ?? null,
                        'childFields' => $childFields
                    ];
                }
            } else {
                $result['debug'][] = 'No entry types found - In Craft 5, Matrix fields use entry types instead of block types';
                $result['info'] = 'Craft 5 detected: Matrix fields now use Entry types. This field may not have entry types configured.';
            }
            
        } catch (\Exception $e) {
            $result['error'] = 'Error: ' . $e->getMessage();
            $result['debug'][] = 'Exception occurred: ' . $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Get matrix field information including nested types and fields
     *
     * @param mixed $matrixField
     * @return array
     */
    private function getMatrixFieldInfo($matrixField): array
    {
        $matrixInfo = [
            'fieldInfo' => [
                'childFields' => []
            ],
            'nestedTypes' => []
        ];
        
        $debugInfo = [];
        $blockTypes = [];
        
        try {
            // Add basic field info
            $debugInfo[] = 'Matrix field ID: ' . ($matrixField->id ?? 'unknown');
            $debugInfo[] = 'Matrix field handle: ' . ($matrixField->handle ?? 'unknown');
            
            // Try direct database approach first (most reliable)
            try {
                $blockTypeRecords = \craft\records\MatrixBlockType::find()
                    ->where(['fieldId' => $matrixField->id])
                    ->all();
                $debugInfo[] = 'Found ' . count($blockTypeRecords) . ' block type records in database';
                
                if (count($blockTypeRecords) > 0) {
                    // Convert records to models
                    foreach ($blockTypeRecords as $record) {
                        $blockType = new \craft\models\MatrixBlockType();
                        $blockType->id = $record->id;
                        $blockType->fieldId = $record->fieldId;
                        $blockType->name = $record->name;
                        $blockType->handle = $record->handle;
                        $blockType->sortOrder = $record->sortOrder;
                        $blockType->fieldLayoutId = $record->fieldLayoutId;
                        $blockTypes[] = $blockType;
                    }
                    $debugInfo[] = 'Successfully created ' . count($blockTypes) . ' block type models';
                    $debugInfo[] = 'Block type handles: ' . implode(', ', array_map(function($bt) { return $bt->handle; }, $blockTypes));
                }
                
            } catch (\Exception $e) {
                $debugInfo[] = 'Database approach failed: ' . $e->getMessage();
            }
            
        } catch (\Exception $e) {
            $debugInfo[] = 'Overall error: ' . $e->getMessage();
            return [
                'fieldInfo' => ['childFields' => []],
                'nestedTypes' => [],
                'error' => 'Failed to process matrix field: ' . $e->getMessage(),
                'debug' => $debugInfo
            ];
        }
        
        // Process the block types we found
        $processedBlockTypes = [];
        foreach ($blockTypes as $blockType) {
            $blockTypeInfo = $this->processMatrixBlockType($blockType, $processedBlockTypes);
            
            if ($blockTypeInfo) {
                // Add to fieldInfo.childFields
                $matrixInfo['fieldInfo']['childFields'][] = [
                    'fieldType' => 'blockType',
                    'fieldName' => $blockType->handle,
                    'displayName' => $blockType->name,
                    'typeIds' => [$blockType->handle]
                ];
                
                // Add to nestedTypes if not already processed
                if (!in_array($blockType->handle, array_column($matrixInfo['nestedTypes'], 'typeHandle'))) {
                    $matrixInfo['nestedTypes'][] = $blockTypeInfo;
                }
            }
        }

        // Add debug info to successful response
        $matrixInfo['debug'] = $debugInfo;
        
        return $matrixInfo;
    }

    /**
     * Process a matrix block type and return its field information
     *
     * @param mixed $blockType
     * @param array &$processedBlockTypes
     * @param int $depth
     * @return array|null
     */
    private function processMatrixBlockType($blockType, &$processedBlockTypes, int $depth = 0): ?array
    {
        // Prevent infinite recursion
        if ($depth > 5 || in_array($blockType->handle, $processedBlockTypes)) {
            return null;
        }

        $processedBlockTypes[] = $blockType->handle;

        try {
            $blockTypeInfo = [
                'typeHandle' => $blockType->handle,
                'typeName' => $blockType->name,
                'typeId' => $blockType->id,
                'childFields' => []
            ];

            // Add title field if the block type has one
            if (property_exists($blockType, 'hasTitleField') && $blockType->hasTitleField) {
                $blockTypeInfo['childFields'][] = [
                    'fieldType' => 'string',
                    'fieldName' => 'title',
                    'displayName' => 'Title',
                    'isLocalizable' => $this->getTitleLocalizationStatus($blockType)
                ];
            }

            // Get the field layout for this block type
            $fieldLayout = $blockType->getFieldLayout();
            if ($fieldLayout) {
                $customFields = $fieldLayout->getCustomFields();
                
                foreach ($customFields as $field) {
                    $childFieldInfo = [
                        'fieldType' => $this->getFieldTypeString($field),
                        'fieldName' => $field->handle,
                        'displayName' => $field->name,
                        'isLocalizable' => $this->getFieldLocalizationStatus($field)
                    ];

                    // If this nested field is also a matrix, get its type IDs
                    if ($this->isMatrixField($field)) {
                        $nestedBlockTypes = [];
                        
                        // Use the same approach as in getMatrixFieldInfo
                        try {
                            if (isset(Craft::$app->matrix)) {
                                $nestedBlockTypes = Craft::$app->matrix->getBlockTypesByFieldId($field->id);
                            }
                            
                            // If that didn't work or returned empty, try accessing from the field directly
                            if (empty($nestedBlockTypes) && property_exists($field, 'blockTypes')) {
                                $nestedBlockTypes = $field->blockTypes;
                            }
                            
                            // If still empty, try the getBlockTypes method
                            if (empty($nestedBlockTypes) && method_exists($field, 'getBlockTypes')) {
                                $nestedBlockTypes = $field->getBlockTypes();
                            }
                        } catch (\Exception $e) {
                            $nestedBlockTypes = [];
                        }
                        
                        $typeIds = [];
                        foreach ($nestedBlockTypes as $nestedBlockType) {
                            $typeIds[] = $nestedBlockType->handle;
                        }
                        $childFieldInfo['typeIds'] = $typeIds;
                    }

                    $blockTypeInfo['childFields'][] = $childFieldInfo;
                }
            }

            return $blockTypeInfo;
        } catch (\Exception $e) {
            return [
                'typeHandle' => $blockType->handle ?? 'unknown',
                'typeName' => $blockType->name ?? 'Unknown Type',
                'typeId' => $blockType->id ?? null,
                'childFields' => [],
                'error' => 'Failed to process block type: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get Neo field information including nested block types and fields
     *
     * @param mixed $neoField
     * @return array
     */
    private function getNeoFieldInfo($neoField): array
    {
        $result = [
            'fieldInfo' => ['childFields' => []],
            'blockTypes' => [],
            'debug' => []
        ];
        
        try {
            $result['debug'][] = 'Neo field ID: ' . ($neoField->id ?? 'unknown');
            $result['debug'][] = 'Neo field handle: ' . ($neoField->handle ?? 'unknown');
            $result['debug'][] = 'Field class: ' . get_class($neoField);
            
            // Get block types from Neo field
            $blockTypes = [];
            
            if (method_exists($neoField, 'getBlockTypes')) {
                $blockTypes = $neoField->getBlockTypes();
                $result['debug'][] = 'getBlockTypes() returned ' . count($blockTypes) . ' block types';
            } elseif (property_exists($neoField, 'blockTypes')) {
                $blockTypes = $neoField->blockTypes ?? [];
                $result['debug'][] = 'blockTypes property returned ' . count($blockTypes) . ' block types';
            }
            
            $result['debug'][] = 'Final block types count: ' . count($blockTypes);
            
            if (!empty($blockTypes)) {
                foreach ($blockTypes as $blockType) {
                    // Generate GraphQL type name
                    $gqlTypeName = ($neoField->handle ?? 'unknown') . '_' . ($blockType->handle ?? 'unknown') . '_BlockType';
                    
                    // Add to fieldInfo.childFields
                    $result['fieldInfo']['childFields'][] = [
                        'fieldType' => 'blockType',
                        'fieldName' => $blockType->handle ?? 'unknown',
                        'displayName' => $blockType->name ?? 'Unknown',
                        'typeIds' => [$blockType->handle ?? 'unknown'],
                        'gqlTypeName' => $gqlTypeName
                    ];
                    
                    // Process the block type to get its fields
                    $blockTypeInfo = $this->processNeoBlockType($blockType, $neoField);
                    if ($blockTypeInfo) {
                        $result['blockTypes'][] = $blockTypeInfo;
                    }
                }
            } else {
                $result['debug'][] = 'No block types found for this Neo field';
                $result['info'] = 'Neo field has no block types configured.';
            }
            
        } catch (\Exception $e) {
            $result['error'] = 'Error: ' . $e->getMessage();
            $result['debug'][] = 'Exception occurred: ' . $e->getMessage();
            $result['debug'][] = 'Stack trace: ' . $e->getTraceAsString();
        }
        
        return $result;
    }

    /**
     * Process a Neo block type and return its field information
     *
     * @param mixed $blockType
     * @param mixed $neoField The parent Neo field (optional, for GraphQL type name generation)
     * @return array|null
     */
    private function processNeoBlockType($blockType, $neoField = null): ?array
    {
        try {
            $blockTypeInfo = [
                'typeHandle' => $blockType->handle ?? 'unknown',
                'typeName' => $blockType->name ?? 'Unknown',
                'typeId' => $blockType->id ?? null,
                'childFields' => [],
                'metadata' => []
            ];
            
            // Add GraphQL type name if we have the field context
            if ($neoField && isset($neoField->handle, $blockType->handle)) {
                $blockTypeInfo['gqlTypeName'] = $neoField->handle . '_' . $blockType->handle . '_BlockType';
            }

            // Add Neo-specific metadata
            if (property_exists($blockType, 'enabled')) {
                $blockTypeInfo['metadata']['enabled'] = $blockType->enabled;
            }
            if (property_exists($blockType, 'description')) {
                $blockTypeInfo['metadata']['description'] = $blockType->description;
            }
            
            // Child block configuration - can be true (all blocks), array of handles (specific blocks), or false (no children)
            if (property_exists($blockType, 'childBlocks')) {
                // Decode JSON string if necessary
                $childBlocks = $blockType->childBlocks;
                if (is_string($childBlocks) && ($childBlocks === 'true' || $childBlocks === '*')) {
                    $childBlocks = true;
                } elseif (is_string($childBlocks)) {
                    try {
                        $decoded = json_decode($childBlocks, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $childBlocks = $decoded;
                        }
                    } catch (\Exception $e) {
                        // Keep as is if decode fails
                    }
                }
                $blockTypeInfo['metadata']['childBlocks'] = $childBlocks;
            }
            
            if (property_exists($blockType, 'topLevel')) {
                $blockTypeInfo['metadata']['topLevel'] = $blockType->topLevel;
            }
            if (property_exists($blockType, 'groupChildBlockTypes')) {
                $blockTypeInfo['metadata']['groupChildBlockTypes'] = $blockType->groupChildBlockTypes;
            }
            
            // Block count constraints
            if (property_exists($blockType, 'minBlocks')) {
                $blockTypeInfo['metadata']['minBlocks'] = $blockType->minBlocks;
            }
            if (property_exists($blockType, 'maxBlocks')) {
                $blockTypeInfo['metadata']['maxBlocks'] = $blockType->maxBlocks;
            }
            if (property_exists($blockType, 'minChildBlocks')) {
                $blockTypeInfo['metadata']['minChildBlocks'] = $blockType->minChildBlocks;
            }
            if (property_exists($blockType, 'maxChildBlocks')) {
                $blockTypeInfo['metadata']['maxChildBlocks'] = $blockType->maxChildBlocks;
            }
            if (property_exists($blockType, 'minSiblingBlocks')) {
                $blockTypeInfo['metadata']['minSiblingBlocks'] = $blockType->minSiblingBlocks;
            }
            if (property_exists($blockType, 'maxSiblingBlocks')) {
                $blockTypeInfo['metadata']['maxSiblingBlocks'] = $blockType->maxSiblingBlocks;
            }

            // Get the field layout for this block type
            $fieldLayout = null;
            if (method_exists($blockType, 'getFieldLayout')) {
                $fieldLayout = $blockType->getFieldLayout();
            }
            
            if ($fieldLayout) {
                $customFields = $fieldLayout->getCustomFields();
                
                foreach ($customFields as $field) {
                    $childFieldInfo = [
                        'fieldType' => $this->getFieldTypeString($field),
                        'fieldName' => $field->handle ?? 'unknown',
                        'displayName' => $field->name ?? 'Unknown',
                        'isLocalizable' => $this->getFieldLocalizationStatus($field)
                    ];

                    // Check if this nested field is also a Neo field or Matrix field
                    if ($this->isNeoField($field)) {
                        // Get nested Neo block types
                        $nestedBlockTypes = [];
                        try {
                            if (method_exists($field, 'getBlockTypes')) {
                                $nestedBlockTypes = $field->getBlockTypes();
                            }
                            
                            $typeIds = [];
                            foreach ($nestedBlockTypes as $nestedBlockType) {
                                $typeIds[] = $nestedBlockType->handle ?? 'unknown';
                            }
                            $childFieldInfo['typeIds'] = $typeIds;
                        } catch (\Exception $e) {
                            // Silently handle nested field type extraction errors
                        }
                    } elseif ($this->isMatrixField($field)) {
                        // Get nested Matrix block/entry types
                        $nestedBlockTypes = [];
                        try {
                            if (method_exists($field, 'getEntryTypes')) {
                                $nestedBlockTypes = $field->getEntryTypes();
                            } elseif (method_exists($field, 'getBlockTypes')) {
                                $nestedBlockTypes = $field->getBlockTypes();
                            }
                            
                            $typeIds = [];
                            foreach ($nestedBlockTypes as $nestedBlockType) {
                                $typeIds[] = $nestedBlockType->handle ?? 'unknown';
                            }
                            $childFieldInfo['typeIds'] = $typeIds;
                        } catch (\Exception $e) {
                            // Silently handle nested field type extraction errors
                        }
                    }

                    $blockTypeInfo['childFields'][] = $childFieldInfo;
                }
            }

            return $blockTypeInfo;
        } catch (\Exception $e) {
            return [
                'typeHandle' => $blockType->handle ?? 'unknown',
                'typeName' => $blockType->name ?? 'Unknown Type',
                'typeId' => $blockType->id ?? null,
                'childFields' => [],
                'error' => 'Failed to process Neo block type: ' . $e->getMessage()
            ];
        }
    }
} 