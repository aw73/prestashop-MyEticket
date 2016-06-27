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

class AdminMyeticketsController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;
        $this->table    = 'myetickets';
        $this->className    = 'Eticket';

        $this->context = Context::getContext();

        parent :: __construct();
    }

    public function init()
    {
        if (Tools::getIsset('submitMyeticketsPdf')) {
            $this->processPdf();
        } elseif (Tools::getIsset('submitMyeticketsCheck')) {
            $this->processCheck();
        }
        parent::init();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitMyeticketsSearch')) {
            if ($_searchValue = Tools::getValue('ean13')) {
                $this->context->smarty->assign('etickets', $this->getSearchResults($_searchValue));
            }
        }
        parent::postProcess();
    }

    public function display()
    {
        $_content = $this->renderSearchForm();
        if (Tools::isSubmit('submitMyeticketsSearch')) {
            $_content .= $this->context->smarty->fetch(_PS_MODULE_DIR_ . 'myetickets/views/templates/admin/searchResults.tpl');
        }
        $this->context->smarty->assign('content', $_content);
        parent::display();
    }

    protected function renderSearchForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this->module;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitMyeticketsSearch';
        //$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        //    .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array('ean13' => Tools::getValue('ean13', '')), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        $_fields = array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Search'),
                'icon' => 'icon-search',
                ),
                'input' => array(
                  array(
                    'type' => 'text',
                    'label' => $this->l('EAN13 code'),
                    'name' => 'ean13',
                    'desc' => $this->l('Set the 13 digits EAN code with scan code.'),
                  ),
                ),
                'submit' => array(
                    'title' => $this->l('Search'),
                ),
            ),
        );

        return $helper->generateForm(array($_fields));
    }

    public function getSearchResults($ean13)
    {
        require_once _PS_MODULE_DIR_ . 'myetickets/classes/Eticket.php';

        return Eticket::getEticketsFromIds(Eticket::getIdsFromEan13($ean13));
    }

    public function processPdf()
    {
        require_once _PS_MODULE_DIR_ . 'myetickets/HTMLTemplateMyeticketsPdf.php';
        require_once _PS_MODULE_DIR_ . 'myetickets/classes/Eticket.php';

        if ($idMyeticket = (int)Tools::getValue('id_myetickets')) {
            $Eticket = new Eticket($idMyeticket);
            $pdf = new PDF($Eticket, 'MyeticketsPdf', Context::getContext()->smarty);
            $pdf->render();
        }
    }

    public function processCheck()
    {
        require_once _PS_MODULE_DIR_ . 'myetickets/classes/Eticket.php';
        if ($idMyeticket = (int)Tools::getValue('id_myetickets')) {
            $Eticket = new Eticket($idMyeticket);
            $Eticket->checked = 1;
            $Eticket->check_date = date("Y-m-d");
            $Eticket->update();
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminMyetickets').'&submitMyeticketsSearch&ean13='.$Eticket->ean13);
        }
    }


    /*
    public function init()
    {
      // EXECUTE EN 1
    }

    public function postProcess()
    {
      // EXECUTE EN 2
    }

    public function initContent()
    {
      // EXECUTE EN 3
    }

    public function display()
    {
      // EXECUTE EN 4
    }

    public function initProcess()
    {
      // EXECUTE EN 5
    }
    */
}
