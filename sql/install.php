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

$sql = array();

// Create module table
$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'myetickets` (
    `id_myetickets` int(11) NOT NULL AUTO_INCREMENT,
    `ean13` VARCHAR(50) NOT NULL,
    `id_product` INT(10) UNSIGNED NOT NULL,
    `id_order` INT(10) UNSIGNED NOT NULL,
    `id_product_attribute` INT(10) UNSIGNED NOT NULL,
    `id_customer` INT(10) UNSIGNED NOT NULL,
    `check_date` DATETIME NULL DEFAULT NULL,
    `checked` TINYINT(1) UNSIGNED NOT NULL,
    `quantity` INT(10) UNSIGNED NOT NULL,
    `product_name` VARCHAR(128) NOT NULL,
    `product_description` TEXT NOT NULL,
    `product_attributes` VARCHAR(255) NOT NULL,
    `total_wt` DECIMAL(20,6) NOT NULL,
    `date_paid` DATETIME NOT NULL,
    PRIMARY KEY  (`id_myetickets`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

// Add poduct field module
$sql[] = 'ALTER TABLE ' . _DB_PREFIX_ . 'product ADD `is_eticket` TINYINT(1) UNSIGNED NOT NULL';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
