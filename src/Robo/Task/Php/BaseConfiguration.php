<?php

namespace PhpTaskman\Drupal\Robo\Task\Php;

use Robo\Config\Config;
use Robo\Exception\TaskException;
use Robo\Task\File\Write;

/**
 * Class BaseConfiguration.
 */
abstract class BaseConfiguration extends Write implements BaseConfigurationInterface
{
    /**
     * Comment ending settings processor block.
     *
     * @var string
     */
    protected $blockEnd = '// End settings processor block.';

    /**
     * Comment starting settings processor block.
     *
     * @var string
     */
    protected $blockStart = '// Start settings processor block.';

    /**
     * Root key in YAML configuration file.
     *
     * @var string
     */
    protected $configKey = 'settings';

    /**
     * Robo configuration object.
     *
     * @var \Robo\Config\Config
     */
    protected $configObject;

    /**
     * BaseConfiguration constructor.
     *
     * @param mixed $filename
     * @param Config $config
     */
    public function __construct($filename, Config $config)
    {
        parent::__construct($filename);
        $this->configObject = $config;
    }

    /**
     * Set BlockEnd property.
     *
     * @param mixed $block_end
     *   Property value.
     *
     * @return $this
     */
    public function setBlockEnd($block_end)
    {
        $this->blockEnd = $block_end;

        return $this;
    }

    /**
     * Set BlockStart property.
     *
     * @param mixed $block_start
     *   Property value.
     *
     * @return $this
     */
    public function setBlockStart($block_start)
    {
        $this->blockStart = $block_start;

        return $this;
    }

    /**
     * Set ConfigKey property.
     *
     * @param mixed $config_key
     *   Property value.
     *
     * @return $this
     */
    public function setConfigKey($config_key)
    {
        $this->configKey = $config_key;

        return $this;
    }

    /**
     * Get configuration block.
     *
     * @throws \Robo\Exception\TaskException
     *    Thrown when configuration key does not exists.
     */
    protected function getConfigurationBlock()
    {
        $line[] = '';
        $line[] = $this->blockStart;
        $line[] = '';

        if (!$this->configObject->has($this->configKey)) {
            throw new TaskException(
                $this,
                'Configuration key ' . $this->configKey . ' not found on current Robo configuration.'
            );
        }

        foreach ($this->configObject->get($this->configKey) as $variable => $values) {
            foreach ($values as $name => $value) {
                $line[] = $this->getStatement($variable, $name, $value);
            }
            $line[] = '';
        }

        $line[] = $this->blockEnd;
        $line[] = '';

        return \implode("\n", $line);
    }

    /**
     * {@inheritdoc}
     */
    protected function getContentsToWrite()
    {
        $content = $this->originalContents();

        return $this->process($content);
    }

    /**
     * Get variable assignment statement.
     *
     * @param string $variable
     *   Variable name.
     * @param string $name
     *   Setting name.
     * @param mixed $value
     *   Setting value.
     *
     * @return string
     *   Full statement.
     */
    protected function getStatement($variable, $name, $value)
    {
        $output = \var_export($value, true);

        if (\is_array($value)) {
            $output = \str_replace(
                [' ', "\n", '=>', ',)', '),'],
                ['', '', ' => ', ')', '), '],
                $output
            );
        }

        return \sprintf("$%s['%s'] = %s;", $variable, $name, $output);
    }

    /**
     * Remove settings block from given content.
     *
     * @param string $content
     *   Content of a PHP file.
     *
     * @return string
     *   Content without setting block.
     */
    protected function sanitizeContent($content)
    {
        $regex = '/^\n' . \preg_quote($this->blockStart, '/') . '.*?' . \preg_quote($this->blockEnd, '/') . '\n/sm';

        return \preg_replace($regex, '', $content);
    }
}
