<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Plugin\Task;

use PhpTaskman\Core\Contract\TaskInterface;
use PhpTaskman\Core\Plugin\BaseTask;
use Robo\Exception\TaskException;

/**
 * Class BaseConfiguration.
 */
abstract class BasePhpTask extends BaseTask implements TaskInterface
{
    /**
     * Get configuration block.
     *
     * @throws \Robo\Exception\TaskException
     *    Thrown when configuration key does not exists.
     */
    protected function getConfigurationBlock()
    {
        $arguments = $this->getTaskArguments();

        $blockStart = $arguments['blockStart'];
        $blockEnd = $arguments['blockEnd'];
        $config = $arguments['config'];

        if (!$this->getConfig()->has($config)) {
            throw new TaskException(
                $this,
                'Configuration key ' . $config . ' not found on current configuration.'
            );
        }

        $line = [
            'start' => $blockStart,
            '',
        ];

        foreach ($this->getConfig()->get($config) as $variable => $values) {
            foreach ($values as $name => $value) {
                $line[] = $this->getStatement($variable, $name, $value);
            }
            $line[] = '';
        }

        $line['stop'] = $blockEnd;

        $textChecksum = \sha1(\json_encode($line));

        $line['start'] .= '(' . $textChecksum . ')';
        $line['stop'] .= '(' . $textChecksum . ')';

        return \implode("\n", $line);
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
        $blockStart = $this->getTaskArguments()['blockStart'];
        $blockEnd = $this->getTaskArguments()['blockEnd'];

        $regex = '/^\n' . \preg_quote($blockStart, '/') . '.*?' . \preg_quote($blockEnd, '/') . '\n/sm';

        return \preg_replace($regex, '', $content);
    }
}
