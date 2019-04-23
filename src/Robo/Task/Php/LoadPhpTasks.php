<?php

namespace PhpTaskman\Drupal\Robo\Task\Php;

use Consolidation\Config\ConfigInterface;
use Robo\Robo;

/**
 * Class LoadPhpTasks.
 */
trait LoadPhpTasks
{
    /**
     * Append Robo YAML configuration to given PHP file as a PHP array.
     *
     * @param string $filename
     *   File path to append Robo configuration to.
     * @param \Consolidation\Config\ConfigInterface $config
     *   Robo configuration.
     *
     * @return \PhpTaskman\Drupal\Robo\Task\Php\AppendConfiguration
     *   Append configuration task.
     */
    protected function taskAppendConfiguration($filename, ConfigInterface $config = null)
    {
        $config = $config ? $config : Robo::config();

        return $this->task(AppendConfiguration::class, $filename, $config);
    }

    /**
     * Prepend Robo YAML configuration to given PHP file as a PHP array.
     *
     * @param string $filename
     *   File path to prepend Robo configuration to.
     * @param \Consolidation\Config\ConfigInterface $config
     *   Robo configuration.
     *
     * @return \PhpTaskman\Drupal\Robo\Task\Php\AppendConfiguration
     *   Append configuration task.
     */
    protected function taskPrependConfiguration($filename, ConfigInterface $config = null)
    {
        $config = $config ? $config : Robo::config();

        return $this->task(PrependConfiguration::class, $filename, $config);
    }

    /**
     * Prepend Robo YAML configuration to given PHP file as a PHP array.
     *
     * @param string $filename
     *   Destination file path.
     * @param \Consolidation\Config\ConfigInterface $config
     *   Robo configuration.
     *
     * @return \PhpTaskman\Drupal\Robo\Task\Php\AppendConfiguration
     *   Append configuration task.
     */
    protected function taskWriteConfiguration($filename, ConfigInterface $config = null)
    {
        $config = $config ? $config : Robo::config();

        return $this->task(WriteConfiguration::class, $filename, $config);
    }
}
