<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Robo\Task\Php;

/**
 * Interface BaseConfigurationInterface.
 */
interface BaseConfigurationInterface
{
    /**
     * Process settings file.
     *
     * @param string $content
     *   Content of a PHP file.
     *
     * @return string
     *   Processed setting file.
     */
    public function process($content);
}
