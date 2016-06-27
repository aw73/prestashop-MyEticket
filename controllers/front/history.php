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

class myeticketsHistoryModuleFrontController extends ModuleFrontController
{
    public $auth = true;
    public $authRedirection = 'my-acount';
    public $ssl = true;

    public function init()
    {
        $this->page_name = 'history'; // page_name and body id
      $this->display_column_left = false;
        $this->display_column_right = false;

        if (Tools::getIsset('submitMyeticketsPdf')) {
            $this->processPdf();
        }
        parent::init();
    }

    public function initContent()
    {
        parent::initContent();
        $myEtickets = array();

        if ($etickets = Eticket::getIdsFromCustomer($this->context->customer->id)) {
            foreach ($etickets as &$eticket) {
                $myEtickets[] = new Eticket((int)$eticket['id_myetickets']);
            }
        }
        $this->context->smarty->assign(array(
          'etickets' => $myEtickets,
      ));

        $this->setTemplate('history.tpl');
    }

    public function processPdf()
    {
        require_once _PS_MODULE_DIR_ . 'myetickets/HTMLTemplateMyeticketsPdf.php';
        require_once _PS_MODULE_DIR_ . 'myetickets/classes/Eticket.php';

        if ($idMyeticket = (int)Tools::getValue('id_myetickets')) {
            $Eticket = new Eticket($idMyeticket);
      // Protect free download
      // Control that current connected user is customer to this e-ticket
      if ($this->context->customer->id == $Eticket->id_customer) {
          $pdf = new PDF($Eticket, 'MyeticketsPdf', Context::getContext()->smarty);
          $pdf->render();
      } else {
          $this->errors[] = $this->module->l("You cannot download this e-ticket: it is not yours !");
      }
        }
    }
}
