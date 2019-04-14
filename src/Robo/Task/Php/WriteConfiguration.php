<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Robo\Task\Php;

/**
 * Class WriteConfiguration.
 */
class WriteConfiguration extends BaseConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        return "<?php\n" . $this->getConfigurationBlock();
    }
}
