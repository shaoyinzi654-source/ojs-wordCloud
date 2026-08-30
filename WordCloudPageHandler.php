<?php
/**
 * @file plugins/generic/wordCloud/WordCloudPageHandler.php
 *
 * Copyright (c) 2026 Bugles (shaoyinzi654-source)
 * Distributed under the GNU GPL v3. For full terms see the file LICENSE.
 *
 * @class WordCloudPageHandler
 *
 * @brief Page handler for the wordCloud plugin (/index.php/journal/wordcloud/...).
 */

namespace APP\plugins\generic\wordCloud;

use APP\template\TemplateManager;
use PKP\handler\PKPHandler;
class WordCloudPageHandler extends \PKP\handler\PKPHandler
{
    /** @var WordCloudPlugin */
    protected $_plugin;

    public function __construct($plugin)
    {
        $this->_plugin = $plugin;
    }

    /**
     * Journal-wide keyword cloud.
     */
    public function index($args, $request)
    {
        $context = $request->getContext();
        if (!$context) {
            return $this->forbidden();
        }
        $maxArticles = max(50, min(5000, (int) ($this->_plugin->setting($request, 'max_articles') ?: 500)));
        $minCount = max(1, (int) ($this->_plugin->setting($request, 'min_count') ?: 2));
        $keywords = [];
        $submissions = \APP\facades\Repo::submission()
            ->getCollector()
            ->filterByContextIds([$context->getId()])
            ->filterByStatus([\APP\submission\Submission::STATUS_PUBLISHED])
            ->limit($maxArticles)
            ->getMany();
        foreach ($submissions as $submission) {
            $publication = $submission->getCurrentPublication();
            if (!$publication) {
                continue;
            }
            foreach ((array) $publication->getLocalizedData('keywords') as $keyword) {
                $keyword = trim((string) $keyword);
                if (mb_strlen($keyword) < 2) {
                    continue;
                }
                $keywords[$keyword] = ($keywords[$keyword] ?? 0) + 1;
            }
        }
        arsort($keywords);
        $keywords = array_filter($keywords, function ($c) use ($minCount) { return $c >= $minCount; });
        $words = [];
        $max = $keywords ? max($keywords) : 1;
        foreach ($keywords as $word => $count) {
            $words[] = [
                'word' => $word,
                'count' => $count,
                'size' => (int) round(14 + 26 * ($count / $max)),
                'url' => $request->getBaseUrl() . '/search/search?query=' . rawurlencode($word),
            ];
        }
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign([
            'words' => array_slice($words, 0, 120),
            'total' => count($words),
            'journalName' => $context->getLocalizedName(),
            'backUrl' => $request->getBaseUrl(),
        ]);
        echo $templateMgr->fetch($this->_plugin->getTemplateResource('cloudPage.tpl'));
    }

    public function forbidden()
    {
        http_response_code(403);
        echo '403 Forbidden';
    }
}
