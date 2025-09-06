<?php
define('_JEXEC', 1);

error_reporting(E_ALL);
ini_set('display_errors', 1);

ini_set('precision', 14);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

// Load .env file
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$joomlaRootDirectory = getenv('JOOMLA_ROOT_DIRECTORY');

if (!$joomlaRootDirectory) {
    throw new Exception('JOOMLA_ROOT_DIRECTORY environment variable is not defined.');
}

if (!is_dir($joomlaRootDirectory)) {
    throw new Exception('JOOMLA_ROOT_DIRECTORY is not a valid directory. Please check your .env file.');
}


if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', $joomlaRootDirectory);
}

if (!defined('JPATH_ROOT')) {
    define('JPATH_ROOT', JPATH_BASE);
}

if (!defined('JPATH_PLATFORM')) {
    define('JPATH_PLATFORM', JPATH_BASE . DIRECTORY_SEPARATOR . 'libraries');
}

if (!defined('JPATH_LIBRARIES')) {
    define('JPATH_LIBRARIES', JPATH_BASE . DIRECTORY_SEPARATOR . 'libraries');
}

if (!defined('JPATH_CONFIGURATION')) {
    define('JPATH_CONFIGURATION', JPATH_BASE);
}

if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', JPATH_ROOT);
}

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
}

if (!defined('JPATH_CACHE')) {
    define('JPATH_CACHE', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'cache');
}

if (!defined('JPATH_API')) {
    define('JPATH_API', JPATH_ROOT . DIRECTORY_SEPARATOR . 'api');
}

if (!defined('JPATH_INSTALLATION')) {
    define('JPATH_INSTALLATION', JPATH_ROOT . DIRECTORY_SEPARATOR . 'installation');
}

if (!defined('JPATH_MANIFESTS')) {
    define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');
}

if (!defined('JPATH_PLUGINS')) {
    define('JPATH_PLUGINS', JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins');
}

if (!defined('JPATH_THEMES')) {
    define('JPATH_THEMES', JPATH_BASE . DIRECTORY_SEPARATOR . 'templates');
}

if (!defined('JDEBUG')) {
    define('JDEBUG', false);
}

require_once JPATH_BASE . '/includes/framework.php';

//
// Instantiate the application.
$app = JFactory::getApplication('site');