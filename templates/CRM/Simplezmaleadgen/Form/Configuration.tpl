{* HEADER *}

<div class="crm-submit-buttons">
  <hr>
</div>
{assign var=i value=0}
<table>
  {foreach from=$elementNames item=elementName}
  {assign var=m4 value=$i%4}
  {assign var=m2 value=$i%2}
  {if $m4 eq 0}
  </tr><tr class="crm-section">
    {/if}
    {if not str_contains($form.$elementName.name, "_description")}
    <td class="right"> {$form.$elementName.label}</td>
    <td>{$form.$elementName.html}
      {/if}
      {if str_contains($form.$elementName.name, "_description")}
      <div class="description"></div> {$form.$elementName.html|nl2br}</div></td>
    {/if}
    {assign var=i value=$i+1}
    {/foreach}

    {if $lastSavedCustomFieldsTable}
    <tr>
      <td colspan="3">
        {$leftEmptySpace}
      </td>
      <td colspan="2">
        {$lastSavedCustomFieldsTable}
      </td>
    </tr>
    {/if}
</table>

{*    {debug}*}
{* FOOTER *}
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>