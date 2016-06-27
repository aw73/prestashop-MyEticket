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
{capture name=path}
	<a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
		{l s='My account' mod='myetickets'}
	</a>
	<span class="navigation-pipe">{$navigationPipe}</span>
	<span class="navigation_page">{l s='E-tickets history' mod='myetickets'}</span>
{/capture}
{include file="$tpl_dir./errors.tpl"}
<h1 class="page-heading bottom-indent">{l s='My E-tickets' mod='myetickets'}</h1>
<p class="info-title">{l s='Here are the e-tickets you\'ve placed since your account was created.' mod='myetickets'}</p>
<div class="block-center" id="block-history">
	{if $etickets && count($etickets)}
  {foreach from=$etickets item=Eticket}
  <form method="post" action="{$link->getModuleLink('myetickets', 'history')}">
    <input type="hidden" name="id_myetickets" value="{$Eticket->id}">
    <div class="col-md-4">
      <div class="panel panel-default">
        <div class="panel-body">
          <h3>{$Eticket->product_name}</h3>
          <p>
            {if $Eticket->checked}
              <span class="label label-danger">
                <i class="icon-exclamation-sign"></i>
                {l s='Used on' mod='myetickets'} {dateFormat date=$Eticket->check_date}
              </span>
            {else}
              <span class="label label-success">
                <i class="icon-check"></i>
                {l s='No checked' mod='myetickets'}
              </span>
            {/if}
          </p>
          <div>{$Eticket->ean13}</div>
          <div>
            <strong>
              {l s='For' mod='myetickets'}
              {$Eticket->quantity}
              {if $Eticket->quantity > 1 }
                {l s='Persons' mod='myetickets'}
              {else}
                {l s='Person' mod='myetickets'}
              {/if}
            </strong>
          </div>
          <br>
          <p>
            <button type="submit" value="1" name="submitMyeticketsPdf" class="btn btn-default"{if $Eticket->checked}disabled="disabled"{/if}>
              <i class="icon-file-text"></i> {l s='PDF Ticket' mod='myetickets'}
            </button>
          </p>
        </div>
      </div>
    </div>
  </form>
  {/foreach}
	{else}
		<p class="alert alert-warning">{l s='You have not placed any e-tickets.' mod='myetickets'}</p>
	{/if}
</div>
<div class="clearfix"></div>
<ul class="footer_links clearfix">
	<li>
		<a class="btn btn-default button button-small" href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}">
			<span>
				<i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='myetickets'}
			</span>
		</a>
	</li>
	<li>
		<a class="btn btn-default button button-small" href="{$base_dir}">
			<span><i class="icon-chevron-left"></i> {l s='Home' mod='myetickets'}</span>
		</a>
	</li>
</ul>
