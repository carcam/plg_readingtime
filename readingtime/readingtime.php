<?php
/**
 * @author                Carlos M. Cámara
 * @copyright           Copyright (C) 2012 Carlos M. Cámara Mora. All rights reserved.
 * @license                GNU General Public License version 2 or later; see LICENSE.txt
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
		$html = '';
                
                                    //Get Params
                                    $excludedCategories = $this->params->def('excludedcategories');
                                    
                                    if(!in_array($row->catid, $excludedCategories))
                                    {
                                    
                                        //Word per minute
                                        $lowRate = 200;
                                        $highRate = 400;
                                        
                                        if(!isset($row->fulltext))
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

                                        $quickTime = ceil($countWords/$lowRate);
                                        $slowTime = ceil($countWords/$highRate);

                                        $customStyle =$this->params->def( 'custom-style', '');

                                        $html="<div class=\"reading-time\"";
                                        if ( $this->params->def( 'default-style', '1') )    
                                        {
                                            $html .= "style=\"font-weight:bold;\"";
                                        }
                                        else if($customStyle!="")
                                        {
                                            $html .= "style=\"".$customStyle."\"";
                                        }
                                        $html .= ">(".JText::_('PLG_READINGTIME_LABEL').": ";
                                        if ($quickTime == $slowTime)
                                        {
                                            $html .= $quickTime;
                                        }
                                        else
                                        {
                                            $html .= $slowTime." - ". $quickTime;
                                        }

                                        $html .= " ".JText::plural('PLG_READINGTIME_N_MINUTES', $quickTime ).")</div>";
                                    }
		return $html;
	}
}
