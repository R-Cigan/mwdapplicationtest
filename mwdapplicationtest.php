<?php
/**
* 2007-2021 PrestaShop
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
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Mwdapplicationtest extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'mwdapplicationtest';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'medani digital';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('medani - application test module');
        $this->description = $this->l('This module should be extended by you.');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
			$this->registerHook('actionOrderStatusPostUpdate');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitMwdapplicationtestModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output;
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }
	
	/**
	 * Perform custom action after the order status has been updated - but before it is changed in the DB
	 */
	public function hookActionOrderStatusPostUpdate($params) {
		// check if the new order status id matches with the enviroment constant for "sent" status
		if($params['newOrderStatus']->id === intval(Configuration::get('PS_OS_SHIPPING'))) {
		// if($params['newOrderStatus']->id === 4) {
			// get order reference from the $params variable
			$order_reference = $params['id_order'];
			
			// generate and save log message with severity level 1
			$message_sent = "Die Bestellung ".$order_reference." wurde versendet";
			PrestaShopLogger::addLog($message_sent, $severity = 1, $error_code = null, $object_type = null, $object_id = null, $allow_duplicate = false, $id_employee = null);
			
			// fetch the "Order" object with the corresponding id
			$order = new Order((int)$params['id_order']);
			// get all products for the current order
			// getProducts($products = false, $selected_products = false, $selected_qty = false, $fullInfos = true)
			// selected_products, selected_qty need to be set to "true" and fullInfos to "false" so we can focus on the quantity
			$products = $order->getProducts(false, true, true, false);
			// other option to get the quantity of products in a order - we could also qeuery the DB directly for the specific order
			// $products = OrderDetail::getList((int)$params['id_order']);
			
			// simple sum of product quantity
			$total_product_quantity = 0;
			foreach ($products as $product) {
				$total_product_quantity += $product['product_quantity'];
			}
			
			if ($total_product_quantity > 3) {
				// generate and save log message with severity level 2
				$message_quantity = "Die Bestellung ".$order_reference." beinhaltet mehr als 3 Artikel";
				PrestaShopLogger::addLog($message_quantity, $severity = 2, $error_code = null, $object_type = null, $object_id = null, $allow_duplicate = false, $id_employee = null);
			}
		}
	}
}
