{**
 * templates/settingsForm.tpl
 *
 * Copyright (c) 2026 Bugles (shaoyinzi654-source)
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * Settings form for the wordCloud plugin.
 *}
<script>
	$(function() {ldelim}
		$('#wordCloudSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="wordCloudSettingsForm" method="post" action="{url router=PKP\core\PKPApplication::ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="wordCloudSettingsFormNotification"}

	<div id="description">{translate key="plugins.generic.wordCloud.manager.settings.description"}</div>

	{fbvFormArea id="wordCloudSettingsFormArea"}
		{fbvElement type="text" id="max_articles" value=$max_articles label="plugins.generic.wordCloud.manager.settings.max_articles" size=$fbvStyles.size.MEDIUM}
		{fbvElement type="text" id="min_count" value=$min_count label="plugins.generic.wordCloud.manager.settings.min_count" size=$fbvStyles.size.MEDIUM}
	{/fbvFormArea}

	{fbvFormButtons}

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</form>
