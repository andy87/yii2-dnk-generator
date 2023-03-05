<?php

namespace frontend\tests\unit\models\items;

use Yii;
use yii\base\InvalidConfigException;
use Codeception\Test\Unit;
use common\tests\UnitTester;
use frontend\models\items\{{CamelCase}};

/**
 * {{CamelCase}} test
 */
class {{CamelCase}}Test extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;


    /**
     * @return array
     */
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
     * @throws InvalidConfigException
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
