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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * This function updates your module from previous versions to the version 1.1,
 * usefull when you modify your database, or register a new hook ...
 * Don't forget to create one file per version.
 */
function upgrade_module_1_1_0($module)
{
    /**
     * Do everything you want right there,
     * You could add a column in one of your module's tables
     */
     // Install the new Tab in Order menu
     $tab = new Tab();

    foreach (Language::getLanguages() as $language) {
        $tab->name[$language['id_lang']] = 'My E-tickets';
    }

    $tab->class_name = 'AdminMyetickets';
    $tab->module = 'myetickets';

    $idParent = (int)Tab::getIdFromClassName('AdminParentOrders');
    $tab->id_parent = $idParent;
    $tab->position = Tab::getNbTabs($idParent);

    if (!$tab->save()) {
        return false;
    }

    Configuration::updateValue('MYMODULE_ADMIN_TAB', $tab->id);

    return true;
}
