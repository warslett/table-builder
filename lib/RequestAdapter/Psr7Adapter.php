<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\RequestAdapter;

use Psr\Http\Message\RequestInterface;

final class Psr7Adapter implements RequestAdapterInterface
{
    private RequestInterface $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        parse_str($this->request->getUri()->getQuery(), $params);
        return $params;
    }

    /**
     * @param RequestInterface $request
     * @return self
     */
    public static function withRequest(RequestInterface $request): self
    {
        return new self($request);
    }
}
