<?php

declare(strict_types=1);

namespace PhpTaskman\Drupal\Robo\Plugin\Commands;

use Boedah\Robo\Task\Drush\loadTasks;
use Consolidation\AnnotatedCommand\CommandData;
use PhpTaskman\Core\Robo\Plugin\Commands\AbstractCommands;
use PhpTaskman\CoreTasks\Plugin\Task\CollectionFactoryTask;
use PhpTaskman\CoreTasks\Plugin\Task\WritePhpTask;
use PhpTaskman\Drupal\Contract\FilesystemAwareInterface;
use PhpTaskman\Drupal\Traits\FilesystemAwareTrait;
use Robo\Common\BuilderAwareTrait;
use Robo\Common\ResourceExistenceChecker;
use Robo\Contract\BuilderAwareInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractDrupalCommands.
 */
abstract class AbstractDrupalCommands extends AbstractCommands implements
    BuilderAwareInterface,
    FilesystemAwareInterface
{
    use \Robo\Task\File\loadTasks;
    use BuilderAwareTrait;
    use FilesystemAwareTrait;
    use loadTasks;
    use ResourceExistenceChecker;

    /**
     * Write Drush configuration files to given directories.
     *
     * Works for both Drush 8 and 9, by default it will:
     *
     * - Generate a Drush 9 configuration file at "${drupal.root}/drush/drush.yml"
     * - Generate a Drush 8 configuration file at "${drupal.root}/sites/all/default/drushrc.php"
     *
     * Configuration file contents can be customized by editing "drupal.drush"
     * values in your local taskman.yml.dist/taskman.yml, as shown below:
     *
     * > drupal:
     * >   drush:
     * >     options:
     * >       ignored-directories: "${drupal.root}"
     * >       uri: "${drupal.base_url}"
     *
     * @command drupal:drush-setup
     *
     * @option root         Drupal root.
     * @option config-dir   Directory where to store Drush 9 configuration file.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function drushSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'config-dir' => InputOption::VALUE_REQUIRED,
    ])
    {
        $config = 'drupal.drush';
        $yaml = Yaml::dump($this->getConfig()->get($config));

        $arguments = [
            'file' => $options['root'] . '/sites/default/drushrc.php',
            'config' => $config,
            'blockStart' => '// Start settings processor block.',
            'blockEnd' => '// End settings processor block.',
        ];

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $this->task(WritePhpTask::class)->setTaskArguments($arguments),
                $this->taskWriteToFile($options['config-dir'] . '/drush.yml')->text($yaml),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFile(): string
    {
        return __DIR__ . '/../../../../config/commands/drupal.yml';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConfigurationFile(): string
    {
        return __DIR__ . '/../../../../config/default.yml';
    }

    /**
     * @return \PhpTaskman\Drupal\Robo\Plugin\Commands\Drupal7Commands|\PhpTaskman\Drupal\Robo\Plugin\Commands\Drupal8Commands
     */
    public function getDrupal()
    {
        return 7 === $this->getConfig()->get('drupal.core') ?
            new Drupal7Commands() :
            new Drupal8Commands();
    }

    /**
     * {@inheritdoc}
     */
    public function getValuelessConfigurationKeys(): array
    {
        return [
            'drupal:site-install' => [
                'existing-config' => 'drupal.site.existing_config',
                'skip-permissions-setup' => 'drupal.site.skip_permissions_setup',
            ],
        ];
    }

    /**
     * Setup Drupal permissions.
     *
     * This command will set the necessary permissions on the default folder.
     *
     * @command drupal:permissions-setup
     *
     * @option root                     Drupal root.
     * @option sites-subdir             Drupal site subdirectory.
     * @option skip-permissions-setup   Drupal skip permissions setup.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function permissionsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
    ])
    {
        $subdirPath = $options['root'] . '/sites/' . $options['sites-subdir'];

        // Define collection of tasks.
        $collection = [
            // Note that the chmod() method takes decimal values.
            $this->taskFilesystemStack()->chmod($subdirPath, \octdec(775), 0000, true),
        ];

        $settingsPath = $subdirPath . '/settings.php';

        if ($this->checkResource($settingsPath, 'file')) {
            // Note that the chmod() method takes decimal values.
            $collection[] = $this->taskFilesystemStack()->chmod($settingsPath, \octdec(664));
        }

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Set runtime configuration values.
     *
     * @param \Symfony\Component\Console\Event\ConsoleCommandEvent $event
     *
     * @hook command-event *
     */
    public function setRuntimeConfig(ConsoleCommandEvent $event): void
    {
        $rootFullPath = \realpath(
            $this->getConfig()->get('drupal.root')
        );

        if (false !== $rootFullPath) {
            $this->getConfig()->set('drupal.root_absolute', $rootFullPath);
        }
    }

    /**
     * Setup Drupal settings overrides.
     *
     * This command will:
     *
     * - Copy "default.settings.php" to "settings.php", which will be overridden if existing
     * - Append to "settings.php" an include operation for a "settings.override.php" file
     * - Write settings specified at "drupal.settings" in "settings.override.php"
     *
     * Default settings can be customized in your local taskman.yml.dist/taskman.yml
     * as shown below:
     *
     * > drupal:
     * >   settings:
     * >     config_directories:
     * >       sync: '../config/sync'
     * >       prod: '../config/prod'
     *
     * The settings override file name can be changed in the Task Runner
     * configuration by setting the "drupal.site.settings_override_file" property.
     *
     * @command drupal:settings-setup
     *
     * @option root                     Drupal root.
     * @option sites-subdir             Drupal site subdirectory.
     * @option settings-override-file   Drupal site settings override filename.
     * @option force                    Drupal force generation of a new settings.php.
     * @option skip-permissions-setup   Drupal skip permissions setup.
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function settingsSetup(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'settings-override-file' => InputOption::VALUE_REQUIRED,
        'force' => false,
        'skip-permissions-setup' => false,
    ])
    {
        $settings_default_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/default.settings.php';
        $settings_path = $options['root'] . '/sites/' . $options['sites-subdir'] . '/settings.php';
        $settings_override_path = $options['root'] .
            '/sites/' . $options['sites-subdir'] .
            '/' . $options['settings-override-file'];

        // Save the filename of the override file in a single variable to use it
        // in the heredoc variable $custom_config hereunder.
        $settings_override_filename = $options['settings-override-file'];

        $custom_config = $this->getDrupal()->getSettingsSetupAddendum($settings_override_filename);

        $collection = [];

        if (true === (bool) $options['force'] || !\file_exists($settings_path)) {
            $collection[] = $this->taskWriteToFile($settings_default_path)->append()->lines([$custom_config]);
            $collection[] = $this->taskFilesystemStack()->copy($settings_default_path, $settings_path, true);
        }

        $arguments = [
            'file' => $settings_override_path,
            'config' => 'drupal.settings',
            'blockStart' => '// Start settings processor block.',
            'blockEnd' => '// End settings processor block.',
        ];

        $collection[] = $this->task(WritePhpTask::class)->setTaskArguments($arguments);

        if (!$options['skip-permissions-setup']) {
            $collection[] = $this->permissionsSetup($options);
        }

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Install target site.
     *
     * This command will install a target Drupal site using configuration values
     * provided in local taskman.yml.dist/taskman.yml files.
     *
     * @command drupal:site-install
     *
     * @option root                   Drupal root.
     * @option site-name              Site name.
     * @option site-mail              Site mail.
     * @option site-profile           Installation profile
     * @option site-update            Whereas to enable the update module or not.
     * @option site-locale            Default site locale.
     * @option account-name           Admin account name.
     * @option account-password       Admin account password.
     * @option account-mail           Admin email.
     * @option database-scheme        Database scheme.
     * @option database-host          Database host.
     * @option database-port          Database port.
     * @option database-name          Database name.
     * @option database-user          Database username.
     * @option database-password      Database password.
     * @option sites-subdir           Sites sub-directory.
     * @option config-dir             Deprecated, use "existing-config" for Drupal 8.6 and higher.
     * @option existing-config        Whether existing config should be imported during installation.
     * @option skip-permissions-setup Whether to skip making the settings file and folder writable during installation.
     *
     * @aliases drupal:si,dsi
     *
     * @param array $options
     *
     * @return \Robo\Collection\CollectionBuilder
     */
    public function siteInstall(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'base-url' => InputOption::VALUE_REQUIRED,
        'site-name' => InputOption::VALUE_REQUIRED,
        'site-mail' => InputOption::VALUE_REQUIRED,
        'site-profile' => InputOption::VALUE_REQUIRED,
        'site-update' => InputOption::VALUE_REQUIRED,
        'site-locale' => InputOption::VALUE_REQUIRED,
        'account-name' => InputOption::VALUE_REQUIRED,
        'account-password' => InputOption::VALUE_REQUIRED,
        'account-mail' => InputOption::VALUE_REQUIRED,
        'database-scheme' => InputOption::VALUE_REQUIRED,
        'database-user' => InputOption::VALUE_REQUIRED,
        'database-password' => InputOption::VALUE_REQUIRED,
        'database-host' => InputOption::VALUE_REQUIRED,
        'database-port' => InputOption::VALUE_REQUIRED,
        'database-name' => InputOption::VALUE_REQUIRED,
        'sites-subdir' => InputOption::VALUE_REQUIRED,
        'config-dir' => InputOption::VALUE_REQUIRED,
        'existing-config' => InputOption::VALUE_OPTIONAL,
        'skip-permissions-setup' => false,
    ])
    {
        if ($options['config-dir']) {
            $this->io()->warning("The 'config-dir' option is deprecated. Use 'existing-config' instead.");
            $options['existing-config'] = true;
        }

        $drush = $this->getConfig()->get('options.bin_dir') . '/drush';

        $dbArray = [
            'scheme' => $options['database-scheme'],
            'user' => $options['database-user'],
            'pass' => $options['database-password'],
            'host' => $options['database-host'],
            'port' => $options['database-password'],
            'path' => $options['database-name'],
        ];
        $dbUrl = \http_build_url($dbArray, $dbArray);

        $task = $this->taskDrushStack($drush)
            ->drupalRootDirectory($options['root'])
            ->siteName($options['site-name'])
            ->siteMail($options['site-mail'])
            ->locale($options['site-locale'])
            ->accountMail($options['account-mail'])
            ->accountName($options['account-name'])
            ->accountPass($options['account-password'])
            ->dbUrl($dbUrl)
            ->sitesSubdir($options['sites-subdir'])
            ->disableUpdateStatusModule()
            ->existingConfig($options['existing-config'])
            ->siteInstall($options['site-profile']);

        // Define collection of tasks.
        $collection = [
            $this->sitePreInstall(),
        ];

        if (!$options['skip-permissions-setup']) {
            $collection[] = $this->permissionsSetup($options);
        }

        $collection[] = $task;
        $collection[] = $this->sitePostInstall();

        return $this->collectionBuilder()->addTaskList($collection);
    }

    /**
     * Run Drupal post-install commands.
     *
     * Commands have to be listed under the "drupal.post_install" property in
     * your local taskman.yml.dist/taskman.yml files, as shown below:
     *
     * > drupal:
     * >   ...
     * >   post_install:
     * >     - "./vendor/bin/drush en views -y"
     * >     - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
     *
     * Post-install commands are automatically executed after installing the site
     * when running "drupal:site-install".
     *
     * @command drupal:site-post-install
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePostInstall()
    {
        $tasks = $this->getConfig()->get('drupal.post_install', []);

        $arguments = [
            'tasks' => $tasks,
        ];

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $this->task(CollectionFactoryTask::class);

        return $collectionFactory->setTaskArguments($arguments);
    }

    /**
     * Run Drupal pre-install commands.
     *
     * Commands have to be listed under the "drupal.pre_install" property in
     * your local taskman.yml.dist/taskman.yml files, as shown below:
     *
     * > drupal:
     * >   ...
     * >   pre_install:
     * >     - { task: "symlink", from: "../libraries", to: "${drupal.root}/libraries" }
     * >     - { task: "process", source: "behat.yml.dist", destination: "behat.yml" }
     *
     * Pre-install commands are automatically executed before installing the site
     * when running "drupal:site-install".
     *
     * @command drupal:site-pre-install
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function sitePreInstall()
    {
        $tasks = $this->getConfig()->get('drupal.pre_install', []);

        $arguments = [
            'tasks' => $tasks,
        ];

        /** @var CollectionFactory $collectionFactory */
        $collectionFactory = $this->task(CollectionFactoryTask::class);

        return $collectionFactory->setTaskArguments($arguments);
    }

    /**
     * @hook validate drupal:site-install
     *
     * @param CommandData $commandData
     *
     * @throws \Exception
     */
    public function validateSiteInstall(CommandData $commandData): void
    {
        $input = $commandData->input();

        // Validate if permissions will be set up.
        if (!$input->getOption('skip-permissions-setup')) {
            return;
        }

        $siteDirectory = \implode('/', [
            \getcwd(),
            $input->getOption('root'),
            'sites',
            $input->getOption('sites-subdir'),
        ]);

        // Check if required files/folders exist and they are writable.
        $requiredFiles = [$siteDirectory, $siteDirectory . '/settings.php'];

        foreach ($requiredFiles as $requiredFile) {
            if (\file_exists($requiredFile) && !\is_writable($requiredFile)) {
                throw new \Exception(
                    \sprintf('The file/folder %s must be writable for installation to continue.', $requiredFile)
                );
            }
        }
    }
}
