<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\RouteGeneratorAdapter;

use Symfony\Component\Routing\RouterInterface;
use WArslett\TableBuilder\RouteGeneratorAdapter\SymfonyRoutingAdapter;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;

class SymfonyRoutingAdapterTest extends TestCase
{
    public function testRenderRouteGeneratesRoute(): void
    {
        $router = $this->mockRouter();
        $adapter = new SymfonyRoutingAdapter($router);
        $route = 'my_route';
        $params = ['foo' => 'bar'];

        $adapter->renderRoute($route, $params);

        $router->shouldHaveReceived('generate')->with($route, $params);
    }

    public function testRenderRouteReturnsGeneratedRoute(): void
    {
        $url = '/foo/bar';
        $adapter = new SymfonyRoutingAdapter($this->mockRouter($url));

        $result = $adapter->renderRoute('my_route', ['foo' => 'bar']);

        $this->assertSame($url, $result);
    }

    /**
     * @param string $url
     * @return RouterInterface&Mock
     */
    private function mockRouter(string $url = ''): RouterInterface
    {
        $router = m::mock(RouterInterface::class);
        $router->shouldReceive('generate')->andReturn($url);
        return $router;
    }
}
