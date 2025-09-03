<?php

use Robo\Exception\TaskException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends \Robo\Tasks
{
    private const SRC = 'src';
    private const BUILD = 'build/package';

    public function __construct()
    {
        if (file_exists(__DIR__ . '/.env')) {
            $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
            $dotenv->load();
        }
    }

    /**
     * Cleans the build directory.
     */
    public function clean()
    {
        $this->taskDeleteDir(self::BUILD)->run();
        $this->_mkdir(self::BUILD);
        $this->say('Build directory cleaned.');
    }

    /**
     * Returns a path to a temporary directory for packaging operations.
     *
     * @return string
     */
    private function tmpDir(): string
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'heptacalcom_robo_temp_' . uniqid();
        $this->_mkdir($tempDir);
        return $tempDir;
    }

    /**
     * Packages the entire Hepta Cal.com extension.
     *
     * This task orchestrates the packaging of the component, modules, and plugins
     * into a single installable package for Joomla.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function package()
    {
        $this->clean();

        $this->say("Starting the packaging process...");

        $componentZip = $this->packageComponent();
        $moduleZips = $this->packageModules();
        $pluginZips = $this->packagePlugins();

        $this->say("Creating final package...");

        $finalPackageName = 'pkg_heptacalcom';
        $finalPackageZip = self::BUILD . '/' . $finalPackageName . '.zip';
        $packageTask = $this->taskPack($finalPackageZip);

        // Add the extension zips to the package
        $packageTask->addFile($componentZip, basename($componentZip));
        foreach ($moduleZips as $zip) {
            $packageTask->addFile($zip, basename($zip));
        }
        foreach ($pluginZips as $zip) {
            $packageTask->addFile($zip, basename($zip));
        }

        // Add the package manifest
        $packageTask->addFile(self::SRC . '/pkg_heptacalcom.xml', 'pkg_heptacalcom.xml');

        $packageTask->run();

        $this->say("Final package created successfully: " . $finalPackageZip);
        $this->say("Packaging process complete!");
    }

    /**
     * Installs the extension to a local Joomla dev environment.
     *
     * @param string $type The type of extension to install (package, component, module, plugin). Defaults to package.
     * @param string|null $name The name of the module or plugin to install (e.g. mod_heptacalcom_keywords_cloud).
     *
     * @throws \Robo\Exception\TaskException
     */
    public function install($type = 'package', $name = null, $plugin_family = null)
    {
        $this->clean();
        $zipPath = '';

        switch ($type) {
            case 'package':
                $this->package();
                $zipPath = __DIR__ . '/' . self::BUILD . '/pkg_heptacalcom.zip';
                break;
            case 'component':
                $zipPath = $this->packageComponent();
                break;
            case 'module':
                if (empty($name)) {
                    throw new TaskException($this, 'Please provide the name of the module to install.');
                }
                $zipPath = $this->packageSingleModule($name);
                break;
            case 'plugin':
                if (empty($name)) {
                    throw new TaskException($this, 'Please provide the name of the plugin to install.');
                }
                $zipPath = $this->packageSinglePlugin($name, $plugin_family);
                break;
            default:
                throw new TaskException($this, "Invalid installation type: {$type}. Valid types are: package, component, module, plugin.");
        }

        $this->runJoomlaInstall($zipPath);
        $this->cleanCache();
        $this->launchAdmin();
    }

    /**
     * Runs integration tests.
     *
     * @throws \Robo\Exception\TaskException
     */
    public function testIntegration()
    {
        $this->say("Running integration tests...");

        // First, install the plugin
        $this->install('plugin', 'heptacalcom', 'content');

        $this->say("Copying tests to ddev environment...");

        $joomlaPath = $_ENV['JOOMLA_PATH'] ?? '';
        if (empty($joomlaPath) || !is_dir($joomlaPath)) {
            throw new TaskException($this, "Joomla installation path not found or not set in .env file. Please set JOOMLA_PATH.");
        }

        $this->taskFilesystemStack()
            ->copy(__DIR__ . '/phpunit.integration.xml', $joomlaPath . '/phpunit.integration.xml', true)
            ->run();

        $this->taskCopyDir([__DIR__ . '/tests' => $joomlaPath . '/tests'])->run();

        $this->say("Running integration tests...");

        $command = sprintf(
            'cd %s && ddev php vendor/bin/phpunit --configuration ../phpunit.integration.xml',
            escapeshellarg($joomlaPath)
        );

        $result = $this->taskExec($command)->run();

        if (!$result->wasSuccessful()) {
            throw new TaskException(
                $this,
                sprintf("Integration tests failed with exit code %s.\nOutput:\n%s", $result->getExitCode(), $result->getMessage())
            );
        }

        $this->say("Integration tests passed.");
    }

    /**
     * @throws \Robo\Exception\TaskException
     */
    private function packageComponent(): string
    {
        $this->say("Packaging component...");
        $componentName = 'com_heptacalcom';
        $componentPath = self::SRC . '/component';
        $buildDir = self::BUILD . '/' . $componentName;

        $fs = new Filesystem();
        $tempDir = $this->tmpDir() . '/heptacalcom_component_temp';
        $this->_mkdir($tempDir);

        $finder = (new \Symfony\Component\Finder\Finder())
            ->in($componentPath)
            ->exclude('*~');

        foreach ($finder as $file) {
            if ($file->isDir()) {
                $fs->mkdir($tempDir . '/' . $file->getRelativePathname());
            } else {
                $fs->copy($file->getRealPath(), $tempDir . '/' . $file->getRelativePathname());
            }
        }

        $zipFile = self::BUILD . '/' . $componentName . '.zip';
        $this->taskPack($zipFile)->addDir($componentName, $tempDir)->run();
        $this->taskDeleteDir($tempDir)->run();

        $this->say("Component packaged successfully: " . $zipFile);
        return __DIR__ . '/' . $zipFile;
    }

    /**
     * @throws \Robo\Exception\TaskException
     */
    private function packageModules(): array
    {
        $this->say("Packaging modules...");
        $moduleDir = self::SRC . '/modules';
        $zips = [];

        $modules = (new \Symfony\Component\Finder\Finder())
            ->in($moduleDir)
            ->directories()
            ->depth(0);

        foreach ($modules as $module) {
            $modulePath = $module->getRealPath();
            $moduleName = $module->getBasename();

            // Find the XML file to be sure it is a module
            $finder = (new \Symfony\Component\Finder\Finder())->in($modulePath)->name('mod_*.xml')->depth(0);
            if (count($finder) === 0) {
                continue;
            }

            $zipFile = self::BUILD . '/' . $moduleName . '.zip';
            $fs = new Filesystem();
            $tempDir = $this->tmpDir() . '/heptacalcom_module_' . $moduleName . '_temp';
            $this->_mkdir($tempDir);

            $finder = (new \Symfony\Component\Finder\Finder())
                ->in($modulePath)
                ->exclude('*~');

            foreach ($finder as $file) {
                if ($file->isDir()) {
                    $fs->mkdir($tempDir . '/' . $file->getRelativePathname());
                } else {
                    $fs->copy($file->getRealPath(), $tempDir . '/' . $file->getRelativePathname());
                }
            }

            $this->taskPack($zipFile)->addDir($moduleName, $tempDir)->run();
            $this->taskDeleteDir($tempDir)->run();
            $zips[] = $zipFile;
            $this->say(" - Packaged module: " . $moduleName);
        }

        $this->say("Modules packaged successfully.");
        return $zips;
    }

    /**
     * @throws \Robo\Exception\TaskException
     */
    private function packagePlugins(): array
    {
        $this->say("Packaging plugins...");
        $pluginDir = self::SRC . '/plugins';
        $zips = [];

        $plugins = (new \Symfony\Component\Finder\Finder())
            ->in($pluginDir)
            ->directories()
            ->depth(0);

        foreach ($plugins as $plugin) {
            $pluginPath = $plugin->getRealPath();
            $pluginName = $plugin->getBasename();

            // Find the XML file to be sure it is a plugin
            $finder = (new \Symfony\Component\Finder\Finder())->in($pluginPath)->name('*.xml')->depth(0);
            if (count($finder) === 0) {
                continue;
            }

            $zipFile = self::BUILD . '/' . $pluginName . '.zip';
            $fs = new Filesystem();
            $tempDir = $this->tmpDir() . '/heptacalcom_plugin_' . $pluginName . '_temp';
            $this->_mkdir($tempDir);

            $finder = (new \Symfony\Component\Finder\Finder())
                ->in($pluginPath)
                ->exclude('*~');

            foreach ($finder as $file) {
                if ($file->isDir()) {
                    $fs->mkdir($tempDir . '/' . $file->getRelativePathname());
                } else {
                    $fs->copy($file->getRealPath(), $tempDir . '/' . $file->getRelativePathname());
                }
            }

            $this->taskPack($zipFile)->addDir($name, $tempDir)->run();
            $this->taskDeleteDir($tempDir)->run();
            $zips[] = $zipFile;
            $this->say(" - Packaged plugin: " . $pluginName);
        }

        $this->say("Plugins packaged successfully.");
        return $zips;
    }

    /**
     * @throws \Robo\Exception\TaskException
     */
    private function packageSingleModule(string $name): string
    {
        $this->say("Packaging single module: {$name}...");
        $modulePath = self::SRC . '/modules/' . $name;

        if (!is_dir($modulePath)) {
            throw new TaskException($this, "Module source directory not found: {$modulePath}");
        }

        $zipFile = self::BUILD . '/' . $name . '.zip';
        $fs = new Filesystem();
        $tempDir = $this->tmpDir() . '/heptacalcom_single_module_' . $name . '_temp';
        $this->_mkdir($tempDir);

        $finder = (new \Symfony\Component\Finder\Finder())
            ->in($modulePath)
            ->exclude('*~');

        foreach ($finder as $file) {
            if ($file->isDir()) {
                $fs->mkdir($tempDir . '/' . $file->getRelativePathname());
            } else {
                $fs->copy($file->getRealPath(), $tempDir . '/' . $file->getRelativePathname());
            }
        }

        $this->taskPack($zipFile)->addDir($name, $tempDir)->run();
        $this->taskDeleteDir($tempDir)->run();

        $this->say(" - Packaged module: " . $name);
        return __DIR__ . '/' . $zipFile;
    }

    /**
     * @throws \Robo\Exception\TaskException
     */
    private function packageSinglePlugin(string $name, string $type): string
    {
        $this->say("Packaging single plugin: {$type}/{$name}...");
        $pluginPath = self::SRC . '/plugins/' . $type . '/plg_' . $type . '_' . $name;

        if (!is_dir($pluginPath)) {
            throw new TaskException($this, "Plugin source directory not found: {$pluginPath}");
        }

        $zipFile = self::BUILD . '/plg_' . $type . '_' . $name . '.zip';
        $fs = new Filesystem();
        $tempDir = $this->tmpDir() . '/heptacalcom_single_plugin_' . $type . '_' . $name . '_temp';
        $this->_mkdir($tempDir);

        $finder = (new \Symfony\Component\Finder\Finder())
            ->in($pluginPath)
            ->exclude('*~');

        foreach ($finder as $file) {
            if ($file->isDir()) {
                $fs->mkdir($tempDir . '/' . $file->getRelativePathname());
                } else {
                    $fs->copy($file->getRealPath(), $tempDir . '/' . $file->getRelativePathname());
                }
            }

            // The addDir method expects the directory name within the zip.
            // This should be the full plugin folder name, e.g., 'plg_content_heptacalcom'
            $this->taskPack($zipFile)->addDir('plg_' . $type . '_' . $name, $tempDir)->run();
            $this->taskDeleteDir($tempDir)->run();

            $this->say(" - Packaged plugin: " . $type . '/' . $name);
            return __DIR__ . '/' . $zipFile;
        }

        /**
         * @throws \Robo\Exception\TaskException
         */
        private function runJoomlaInstall(string $packagePath)
        {
            $this->say("Installing {$packagePath} to local environment...");

            $joomlaPath = $_ENV['JOOMLA_PATH'] ?? '';
            if (empty($joomlaPath) || !is_dir($joomlaPath)) {
                throw new TaskException($this, "Joomla installation path not found or not set in .env file. Please set JOOMLA_PATH.");
            }

            // 1. Copy the package to the DDEV temp directory
            $packageName = basename($packagePath);
            $tempDir = $joomlaPath . '/tmp';
            $this->_mkdir($tempDir);
            $copyResult = $this->taskFilesystemStack()
                ->copy($packagePath, $tempDir . '/' . $packageName, true)
                ->run();

            if (!$copyResult->wasSuccessful()) {
                throw new TaskException(
                    $this,
                    sprintf("Failed to copy package to DDEV temp directory: %s", $copyResult->getMessage())
                );
            }

            $this->say("Copied package to {$tempDir}");

            // 2. Use the correct in-container relative path for the installation
            $inContainerPath = 'tmp/' . $packageName;

            $command = sprintf(
                'ddev php joomla/cli/joomla.php extension:install --path=%s',
                escapeshellarg('joomla/' . $inContainerPath)
            );

            $result = $this->taskExec($command)->run();

            if (!$result->wasSuccessful()) {
                throw new TaskException(
                    $this,
                    sprintf("Installation command failed with exit code %s.\nOutput:\n%s", $result->getExitCode(), $result->getMessage())
                );
            }

            $this->say("Installation complete.");
        }

        private function cleanCache()
        {
            $joomlaPath = $_ENV['JOOMLA_PATH'] ?? '';

            if (empty($joomlaPath) || !is_dir($joomlaPath)) {
                throw new TaskException($this, "Joomla installation path not found or not set in .env file. Please set JOOMLA_PATH.");
            }

            $command = sprintf(
                'ddev php %s/cli/joomla.php cache:clean',
                escapeshellarg($joomlaPath));

            $result = $this->taskExec($command)->run();

            if (!$result->wasSuccessful()) {
                throw new TaskException(
                    $this,
                    sprintf("Cache clear command failed with exit code %s.\nOutput:\n%s", $result->getExitCode(), $result->getMessage())
                );
            }
            $this->say("Joomla! Cache cleared.");
        }

        private function launchAdmin()
        {
            $command = sprintf( 'ddev launch administrator');

            $result = $this->taskExec($command)->run();

        }
    }
