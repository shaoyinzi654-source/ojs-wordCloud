{** wordCloud **}<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>{$journalName|escape} — {translate key="plugins.generic.wordCloud.ui.title"}</title>
	<style>
		body { font-family: -apple-system, "Segoe UI", "Microsoft YaHei", sans-serif; background: #fbfcfd; margin: 0; padding: 40px 20px; }
		.wrap { max-width: 960px; margin: 0 auto; }
		h1 { font-size: 22px; }
		.cloud { text-align: center; line-height: 2.4; }
		.cloud a { text-decoration: none; margin: 0 10px; color: #14508c; }
		.cloud a:hover { color: #d44a33; }
		.hint { color: #888; font-size: 13px; text-align: center; }
	</style>
</head>
<body>
	<div class="wrap">
		<h1>{$journalName|escape} · {translate key="plugins.generic.wordCloud.ui.title"}</h1>
		<div class="hint">{translate key="plugins.generic.wordCloud.ui.hint"}</div>
		<div class="cloud">
			{foreach from=$words item=w}
				<a href="{$w.url|escape}" style="font-size:{$w.size}px" title="{$w.count}">{$w.word|escape}</a>
			{foreachelse}
				<p>{translate key="plugins.generic.wordCloud.ui.empty"}</p>
			{/foreach}
		</div>
		<p><a href="{$backUrl|escape}">← {translate key="plugins.generic.wordCloud.ui.back"}</a></p>
	</div>
</body>
</html>
