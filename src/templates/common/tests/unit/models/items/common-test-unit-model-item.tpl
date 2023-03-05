<?php

namespace common\tests\unit\models\items;

use Codeception\Test\Unit;
use common\tests\UnitTester;
use common\models\items\{{CamelCase}};

/**
 * {{CamelCase}} test
 */
class {{CamelCase}}Test extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /*public function _fixtures()
    {
        return [
            '{{snake_case}}' => [
                'class' => {{CamelCase}}Fixture::class,
                'dataFile' => '{{snake_case}}.php'
            ]
        ];
    }*/

    /**
     * @return void
     */
    public function testOne(): void
    {
        $model = new {{CamelCase}}([
            'property_1' => 'value',
            'property_2' => false,
        ]);

        verify($model->method())->false();
        verify($model->property)->true();
        //verify($model->property)->instanceOf('yii\mail\MessageInterface');
        //verify($model->property->getTo())->arrayHasKey('admin@example.com');
        //verify($model->property->getSubject())->equals('very important letter subject');
        //verify($model->property->toString())->stringContainsString('body of current message');
    }
}
