<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\RequestAdapter;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use WArslett\TableBuilder\RequestAdapter\SymfonyHttpAdapter;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class SymfonyAdapterTest extends TestCase
{
    /**
     * @return void
     */
    public function testGetParametersReturnsRequestParameters(): void
    {
        $parameters = ['foo' => 'bar'];
        $request = $this->mockRequest($parameters);
        $adapter = SymfonyHttpAdapter::withRequest($request);

        $actual = $adapter->getParameters();

        $this->assertSame($parameters, $actual);
    }

    /**
     * @param array $params
     * @return Request&Mock
     */
    private function mockRequest(array $params): Request
    {
        $request = m::mock(Request::class);
        $request->query = new InputBag($params);
        return $request;
    }
}
