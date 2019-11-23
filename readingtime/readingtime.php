<?php
/**
 * @author	  Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url		 https://extensions.hepta.es
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

/**
 * ERT plugin.
 * @since 1.0.0
 */
class PlgContentReadingtime extends CMSPlugin
{
	/**
	 * The database object
	 *
	 * @var JDatabaseInterface
	 */
	protected $db;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var	boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = true;

	/**
	 * Words per minutes for a slow reader
	 *
	 * @var integer
	 */
	protected $lowRate = 200;

	/**
	 * Words per minutes for a fast reader
	 *
	 * @var int
	 */
	protected $highRate = 400;

	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		if (!in_array($context, array('com_content.article', 'com_content.featured', 'com_content.category')))
		{
			return;
		}

		$excludedCategories = $this->params->get('excludedcategories', array());

		if ($excludedCategories && in_array($row->catid, $excludedCategories))
		{
			return;
		}

		if (!isset($row->fulltext) && isset($row->id))
		{
			$query = $this->db->getQuery(true);

			$query	->select($query->qn('fulltext'))
					->from($query->qn('#__content'))
					->where($query->qn('id') . ' = ' . (int) $row->id);

			$fullText = $this->db->setQuery($query)->loadResult();

			$fullArticle = $row->introtext . " " . $fullText;
		}
		else
		{
			$fullArticle = $row->introtext . " " . $row->fulltext;
		}

		if (!function_exists('mb_str_word_count'))
		{
			require_once dirname(__FILE__) . '/libraries/string.php';
		}

		$countWords = mb_str_word_count(strip_tags($fullArticle));

		$slowTime = ceil($countWords / $this->lowRate);
		$quickTime = ceil($countWords / $this->highRate);

		if ($this->params->def('default-style', '1'))
		{
			$customStyle = "font-weight:bold;";
		}
		else
		{
			$customStyle = $this->params->def('custom-style', '');
		}

		// Render plugin
		$path = PluginHelper::getLayoutPath('content', 'readingtime');

		ob_start();
		include $path;
		$html = ob_get_clean();

		$readingTimeData = new stdClass;

		$readingTimeData->slowtime = $slowTime;
		$readingTimeData->quicktime = $quickTime;
		$readingTimeData->wordCount = $countWords;
		$readingTimeData->formattedtime = $html;

		$row->readingtime = $readingTimeData;

		if (!$this->params->def('hideoutput', '0'))
		{
			return $html;
		}
	}

	public function onContentPrepare($context, &$row, &$params, $page=0)
	{
		if ($context !== 'com_content.article' || !$this->params->get('showindicator', '0'))
		{
			return;
		}

		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'plg_content_readingtime/readingprogress.js', false, true);
		HTMLHelper::_('stylesheet', 'plg_content_readingtime/readingprogress.css', array(), true);

		$indicatorType = $this->params->get('bar_indicator_type', '');

		if ($indicatorType)
		{
			$indicatorBarContext  = $this->params->get('bar_indicator_context', '');
			$indicatorBarStriped  = ($this->params->get('bar_indicator_striped', '0')) ? 'striped' : '';
			$indicatorBarAnimated = ($this->params->get('bar_indicator_animated', '0')) ? ' active' : '';
			$indicatorLabel       = $this->params->get('showindicatorlabel', '0');
		}

		$layout = new JLayoutFile('progressbar', null, array('debug' => false, 'suffixes' => array($indicatorType)));
		$layout->addIncludePaths(JPATH_PLUGINS . '/content/readingtime/layouts');

		$row->text = $layout->render(
			compact('indicatorBarContext', 'indicatorBarStriped', 'indicatorBarAnimated', 'indicatorLabel')
		)
			. '<span id="ert-start"></span>' . $row->text;
	}
}
