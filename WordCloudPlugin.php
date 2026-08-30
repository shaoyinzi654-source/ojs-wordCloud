<?php
/**
 * @file plugins/generic/wordCloud/WordCloudPlugin.php
 *
 * Copyright (c) 2026 Bugles (shaoyinzi654-source)
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class WordCloudPlugin
 *
 * @brief Journal-wide keyword cloud page linking every term to its search results.
 */

namespace APP\plugins\generic\wordCloud;

use APP\core\Application;
use APP\template\TemplateManager;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use PKP\plugins\Hook;

class WordCloudPlugin extends GenericPlugin
{
    public function register($category, $path, $mainContextId = null): bool
    {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && !Application::isUnderMaintenance() && $this->getEnabled($mainContextId)) {
            Hook::add('LoadHandler', [$this, 'loadHandler']);
        }
        return $success;
    }

    public function getDisplayName(): string
    {
        return __('plugins.generic.wordCloud.displayName');
    }

    public function getDescription(): string
    {
        return __('plugins.generic.wordCloud.description');
    }

    public function getActions($request, $verb)
    {
        $router = $request->getRouter();
        return array_merge(
            $this->getEnabled() ? [
                new LinkAction(
                    'settings',
                    new AjaxModal(
                        $router->url($request, null, null, 'manage', null, ['verb' => 'settings', 'plugin' => $this->getName(), 'category' => 'generic']),
                        $this->getDisplayName()
                    ),
                    __('manager.plugins.settings'),
                    null
                ),
            ] : [],
            parent::getActions($request, $verb)
        );
    }

    public function manage($args, $request)
    {
        switch ($request->getUserVar('verb')) {
            case 'settings':
                $context = $request->getContext();
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->registerPlugin('function', 'plugin_url', $this->smartyPluginUrl(...));

                $form = new WordCloudSettingsForm($this, $context->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        return new JSONMessage(true);
                    }
                } else {
                    $form->initData();
                }
                return new JSONMessage(true, $form->fetch($request));
        }
        return parent::manage($args, $request);
    }

    /**
     * Build the URL for a file shipped with this plugin.
     */
    public function assetUrl($request, string $relative): string
    {
        return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/' . $relative;
    }

    /**
     * Fetch one of this plugin's context settings, falling back to the
     * declared default before a manager has saved the settings form.
     */
    public function setting($request, string $key)
    {
        $context = $request->getContext();
        $value = $this->getSetting($context ? $context->getId() : \PKP\core\PKPApplication::CONTEXT_ID_NONE, $key);
        if ($value === null || $value === '') {
            $defaults = $this->getDefaultSettings();
            if (array_key_exists($key, $defaults)) {
                return $defaults[$key];
            }
        }
        return $value;
    }

    /**
     * Default values for all settings, used until the settings form is saved.
     */
    public function getDefaultSettings(): array
    {
        return [
            'max_articles' => 500,
            'min_count' => 1,
        ];
    }

    /**
     * Fetch a template shipped with this plugin, with variables assigned.
     */
    protected function fetchTemplate($templateMgr, string $name, array $vars = []): string
    {
        foreach ($vars as $k => $v) {
            $templateMgr->assign($k, $v);
        }
        return $templateMgr->fetch($this->getTemplateResource($name));
    }

    /**
     * True when the current request is a public frontend page.
     */
    protected function isFrontendPage($request): bool
    {
        $router = $request->getRouter();
        if (!$router instanceof \PKP\core\PKPPageRouter) {
            return false;
        }
        $page = $router->getRequestedPage($request);
        $backendPages = ['admin', 'manager', 'user', 'login', 'signout', 'profile', 'dashboard', 'submissions', 'payment', 'library', 'api', 'pages'];
        return !in_array($page, $backendPages, true);
    }


    /**
     * Register this plugin's custom page handler (/index.php/wordcloud/...).
     */
    public function loadHandler(string $hookName, &$page, &$op, &$sourceFile, &$handler): bool
    {
        if ($page !== 'wordcloud') {
            return Hook::CONTINUE;
        }
        require_once implode(DIRECTORY_SEPARATOR, [dirname(__FILE__), 'WordCloudPageHandler.php']);
        $handler = new WordCloudPageHandler($this);
        return Hook::ABORT;
    }

}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\wordCloud\WordCloudPlugin', '\WordCloudPlugin');
}
