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

class HTMLTemplateMyeticketsPdf extends HTMLTemplate
{
    public $Eticket;
    public $php_self = 'pdf';
    protected $display_header = false;
    protected $display_footer = false;

    public $content_only = true;



    public function __construct($custom_object, $smarty)
    {
        $this->Eticket = $custom_object;
        $this->smarty = $smarty;

        // header informations
        $this->title = HTMLTemplateMyeticketsPdf::l('Custom Title');
        // footer informations
        $this->shop = new Shop(Context::getContext()->shop->id);
    }

    /**
     * Returns the template's HTML content
     * @return string HTML content
     */
    public function getContent()
    {
        $barcodeFilePath = Eticket::createBarCode($this->Eticket->ean13, 'EAN13');
        //ddd($barcodeFilePath);
        $this->smarty->assign(array(
            'Eticket' => $this->Eticket,
            'barcodeFilePath' => $barcodeFilePath,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'myetickets/views/pdf/custom_template_content.tpl');
    }

    public function getLogo()
    {
        $this->smarty->assign(array(
            'Eticket' => $this->Eticket,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'myetickets/views/pdf/custom_template_logo.tpl');
    }

    public function getHeader()
    {
        $this->smarty->assign(array(
            'Eticket' => $this->Eticket,
        ));

        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'myetickets/views/pdf/custom_template_header.tpl');
    }

    /**
     * Returns the template filename
     * @return string filename
     */
    public function getFooter()
    {
        return $this->smarty->fetch(_PS_MODULE_DIR_ . 'myetickets/views/pdf/custom_template_footer.tpl');
    }

    /**
     * Returns the template filename
     * @return string filename
     */
    public function getFilename()
    {
        return 'custom_pdf.pdf';
    }

    /**
     * Returns the template filename when using bulk rendering
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'custom_pdf.pdf';
    }

    public function getPagination()
    {
        return 'pagination';
    }
}
