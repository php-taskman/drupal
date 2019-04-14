<?php

declare(strict_types = 1);

namespace PhpTaskman\Drupal\Contract;

/**
 * Interface FilesystemAwareTrait.
 */
interface FilesystemAwareInterface
{
    /**
     * @return \Symfony\Component\Filesystem\Filesystem
     */
    public function getFilesystem();

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     *
     * @return $this
     */
    public function setFilesystem($filesystem);
}
