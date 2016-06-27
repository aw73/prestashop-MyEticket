{*
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
*}
<div id="product-eticket" class="panel product-tab">
  <input type="hidden" name="submitted_tabs[]" value="Etickets" />
  <h3 class="tab"> <i class="icon-ticket"></i> {l s='E-ticket'}</h3>


  {* status informations *}
  <div class="form-group">
    <div class="col-lg-1">
      <span class="pull-right">

      </span>
    </div>
    <label class="control-label col-lg-2">
      {l s='Enabled'}
    </label>
    <div class="col-lg-9">
      <span class="switch prestashop-switch fixed-width-lg">
        <input onclick="toggleDraftWarning(false);" type="radio" name="is_eticket" id="eticket_on" value="1" {if $is_eticket}checked="checked" {/if} />
        <label for="eticket_on" class="radioCheck">
          {l s='Yes'}
        </label>
        <input onclick="toggleDraftWarning(true);"  type="radio" name="is_eticket" id="eticket_off" value="0" {if !$is_eticket}checked="checked"{/if} />
        <label for="eticket_off" class="radioCheck">
          {l s='No'}
        </label>
        <a class="slide-button btn"></a>
      </span>
    </div>
  </div>
  <div class="panel-footer">
    <a href="{$link->getAdminLink('AdminProducts')|escape:'html':'UTF-8'}{if isset($smarty.request.page) && $smarty.request.page > 1}&amp;submitFilterproduct={$smarty.request.page|intval}{/if}" class="btn btn-default"><i class="process-icon-cancel"></i> {l s='Cancel'}</a>
    <button type="submit" name="submitAddproduct" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save'}</button>
    <button type="submit" name="submitAddproductAndStay" class="btn btn-default pull-right" disabled="disabled"><i class="process-icon-loading"></i> {l s='Save and stay'}</button>
  </div>
</div>
<script type="text/javascript">
hideOtherLanguage({$default_form_language});
var missing_product_name = '{l s='Please fill product name input field' js=1}';
</script>
