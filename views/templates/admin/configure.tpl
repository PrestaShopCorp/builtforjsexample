{*
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
*}

<prestashop-accounts></prestashop-accounts>
<div id="prestashop-cloudsync"></div>

<div id="ps-billing"></div>
<div id="ps-modal"></div>

<br>

<div id="module-config">

	<div class="panel">
		<h3><i class="icon icon-credit-card"></i> {l s='Example with PrestaShop Integration Framework' mod='fullsaasappjs'}</h3>
		<p>
			<strong>{l s='Here is a module presenting the PrestaShop Integration Framework!' mod='fullsaasappjs'}</strong><br />
			{l s='Please associate your shop with PrestaShop Accounts.' mod='fullsaasappjs'}<br />
			{l s='Then agree to share your data with PrestaShop CloudSync' mod='fullsaasappjs'}<br />
			{l s='Finally, choose your subscription plan in PrestaShop Billing' mod='fullsaasappjs'}
		</p>
		<br />
		<p>
			{l s='This module will boost your sales!' mod='fullsaasappjs'}
		</p>
	</div>
</div>

<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
<script src="{$urlCloudsync|escape:'htmlall':'UTF-8'}"></script>
<script src="{$urlBilling|escape:'htmlall':'UTF-8'}"></script>

<script>
    window?.psaccountsVue?.init();

    if(window.psaccountsVue.isOnboardingCompleted() != true)
    {
    	document.getElementById("module-config").style.opacity = "0.5";
    }

	// Cloud Sync
	const cdc = window.cloudSyncSharingConsent;

	cdc.init('#prestashop-cloudsync');
	cdc.on('OnboardingCompleted', (isCompleted) => {
		console.log('OnboardingCompleted', isCompleted);
		
	});
	cdc.isOnboardingCompleted((isCompleted) => {
		console.log('Onboarding is already Completed', isCompleted);
	});


	window.psBilling.initialize(window.psBillingContext.context, '#ps-billing', '#ps-modal', (type, data) => {
		// Event hook listener
		switch (type) {
		  case window.psBilling.EVENT_HOOK_TYPE.BILLING_INITIALIZED:
		    console.log('Billing initialized', data);
		    break;
		  case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_UPDATED:
		    console.log('Sub updated', data);
		    break;
		  case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CANCELLED:
		    console.log('Sub cancelled', data);
		    break;
		}
	});
</script>
