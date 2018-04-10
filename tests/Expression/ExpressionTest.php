<?php declare(strict_types=1);

namespace DI\ExpressionParser\Tests\Expression;

use DI\ExpressionParser\Expression;
use PHPUnit\Framework\TestCase;

class ExpressionTest extends TestCase
{
    /**
     * @dataProvider additionProvider
     *
     * @param Expression $expression
     * @param            $params
     * @param            $expected
     */
    public function testAllExpressions(Expression $expression, $params, $expected)
    {
        $expression->setMappings($params);
        $this->assertEquals($expected, $expression->value());
    }

    public function additionProvider()
    {
        return (include __DIR__ .'/../Providers/ExpressionDataProvider.php');
    }
}