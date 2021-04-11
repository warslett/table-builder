<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

use Symfony\Component\HttpFoundation\Request;

final class SymfonyHttpAdapter implements RequestAdapterInterface
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->request->query->all();
    }

    /**
     * @param Request $request
     * @return self
     */
    public static function withRequest(Request $request): self
    {
        return new self($request);
    }
}
