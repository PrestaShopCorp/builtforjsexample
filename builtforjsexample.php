<?php
/**
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use Prestashop\ModuleLibMboInstaller\DependencyBuilder;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;
use PrestaShop\Module\Builtforjsexample\Services\PrestashopModuleTracking;

if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class BuiltForJsExample extends Module
{
    protected $config_form = false;

    private $container;
    private $segmentWriteKey = '';

    public function __construct()
    {
        $this->name = 'builtforjsexample';
        $this->tab = 'administration';
        $this->version = '3.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Example module with all PrestaShop Integration Framework');
        $this->description = $this->l('Module using PrestaShop Account, PrestaShop Billing and PrestaShop CloudSync.');

        $this->ps_versions_compliancy = array('min' => '1.7.5', 'max' => _PS_VERSION_);
        $this->php_versions_compliancy = array('min' => '7.2');

        if ($this->container === null) {
            $this->container = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $this->trackModuleEvent('Module Installed');

        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }

        $this->trackModuleEvent('Module Uninstalled');

        return true;
    }

    public function enable($force_all = false)
    {
        if(!parent::enable($force_all)){
            return false;
        }

        $this->trackModuleEvent('Module Enabled');

        return true;
    }

    public function disable($force_all = false)
    {
        if (!parent::disable($force_all)) {
            return false;
        }
        
        $this->trackModuleEvent('Module Disabled');

        return true;
    }

    public function runUpgradeModule()
    {
        if (!parent::runUpgradeModule()) {
            return false;
        }

        $this->segmentTrack('Module Upgraded');

        return true;
    }

    /**
     * Load the configuration content
     */
    public function getContent()
    {

        # Load dependencies manager
        $mboInstaller = new \Prestashop\ModuleLibMboInstaller\DependencyBuilder($this);

        if( !$mboInstaller->areDependenciesMet() )
        {
            $dependencies = $mboInstaller->handleDependencies();

            $this->smarty->assign('dependencies', $dependencies);

            return $this->display(__FILE__, 'views/templates/admin/dependency_builder.tpl');
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $moduleManager = ModuleManagerBuilder::getInstance()->build();

        $accountsService = null;

        try {
            $accountsFacade = $this->getService('builtforjsexample.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            $accountsInstaller = $this->getService('builtforjsexample.ps_accounts_installer');
            $accountsInstaller->install();
            $accountsFacade = $this->getService('builtforjsexample.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }

        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()
                    ->present($this->name),
            ]);

            // Retrieve Account CDN
            $this->context->smarty->assign('urlAccountsCdn', $accountsService->getAccountsCdn());

        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();
            return '';
        }

        if (\Tools::isSubmit('submit' . $this->name)) {
            // implement parameters if needed
            $params = [];
            $this->trackModuleEvent('Module Configured', $params);
        }

        if ($moduleManager->isInstalled("ps_eventbus")) {
            $eventbusModule =  \Module::getInstanceByName("ps_eventbus");
            if (version_compare($eventbusModule->version, '1.9.0', '>=')) {

                $eventbusPresenterService = $eventbusModule->getService('PrestaShop\Module\PsEventbus\Service\PresenterService');

                $this->context->smarty->assign('urlCloudsync', "https://assets.prestashop3.com/ext/cloudsync-merchant-sync-consent/latest/cloudsync-cdc.js");

                Media::addJsDef([
                    'contextPsEventbus' => $eventbusPresenterService->expose($this, ['info', 'modules', 'themes'])
                ]);
            }
        }

        /**********************
         * PrestaShop Billing *
         * *******************/

        // Load context for PsBilling
        $billingFacade = $this->getService('builtforjsexample.ps_billings_facade');   
        $partnerLogo = $this->getLocalPath() . 'logo.png';

        // Billing
        Media::addJsDef($billingFacade->present([
            'logo' => $partnerLogo,
            'tosLink' => 'https://www.prestashop.com/en/prestashop-account-terms-conditions',
            'privacyLink' => 'https://www.prestashop.com/en/privacy-policy',
            'emailSupport' => 'your@email.tld',
        ]));

        $this->context->smarty->assign('urlBilling', "https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle.js");


        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
        return $output;
    }

    /**
     * Retrieve service
     *
     * @param string $serviceName
     *
     * @return mixed
     */
    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }

    protected function trackModuleEvent($eventName, array $properties = [])
    {
        PrestashopModuleTracking::track(
            $this->segmentWriteKey,
            $this,
            $eventName,
            $properties
        );
    }

}
