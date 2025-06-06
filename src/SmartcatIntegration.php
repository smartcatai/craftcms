<?php

namespace smartcat\smartcatintegration;

use Craft;
use craft\base\Plugin;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use yii\base\Event;

/**
 * Smartcat Integration plugin
 *
 * @method static SmartcatIntegration getInstance()
 * @author Smartcat <support@smartcat.com>
 * @copyright Smartcat
 * @license MIT
 */
class SmartcatIntegration extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = false;
    public bool $hasCpSection = false;

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });
    }

    private function attachEventHandlers(): void
    {
        // Register API routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['api/smartcat/fields'] = 'smartcat-integration/api/fields';
                $event->rules['api/smartcat/sites'] = 'smartcat-integration/api/sites';
                $event->rules['api/smartcat/sections'] = 'smartcat-integration/api/sections';
                $event->rules['api/smartcat/types'] = 'smartcat-integration/api/types';
                $event->rules['api/smartcat/users'] = 'smartcat-integration/api/users';
            }
        );
    }
} 