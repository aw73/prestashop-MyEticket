<?php
/**
* 2007-2015 PrestaShop
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
*  @author    Sébastien Rufer <sebastien@rufer.fr>
*  @copyright 2016 Sébastien Rufer
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class Myetickets extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        if (!defined('_PS_VERSION_')) {
            exit;
        }

        include_once dirname(__FILE__).'/classes/Eticket.php';

        $this->name = 'myetickets';
        $this->tab = 'administration';
        $this->version = '1.3.0';
        $this->author = 'Besens';
        $this->need_instance = 1;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('My E-tickets');
        $this->description = $this->l('Transform a product into a e-ticket to print. Shop admin can check the e-ticket validity and the customer can print his e-ticket.');

        $this->confirmUninstall = $this->l('Are you sur you want to uninstall the e-ticket module ?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        // Install the new Tab in Order menu
        $tab = new Tab();

        foreach (Language::getLanguages() as $language) {
            $tab->name[$language['id_lang']] = 'My E-tickets';
        }

        $tab->class_name = 'AdminMyetickets';
        $tab->module = $this->name;

        $idParent = (int)Tab::getIdFromClassName('AdminParentOrders');
        $tab->id_parent = $idParent;
        $tab->position = Tab::getNbTabs($idParent);

        if (!$tab->save()) {
            return false;
        }

        Configuration::updateValue('MYMODULE_ADMIN_TAB', $tab->id);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionOrderStatusUpdate') &&
            $this->registerHook('actionProductSave') &&
            $this->registerHook('displayAdminOrderContentOrder') &&
            $this->registerHook('displayAdminOrderTabOrder') &&
            $this->registerHook('displayAdminProductsExtra') &&
            $this->registerHook('displayCustomerAccount');
    }

    public function uninstall()
    {
        Configuration::deleteByName('MYETICKETS_CGV');
        Configuration::deleteByName('MYETICKETS_PERIOD');

        $adminTabId = Configuration::get('MYMODULE_ADMIN_TAB');

        if (Tab::existsInDatabase($adminTabId, Tab::$definition['table'])) {
            $adminTab = new Tab($adminTabId);
            if (!$adminTab->delete()) {
                return false;
            }
        }

        include(dirname(__FILE__).'/sql/uninstall.php');

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
        if (((bool)Tools::isSubmit('submitMyeticketsModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMyeticketsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                  array(
                    'type' => 'textarea',
                    'label' => $this->l('CGV'),
                    'name' => 'MYETICKETS_CGV',
                    'desc' => $this->l('Set the CGV that will be print to e-tckets.'),
                  ),
                  array(
                    'type' => 'text',
                    'label' => $this->l('Number of days to use e-tickets'),
                    'name' => 'MYETICKETS_PERIOD',
                    'class' => 'fixed-width-xs',
                    'desc' => $this->l('Set the number of days to use and check e-tckets.'),
                  ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'MYETICKETS_CGV' => Configuration::get('MYETICKETS_CGV', 'contact@prestashop.com'),
            'MYETICKETS_PERIOD' => Configuration::get('MYETICKETS_PERIOD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
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
     * [hookActionOrderStatusUpdate Eval order status to add or remove product e-ticket ]
     * @param  [array] $params Order context data
     * @return nothing
     */
    public function hookActionOrderStatusUpdate($params)
    {
        $products = $params['cart']->getProducts();
        $_add_count = 0;
        $_delete_count = 0;
        foreach ($products as $product) {
            if ($params['newOrderStatus']->paid && Eticket::isEticketProduct($product['id_product'])) {
                // Add new ticket
                $Eticket = new Eticket();
                $Eticket->ean13 = Eticket::calculateEan13($params['id_order'], $product['id_product'], $product['id_product_attribute']);
                $Eticket->id_product = $product['id_product'];
                $Eticket->id_order = $params['id_order'];
                if (isset($product['id_product_attribute'])) {
                    $Eticket->id_product_attribute = $product['id_product_attribute'];
                }
                $Eticket->id_customer = $params['cart']->id_customer;
                $Eticket->checked = false;
                $Eticket->quantity = $product['cart_quantity'];
                $Eticket->product_name = $product['name'];
                if (isset($product['description_short'])) {
                    $Eticket->product_description = $product['description_short'];
                }
                if (isset($product['attributes'])) {
                    $Eticket->product_attributes = $product['attributes'];
                }
                $Eticket->total_wt = $product['total_wt'];
                $Eticket->date_paid = $product['date_upd'];
                $Eticket->validate();
                $_add_count++;
            } else {
                // Delete existing tickets
                $Eticket = new Eticket(Eticket::getIdFromOrderProductProductAttributes($params['id_order'], $product['id_product'], $product['id_product_attribute']));
                $Eticket->delete();
                $_delete_count++;
            }
        }
        return $this->displayConfirmation('E-ticket : '.$_delete_count.' removed and '.$_add_count.' created !');
    }

    /**
     * [hookDisplayAdminOrderContentOrder description]
     * @param  [array] $params Order context data
     * @return [boolean]         redirect to template view
     */
    public function hookDisplayAdminOrderContentOrder($params)
    {
        $_etickets = Eticket::getEticketsFromIds(Eticket::getIdsFromOrder($params['order']->id));
        if ($_etickets) {
            $this->context->smarty->assign(array(
                'etickets' => $_etickets
            ));
            return $this->display(__FILE__, 'adminOrderContentOrder.tpl');
        }
    }

  /**
   * [hookDisplayAdminOrderTabOrder Add tab in order status view]
   * @return [boolean] rediret to template view
   */
    public function hookDisplayAdminOrderTabOrder($params)
    {
        if (Eticket::getEticketsFromIds(Eticket::getIdsFromOrder($params['order']->id))) {
            return $this->display(__FILE__, 'adminOrderTabOrder.tpl');
        }
    }

    /**
     * [hookDisplayAdminProductsExtra Add a form in the Product form]
     * @return [boolean] redirect to template view
     */
    public function hookDisplayAdminProductsExtra()
    {
        $this->context->smarty->assign(
            array(
                'my_module_name' => Configuration::get('MYMODULE_NAME'),
                'my_module_link' => $this->context->link->getModuleLink('myetickets', 'display'),
                'is_eticket' => Eticket::isEticketProduct((int)Tools::getValue('id_product')),
                'languages' => $this->context->controller->_languages,
                'default_form_language' => (int)Configuration::get('PS_LANG_DEFAULT')
            )
        );
        return $this->display(__FILE__, 'adminProductExtra.tpl');
    }

    /**
     * [hookActionProductSave Save the is_eticket field in the product table]
     * @param  [array] $params Product context data
     * @return [nothing]
     */
    public function hookActionProductSave($params)
    {
        $id_product = $params['id_product'];
        if (!Db::getInstance()->update('product', array('is_eticket'=> pSQL(Tools::getValue('is_eticket'))), 'id_product = ' .$id_product)) {
            $this->context->controller->_errors[] = Tools::displayError('Error: ').mysql_error();
        }
    }

    public function hookDisplayCustomerAccount($params)
    {
        $this->context->smarty->assign(
            array(
                'id_customer' => $this->context->customer->id,
                'default_form_language' => (int)Configuration::get('PS_LANG_DEFAULT')
            )
        );
        return $this->display(__FILE__, 'displayCustomerAccount.tpl');
    }
}
