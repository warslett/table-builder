<?php

declare(strict_types=1);

namespace WArslett\TableBuilder\Tests\Renderer\Html;

use WArslett\TableBuilder\Renderer\Html\TwigRenderer;
use WArslett\TableBuilder\Tests\TestCase;
use Mockery as m;
use Mockery\Mock;
use Twig;
use WArslett\TableBuilder\RouteGeneratorAdapter\RouteGeneratorAdapterInterface;
use WArslett\TableBuilder\Table;
use WArslett\TableBuilder\TableCell;

class TwigRendererTest extends TestCase
{

    public function testRenderRouteCallsRouteGeneratorAdapter()
    {
        $routeGeneratorAdapter = $this->mockRouteGeneratorAdaptor('/foo/bar');
        $renderer = new TwigRenderer($this->mockTwigEnvironment(), $routeGeneratorAdapter);

        $renderer->renderTableRoute('foo', ['bar']);

        $routeGeneratorAdapter->shouldHaveReceived('renderRoute')->with('foo', ['bar'])->once();
    }

    public function testRenderCellValueCastsToSting()
    {
        $renderer = new TwigRenderer($this->mockTwigEnvironment(), $this->mockRouteGeneratorAdaptor('/foo/bar'));

        $result = $renderer->renderTableCellValue($this->mockTable(), $this->mockTableCell(5));

        $this->assertSame('5', $result);
    }

    /**
     * @param string $url
     * @return RouteGeneratorAdapterInterface&Mock
     */
    private function mockRouteGeneratorAdaptor(string $url): RouteGeneratorAdapterInterface
    {
        $routeGeneratorAdapter = m::mock(RouteGeneratorAdapterInterface::class);
        $routeGeneratorAdapter->shouldReceive('renderRoute')->andReturn($url);
        return $routeGeneratorAdapter;
    }

    /**
     * @return Table&Mock
     */
    private function mockTable(): Table
    {
        return m::mock(Table::class);
    }

    /**
     * @param mixed $value
     * @param string $renderingType
     * @return TableCell
     */
    private function mockTableCell($value, string $renderingType = ''): TableCell
    {
        $cell = m::mock(TableCell::class);
        $cell->shouldReceive('getValue')->andReturn($value);
        $cell->shouldReceive('getRenderingType')->andReturn($renderingType);
        return $cell;
    }

    /**
     * @return Twig\Environment&Mock
     */
    private function mockTwigEnvironment(): Twig\Environment
    {
        return m::mock(Twig\Environment::class);
    }
}
