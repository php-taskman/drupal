<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Plugin\Task;

use Robo\Common\BuilderAwareTrait;
use Robo\Contract\BuilderAwareInterface;
use Robo\Task\File\Write;

/**
 * Class PrependConfiguration.
 */
class PrependPhpTask extends BasePhpTask implements BuilderAwareInterface
{
    use BuilderAwareTrait;
    public const ARGUMENTS = [
        'file',
        'config',
        'blockEnd',
        'blockStart',
    ];

    public const NAME = 'prepend.php';

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

        $text = "<?php\n" . $this->getConfigurationBlock() . $this->sanitizeContent($writeTask->originalContents());

        return $this
            ->collectionBuilder()
            ->addTaskList([
                $writeTask->text($text),
            ])
            ->run();
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
