<?php

namespace PrestaShop\Module\Builtforjsexample\Services;

use Module;

class PrestashopModuleTracking
{
    /**
     * Send tracking event to Segment.
     *
     * @param string $apiKey
     * @param Module $module
     * @param string $eventName
     * @param array $properties
     *
     * @return void
     */
    public static function track($apiKey, Module $module, $eventName, array $properties = [])
    {
        if (empty($apiKey)) {
            self::log($module, sprintf('Skip "%s" because API key is empty.', $eventName));
            return;
        }

        $baseProperties = [
            'shop_url' => defined('_PS_BASE_URL_SSL_') ? _PS_BASE_URL_SSL_ : (defined('_PS_BASE_URL_') ? _PS_BASE_URL_ : ''),
            'ps_version' => defined('_PS_VERSION_') ? _PS_VERSION_ : '',
            'php_version' => PHP_VERSION,
            'module_version' => property_exists($module, 'version') ? $module->version : '',
        ];

        if(!empty($properties)){
            $baseProperties['custom'] = $properties;
        }

        self::log($module, sprintf('Preparing "%s" tracking.', $eventName), $baseProperties);

        try {
            if (method_exists($module, 'getService')) {
                $serviceName = sprintf('%s.ps_accounts_facade', $module->name);
                $accountsFacade = $module->getService($serviceName);
                if ($accountsFacade && method_exists($accountsFacade, 'getPsAccountsService')) {
                    $psAccountsService = $accountsFacade->getPsAccountsService();
                    $baseProperties = array_merge($baseProperties, [
                        'user_id' => $psAccountsService->getUserUuid(),
                        'email' => $psAccountsService->getEmail(),
                        'shop_id' => $psAccountsService->getShopUuid(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            if (class_exists('PrestaShopLogger')) {
                \PrestaShopLogger::addLog($e->getMessage(), 3, null, $module->name);
            }
        }

        try {
            if (class_exists('\\Segment')) {
                \Segment::init($apiKey);
                \Segment::track([
                    'anonymousId' => $module->name,
                    'event' => $eventName,
                    'properties' => $baseProperties,
                ]);
            } elseif (class_exists('\\Segment\\Segment')) {
                \Segment\Segment::init($apiKey);
                \Segment\Segment::track([
                    'anonymousId' => $module->name,
                    'event' => $eventName,
                    'properties' => $baseProperties,
                ]);
            } else {
                self::log($module, 'Segment library not available, aborting track call.');
                return;
            }

            self::log($module, sprintf('Track "%s" sent.', $eventName));
        } catch (\Throwable $e) {
            if (class_exists('PrestaShopLogger')) {
                \PrestaShopLogger::addLog($e->getMessage(), 3, null, $module->name);
            }
        }
    }

    protected static function log(Module $module, $message, array $context = [])
    {
        if (!class_exists('PrestaShopLogger')) {
            return;
        }

        if (!defined('_PS_MODE_DEV_') || !_PS_MODE_DEV_) {
            return;
        }

        \PrestaShopLogger::addLog(
            sprintf('[Tracking][%s] %s | Context: %s', $module->name, $message, json_encode($context)),
            1,
            null,
            $module->name
        );
    }
}
