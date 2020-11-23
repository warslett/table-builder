<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Twig;

use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

final class StandardTemplatesLoader extends FilesystemLoader implements LoaderInterface
{

    public function __construct()
    {
        $twigDir = __DIR__ . '/../../templates/twig';
        parent::__construct([$twigDir]);
    }
}
