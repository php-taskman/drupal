<?php

namespace PhpTaskman\Drupal\Robo\Task\Php;

/**
 * Class AppendConfiguration.
 */
class AppendConfiguration extends BaseConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        return $this->sanitizeContent($content) . $this->getConfigurationBlock();
    }
}
