<?php

namespace PhpTaskman\Drupal\Robo\Plugin\Commands;

use Symfony\Component\Console\Input\InputOption;

/**
 * Class DrupalCommands.
 */
class DrupalCommands extends AbstractDrupalCommands
{
    /**
     * Run PHP server.
     *
     * Run a PHP server using default configuration value.
     *
     * @command drupal:run-server
     *
     * @option root         Drupal site root.
     * @option base-url     Drupal site base URL
     * @option background   Run in background.
     *
     * @aliases drupal:rs
     *
     * @param array $options
     *
     * @return \Robo\Contract\TaskInterface
     */
    public function runServer(array $options = [
        'root' => InputOption::VALUE_REQUIRED,
        'base-url' => InputOption::VALUE_REQUIRED,
        'background' => InputOption::VALUE_NONE,
    ])
    {
        $host = \parse_url($options['base-url'], PHP_URL_HOST);
        $port = \parse_url($options['base-url'], PHP_URL_PORT);
        $port = $port === null ? 80 : $port;

        return $this->taskServer($port)
            ->rawArg('-dalways_populate_raw_post_data=-1')
            ->host($host)
            ->dir($options['root'])
            ->background($options['background']);
    }
}
