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
    protected array|bool|int $allowAnonymous = ['fields', 'locales'];

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
     * Returns a list of available locales/sites
     *
     * @return Response
     */
    public function actionLocales(): Response
    {
        $locales = $this->getAvailableLocales();
        return $this->asJson($locales);
    }

    /**
     * Get available locales/sites
     *
     * @return array
     */
    private function getAvailableLocales(): array
    {
        $locales = [];
        $sites = Craft::$app->getSites()->getAllSites();
        
        foreach ($sites as $site) {
            $locales[] = [
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
        
        return $locales;
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
     * Get field type as string
     *
     * @param mixed $field
     * @return string
     */
    private function getFieldTypeString($field): string
    {
        try {
            $className = get_class($field);
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
} 