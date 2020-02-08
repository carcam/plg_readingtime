<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;

JFormHelper::loadFieldClass('List');

/**
 * K2 Categories Field class for the Estimated Reading Time Plugin.
 *
 * @since  3.6.0
 */
class ErtFormFieldK2categories extends \JFormFieldList
{
	protected $type = "ert.k2categories";

	public function getLabel()
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_k2/elements/categoriesmultiple.php'))
		{
			return '';
		}
	}

	public function getInput()
	{
		if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_k2/elements/categoriesmultiple.php'))
		{
			return '';
		}

		require_once JPATH_ADMINISTRATOR . '/components/com_k2/elements/categoriesmultiple.php';

		$k2CategoriesElement = new K2ElementCategoriesMultiple;

		$name = $this->name;
		$value = $this->value;
		$node = new stdClass;

		return $k2CategoriesElement->fetchElement($name, $value,  $node, $name);
	}
}
