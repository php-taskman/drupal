<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Robo\Task\Php;

/**
 * Class PrependConfiguration.
 */
class PrependConfiguration extends BaseConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function process($content)
    {
        $content = $this->sanitizeContent($content);

        return "<?php\n" . $this->getConfigurationBlock() . $content;
    }

    /**
     * {@inheritdoc}
     */
    protected function sanitizeContent($content)
    {
        $content = parent::sanitizeContent($content);

        return \preg_replace('/^<\?(php)?/', '', $content);
    }
}
