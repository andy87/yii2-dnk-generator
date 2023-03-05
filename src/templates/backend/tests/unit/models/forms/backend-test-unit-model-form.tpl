<?php

namespace backend\tests\unit\models\items;

use Yii;
use backend\models\forms\{{CamelCase}}Form;

/**
 * {{CamelCase}}Form test
 */
class {{CamelCase}}Test extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;


    /**
     * @return array
     */
    /*public function _fixtures()
    {
        return [
            '{{snake_case}}' => [
                'class' => {{CamelCase}}FormFixture::class,
                'dataFile' => '{{snake_case}}.php'
            ]
        ];
    }*/

    public function testOne()
    {
        $model = new {{CamelCase}}Form([
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
