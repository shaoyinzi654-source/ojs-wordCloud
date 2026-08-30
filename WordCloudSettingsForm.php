<?php
/**
 * @file plugins/generic/wordCloud/WordCloudSettingsForm.php
 *
 * Copyright (c) 2026 Bugles (shaoyinzi654-source)
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class WordCloudSettingsForm
 *
 * @brief Settings form for the wordCloud plugin.
 */

namespace APP\plugins\generic\wordCloud;

use APP\template\TemplateManager;
use PKP\form\Form;

class WordCloudSettingsForm extends Form
{
    /** @var int */
    public $_contextId;

    /** @var object */
    public $_plugin;

    public function __construct($plugin, $contextId)
    {
        $this->_contextId = $contextId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplateResource('settingsForm.tpl'));

		$this->addCheck(new \PKP\form\validation\FormValidatorCustom($this, 'max_articles', 'optional', function ($v) { return is_numeric($v); }));
		$this->addCheck(new \PKP\form\validation\FormValidatorCustom($this, 'min_count', 'optional', function ($v) { return is_numeric($v); }));
        $this->addCheck(new \PKP\form\validation\FormValidatorPost($this));
        $this->addCheck(new \PKP\form\validation\FormValidatorCSRF($this));
    }

    public function initData()
    {
        $this->_data = [
			'max_articles' => (int) ($this->_plugin->getSetting($this->_contextId, 'max_articles') ?: 500),
			'min_count' => (int) ($this->_plugin->getSetting($this->_contextId, 'min_count') ?: 1),
        ];
        parent::initData();
    }

    public function readInputData()
    {
        $this->readUserVars([
			'max_articles',
			'min_count',
        ]);
    }

    public function fetch($request, $template = null, $display = false)
    {
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->_plugin->getName());
        return parent::fetch($request, $template, $display);
    }

    public function execute(...$functionArgs)
    {
			$this->_plugin->updateSetting($this->_contextId, 'max_articles', max(1, (int) $this->getData('max_articles')), 'int');
			$this->_plugin->updateSetting($this->_contextId, 'min_count', max(1, (int) $this->getData('min_count')), 'int');
        parent::execute(...$functionArgs);
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\plugins\generic\wordCloud\WordCloudSettingsForm', '\WordCloudSettingsForm');
}
