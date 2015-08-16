<?php
/**
 * @author		Carlos M. Cámara
 * @copyright	Copyright (C) 2012-2014 Carlos M. Cámara Mora. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * ERT plugin.
 *
 * @package	Joomla.Plugin
 * @subpackage	Content.readingtime
 */
class plgContentReadingtime extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	* @since	1.6
	*/
	public function onContentBeforeDisplay($context, &$row, &$params, $page=0)
	{
		if ($context == 'com_content.article' || $context == 'com_content.featured' || $context == 'com_content.category')
		{
			$html = '';

			//Get Params
			$excludedCategories = $this->params->def('excludedcategories');

			if($excludedCategories)
			{
				if(in_array($row->catid, $excludedCategories))
				{
					return;
				}
			}

			//Word per minute
			$lowRate = 200;
			$highRate = 400;

			if(!isset($row->fulltext) && isset($row->id))
			{
				$db = JFactory::getDbo();
				$query = "SELECT `fulltext` FROM #__content WHERE id=".$row->id;
				$db->setQuery($query);
				$fullText = $db->loadResult();

				$fullArticle = $row->introtext." ".$fullText;
			}
			else
			{
				$fullArticle = $row->introtext." ".$row->fulltext;
			}

			$countWords = str_word_count(strip_tags($fullArticle));

			$slowTime = ceil($countWords/$lowRate);
			$quickTime = ceil($countWords/$highRate);

			if ( $this->params->def( 'default-style', '1') )
			{
				$customStyle = "font-weight:bold;";
			}
			else
			{
				$customStyle =$this->params->def( 'custom-style', '');
			}

			//Render plugin
			$path = JPluginHelper::getLayoutPath('content', 'readingtime');
			ob_start();
			include $path;
			$html = ob_get_clean();

			$readingTimeData = new stdClass();
			$readingTimeData->slowtime = $slowTime;
			$readingTimeData->quicktime = $quickTime;
			$readingTimeData->formattedtime = $html;
			
			$row->readingtime = $readingTimeData;

			if ( !$this->params->def( 'hide-output', '0') )
			{
				return $html;
			}
		}
		return;
	}
}
