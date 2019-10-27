<?php
/**
 * Articles Module Tests.
 *
 * @copyright  Copyright (C) 2018 catpointersolutions.com, Inc. All rights reserved.
 * @license    See COPYING.txt
 */

//namespace EstimatedReadingTime\Joomla\Plugin\Content\EstimatedReadingTime;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
//use CatPointer\Joomla\Module\Site\Articles\ArticlesModule;

require_once './src/readingtime.php';


/**
 * Base entity tests.
 *
 * @since   __DEPLOY_VERSION__
 */
class ParamsTest extends \TestCaseDatabase
{
	/**
	 * An instance of the class to test.
	 *
	 * @var    PlgContentEmailcloak
	 * @since  3.6.2
	 */
	protected $class;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   3.6.2
	 */
	public function setup()
	{
		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session = $this->getMockSession();

		// Force the cloak JS inline so that we can unit test it easier than messing with script head in document
		JFactory::getApplication()->input->server->set('HTTP_X_REQUESTED_WITH', 'xmlhttprequest');

		/**
		 * Create a mock dispatcher instance
		 *
		 * @var $dispatcher Mock_JEventDispatcher_f5646d4b e.g
		 */
		$dispatcher = TestCaseDatabase::getMockDispatcher();

		$plugin = array(
			'name'   => 'readingtime',
			'type'   => 'Content',
			'params' => new \JRegistry
		);

		$this->class = new PlgContentReadingTime($dispatcher, $plugin);
	}

	/**
	 * @test
	 *
	 * @return void
	 */
	public function constructorSetsParams()
	{
		$params = new Registry();

		$this->assertSame($params, $this->class->params);
	}
}
