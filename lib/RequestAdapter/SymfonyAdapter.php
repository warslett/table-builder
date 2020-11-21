<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

use Symfony\Component\HttpFoundation\Request;

final class SymfonyAdapter implements RequestAdapterInterface
{
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param $request
     * @return static
     */
    public static function withRequest(Request $request): self
    {
        return new self($request);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getParameter(string $name)
    {
        return $this->request->get($name);
    }
}
