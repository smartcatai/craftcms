<?php

namespace smartcat\smartcatintegration\controllers;

use Craft;
use craft\web\Controller;
use craft\web\Response;
use craft\elements\Entry;
use craft\elements\Category;
use craft\elements\Asset;
use craft\elements\User;
use craft\elements\GlobalSet;
use craft\fields\BaseField;
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
    protected array|bool|int $allowAnonymous = ['fields'];

    /**
     * Returns field information for the specified entity type
     *
     * @return Response
     * @throws BadRequestHttpException
     */
    public function actionFields(): Response
    {
        $request = Craft::$app->getRequest();
        $entityType = $request->getQueryParam('type');

        if (!$entityType) {
            throw new BadRequestHttpException('The "type" parameter is required.');
        }

        $fields = $this->getFieldsForEntityType($entityType);

        return $this->asJson($fields);
    }

    /**
     * Get fields for the specified entity type
     *
     * @param string $entityType
     * @return array
     * @throws BadRequestHttpException
     */
    private function getFieldsForEntityType(string $entityType): array
    {
        $fieldsService = Craft::$app->getFields();
        $fields = [];

        switch (strtolower($entityType)) {
            case 'entry':
            case 'entries':
                $fields = $this->getEntryFields();
                break;
            
            case 'category':
            case 'categories':
                $fields = $this->getCategoryFields();
                break;
            
            case 'asset':
            case 'assets':
                $fields = $this->getAssetFields();
                break;
            
            case 'user':
            case 'users':
                $fields = $this->getUserFields();
                break;
            
            case 'globalset':
            case 'globals':
                $fields = $this->getGlobalSetFields();
                break;
            
            default:
                throw new BadRequestHttpException("Unsupported entity type: {$entityType}");
        }

        return $fields;
    }

    /**
     * Get fields for entries
     *
     * @return array
     */
    private function getEntryFields(): array
    {
        $fields = [];
        $sectionsService = Craft::$app->getSections();
        $fieldsService = Craft::$app->getFields();

        // Get all sections
        $sections = $sectionsService->getAllSections();

        foreach ($sections as $section) {
            // Get entry types for this section
            $entryTypes = $section->getEntryTypes();
            
            foreach ($entryTypes as $entryType) {
                $fieldLayout = $entryType->getFieldLayout();
                
                if ($fieldLayout) {
                    $customFields = $fieldLayout->getCustomFields();
                    
                    foreach ($customFields as $field) {
                        $fieldInfo = $this->formatFieldInfo($field);
                        $fieldInfo['section'] = $section->name;
                        $fieldInfo['entryType'] = $entryType->name;
                        $fields[] = $fieldInfo;
                    }
                }
            }
        }

        // Add default entry fields
        $fields = array_merge($fields, $this->getDefaultEntryFields());

        return $fields;
    }

    /**
     * Get fields for categories
     *
     * @return array
     */
    private function getCategoryFields(): array
    {
        $fields = [];
        $categoriesService = Craft::$app->getCategories();

        $categoryGroups = $categoriesService->getAllGroups();

        foreach ($categoryGroups as $group) {
            $fieldLayout = $group->getFieldLayout();
            
            if ($fieldLayout) {
                $customFields = $fieldLayout->getCustomFields();
                
                foreach ($customFields as $field) {
                    $fieldInfo = $this->formatFieldInfo($field);
                    $fieldInfo['categoryGroup'] = $group->name;
                    $fields[] = $fieldInfo;
                }
            }
        }

        // Add default category fields
        $fields = array_merge($fields, $this->getDefaultCategoryFields());

        return $fields;
    }

    /**
     * Get fields for assets
     *
     * @return array
     */
    private function getAssetFields(): array
    {
        $fields = [];
        $volumesService = Craft::$app->getVolumes();

        $volumes = $volumesService->getAllVolumes();

        foreach ($volumes as $volume) {
            $fieldLayout = $volume->getFieldLayout();
            
            if ($fieldLayout) {
                $customFields = $fieldLayout->getCustomFields();
                
                foreach ($customFields as $field) {
                    $fieldInfo = $this->formatFieldInfo($field);
                    $fieldInfo['volume'] = $volume->name;
                    $fields[] = $fieldInfo;
                }
            }
        }

        // Add default asset fields
        $fields = array_merge($fields, $this->getDefaultAssetFields());

        return $fields;
    }

    /**
     * Get fields for users
     *
     * @return array
     */
    private function getUserFields(): array
    {
        $fields = [];
        $usersService = Craft::$app->getUsers();
        $fieldLayout = $usersService->getLayout();

        if ($fieldLayout) {
            $customFields = $fieldLayout->getCustomFields();
            
            foreach ($customFields as $field) {
                $fields[] = $this->formatFieldInfo($field);
            }
        }

        // Add default user fields
        $fields = array_merge($fields, $this->getDefaultUserFields());

        return $fields;
    }

    /**
     * Get fields for global sets
     *
     * @return array
     */
    private function getGlobalSetFields(): array
    {
        $fields = [];
        $globalsService = Craft::$app->getGlobals();

        $globalSets = $globalsService->getAllSets();

        foreach ($globalSets as $globalSet) {
            $fieldLayout = $globalSet->getFieldLayout();
            
            if ($fieldLayout) {
                $customFields = $fieldLayout->getCustomFields();
                
                foreach ($customFields as $field) {
                    $fieldInfo = $this->formatFieldInfo($field);
                    $fieldInfo['globalSet'] = $globalSet->name;
                    $fields[] = $fieldInfo;
                }
            }
        }

        return $fields;
    }

    /**
     * Format field information
     *
     * @param BaseField $field
     * @return array
     */
    private function formatFieldInfo(BaseField $field): array
    {
        return [
            'fieldName' => $field->handle,
            'displayName' => $field->name,
            'isLocalizable' => (bool) $field->translationMethod !== 'none',
            'type' => $this->getFieldTypeString($field)
        ];
    }

    /**
     * Get field type as string
     *
     * @param BaseField $field
     * @return string
     */
    private function getFieldTypeString(BaseField $field): string
    {
        $className = get_class($field);
        $parts = explode('\\', $className);
        $fieldType = end($parts);
        
        // Convert common field types to more readable names
        $typeMap = [
            'PlainText' => 'string',
            'Textarea' => 'text',
            'RichText' => 'richtext',
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
    }

    /**
     * Get default entry fields
     *
     * @return array
     */
    private function getDefaultEntryFields(): array
    {
        return [
            [
                'fieldName' => 'title',
                'displayName' => 'Title',
                'isLocalizable' => true,
                'type' => 'string'
            ],
            [
                'fieldName' => 'slug',
                'displayName' => 'Slug',
                'isLocalizable' => true,
                'type' => 'string'
            ],
            [
                'fieldName' => 'postDate',
                'displayName' => 'Post Date',
                'isLocalizable' => false,
                'type' => 'date'
            ],
            [
                'fieldName' => 'expiryDate',
                'displayName' => 'Expiry Date',
                'isLocalizable' => false,
                'type' => 'date'
            ]
        ];
    }

    /**
     * Get default category fields
     *
     * @return array
     */
    private function getDefaultCategoryFields(): array
    {
        return [
            [
                'fieldName' => 'title',
                'displayName' => 'Title',
                'isLocalizable' => true,
                'type' => 'string'
            ],
            [
                'fieldName' => 'slug',
                'displayName' => 'Slug',
                'isLocalizable' => true,
                'type' => 'string'
            ]
        ];
    }

    /**
     * Get default asset fields
     *
     * @return array
     */
    private function getDefaultAssetFields(): array
    {
        return [
            [
                'fieldName' => 'title',
                'displayName' => 'Title',
                'isLocalizable' => true,
                'type' => 'string'
            ],
            [
                'fieldName' => 'filename',
                'displayName' => 'Filename',
                'isLocalizable' => false,
                'type' => 'string'
            ],
            [
                'fieldName' => 'kind',
                'displayName' => 'File Kind',
                'isLocalizable' => false,
                'type' => 'string'
            ],
            [
                'fieldName' => 'size',
                'displayName' => 'File Size',
                'isLocalizable' => false,
                'type' => 'number'
            ],
            [
                'fieldName' => 'width',
                'displayName' => 'Width',
                'isLocalizable' => false,
                'type' => 'number'
            ],
            [
                'fieldName' => 'height',
                'displayName' => 'Height',
                'isLocalizable' => false,
                'type' => 'number'
            ]
        ];
    }

    /**
     * Get default user fields
     *
     * @return array
     */
    private function getDefaultUserFields(): array
    {
        return [
            [
                'fieldName' => 'username',
                'displayName' => 'Username',
                'isLocalizable' => false,
                'type' => 'string'
            ],
            [
                'fieldName' => 'firstName',
                'displayName' => 'First Name',
                'isLocalizable' => false,
                'type' => 'string'
            ],
            [
                'fieldName' => 'lastName',
                'displayName' => 'Last Name',
                'isLocalizable' => false,
                'type' => 'string'
            ],
            [
                'fieldName' => 'fullName',
                'displayName' => 'Full Name',
                'isLocalizable' => false,
                'type' => 'string'
            ],
            [
                'fieldName' => 'email',
                'displayName' => 'Email',
                'isLocalizable' => false,
                'type' => 'email'
            ]
        ];
    }
} 