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

class Eticket extends ObjectModel
{
    public $id;
    public $ean13;
    public $id_product;
    public $id_order;
    public $id_product_attribute;
    public $id_customer;
    public $check_date; // = "0000-00-00";
  public $checked;
    public $quantity;
    public $product_name;
    public $product_description;
    public $product_attributes;
    public $total_wt;
    public $date_paid;

  /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'myetickets',
        'primary' => 'id_myetickets',
        'multilang' => false,
        'fields' => array(
            'ean13' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 50),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'check_date' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'checked' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
            'quantity' => array('type' => self::TYPE_INT, 'validate' => 'isInt'),
            'product_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 128),
            'product_description' => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'),
            'product_attributes' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 255),
            'total_wt' => array('type' => self::TYPE_FLOAT, 'shop' => true),
            'date_paid' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public function validate()
    {
        $this->validated = 1;
        $this->save();
        return true;
    }

    public static function getIdFromOrderProductProductAttributes($id_order, $id_product, $id_product_attribute)
    {
        $query = new DbQuery();
        $query->select('id_myetickets');
        $query->from('myetickets', 'ticket');
        $query->where('`id_order` = '.(int)$id_order.' AND `id_product` = '.(int)$id_product.' AND `id_product_attribute` = '.(int)$id_product_attribute);

        return (int)Db::getInstance()->getValue($query);
    }

    public static function getIdsFromOrder($orderId)
    {
        $query = new DbQuery();
        $query->select('id_myetickets');
        $query->from('myetickets', 'ticket');
        $query->where('`id_order` = '.(int)$orderId);

        return Db::getInstance()->executeS($query);
    }

    public static function getIdsFromCustomer($customerId)
    {
        $query = new DbQuery();
        $query->select('id_myetickets');
        $query->from('myetickets', 'ticket');
        $query->where('`id_customer` = '.(int)$customerId);

        return Db::getInstance()->executeS($query);
    }

    public static function getIdsFromEan13($ean13, $strict = false)
    {
        $query = new DbQuery();
        $query->select('id_myetickets');
        $query->from('myetickets', 'ticket');
        if ($strict) {
            $query->where('`ean13` = \''.$ean13.'\'');
        } else {
            $query->where('`ean13` like \'%'.$ean13.'%\'');
        }

        return Db::getInstance()->executeS($query);
    }

    public static function getEticketsFromIds($id_array)
    {
        $list = array();
        if (!empty($id_array) && is_array($id_array)) {
            foreach ($id_array as $value) {
                $list[] = new Eticket($value['id_myetickets']);
            }
        } else {
            $list = false;
        }

        return $list;
    }

    public static function isEticketProduct($id_product)
    {
        $result = Db::getInstance()->ExecuteS('SELECT is_eticket FROM '._DB_PREFIX_.'product WHERE id_product = ' . (int)$id_product);

        if (!empty($result)) {
            $return = $result[0]['is_eticket'];
        } else {
            $return = 0;
        }

        return $return;
    }

    public static function calculateEan13($id_order, $id_product, $id_product_attribute)
    {
        // EAN13 Code must begin with a number between 20 to 29
        // MyFormat rules :
        // ID_ORDER : 5 digits
        // ID_PRODUCT : 4 digits
        // ID_PRODUCT_ATTRIBUTE : 2 digits

        // Formating
        $evalOrder = str_pad("$id_order", 5, '0', STR_PAD_LEFT);
        $evalProduct = str_pad("$id_product", 4, '0', STR_PAD_LEFT);
        $evalProductAttribute = str_pad("$id_product_attribute", 2, '0', STR_PAD_LEFT);

        // Trunc if attributes too long
        if (Tools::strlen($evalProductAttribute) > 2) {
            $evalProductAttribute = Tools::substr($evalProductAttribute, -2);
        }

        // Concat the terminal part of the code
        $ean13Ending = "{$evalOrder}{$evalProduct}{$evalProductAttribute}";

        // Test if this current EAN13 is already used
        $ean13Begining = 20;
        while (Eticket::isEan13Used("{$ean13Begining}{$ean13Ending}")) {
            $ean13Begining++;
        }

        // If begining upper than 29 the return error
        if ($ean13Begining > 29) {
            return false;
        }

        return "{$ean13Begining}{$ean13Ending}";
    }

    public static function isEan13Used($ean13Code)
    {
        $result = Db::getInstance()->ExecuteS('SELECT ean13 FROM '._DB_PREFIX_.'myetickets WHERE ean13 = '.$ean13Code);

        return !empty($result);
    }

    public static function createBarCode($code, $type)
    {
        require_once _PS_MODULE_DIR_ . 'myetickets/lib/Barcode.php';
        $cacheFileName = _PS_CACHE_DIR_."tcpdf/".$type."-".$code.".png";
        if (!file_exists($cacheFileName)) {
        //$fontSize = 10;   // GD1 in px ; GD2 in point
        //$marge    = 10;   // between barcode and hri in pixel
        $x        = 125;  // barcode center
        $y        = 50;  // barcode center
        $height   = 75;   // barcode height in 1D ; module size in 2D
        $width    = 2;    // barcode height in 1D ; not use in 2D
        $angle    = 0;   // rotation in degrees : nb : non horizontable barcode might not be usable because of pixelisation

        $img     = imagecreatetruecolor(250, 100);
            $black  = ImageColorAllocate($img, 0x00, 0x00, 0x00);
            $white  = ImageColorAllocate($img, 0xff, 0xff, 0xff);
            //$red    = ImageColorAllocate($img, 0xff, 0x00, 0x00);
            //$blue   = ImageColorAllocate($img, 0x00, 0x00, 0xff);
            imagefilledrectangle($img, 0, 0, 300, 300, $white);

            //$data = Barcode::gd($img, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);
            Barcode::gd($img, $black, $x, $y, $angle, $type, array('code'=>$code), $width, $height);
        //header('Content-type: image/jpg');
        if (imagepng($img, $cacheFileName)) {
            return $cacheFileName;
        } else {
            return false;
        }
        } else {
            return $cacheFileName;
        }
    }
}
