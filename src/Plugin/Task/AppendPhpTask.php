<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Plugin\Task;

use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Task\File\Write;

/**
 * Class AppendConfiguration.
 */
class AppendPhpTask extends BasePhpTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;
    public const ARGUMENTS = [
        'file',
        'config',
        'blockEnd',
        'blockStart',
    ];

    public const NAME = 'append.php';

    /**
     * @throws \Robo\Exception\TaskException
     *
     * @return \Robo\Result
     */
    public function run()
    {
        $arguments = $this->getTaskArguments();

        /** @var \Robo\Task\File\Write $writeTask */
        $writeTask = $this->task(Write::class, $arguments['file']);

        $text = $this->sanitizeContent($writeTask->originalContents()) . $this->getConfigurationBlock();

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $writeTask->text($text),
            ])
            ->run();
    }
}
