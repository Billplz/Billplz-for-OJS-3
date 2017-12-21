{**
 * plugins/paymethod/billplz/templates/settingsForm.tpl
 *
 * Form for billplz payment settings.
 *}

{fbvFormSection title="plugins.paymethod.billplz.settings"}
	{fbvElement type="text" name="billplz_api_key" id="billplz_api_key" value=$billplz_api_key label="plugins.paymethod.billplz.settings.billplz_api_key"}
	{fbvElement type="text" name="billplz_collection_id" id="billplz_collection_id" value=$billplz_collection_id label="plugins.paymethod.billplz.settings.billplz_collection_id"}
	{fbvElement type="text" name="billplz_x_signature" id="billplz_x_signature" value=$billplz_x_signature label="plugins.paymethod.billplz.settings.billplz_x_signature"}
{/fbvFormSection}
{fbvFormSection for="testMode" list=true}
  {fbvElement type="select" label="plugins.paymethod.billplz.settings.billplz_deliver" from=$billplz_delivers selected=$billplz_deliver id="billplz_deliver" translate=false size=$fbvStyles.size.SMALL inline="true"}
{/fbvFormSection}
