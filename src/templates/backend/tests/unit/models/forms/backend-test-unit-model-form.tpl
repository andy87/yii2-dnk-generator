<?php

namespace backend\tests\unit\models\forms;

use Codeception\Test\Unit;
use Codeception\Actor;
use backend\models\forms\{{CamelCase}}Form;

/**
 * {{CamelCase}}Form test
 */
class {{CamelCase}}Test extends Unit
{
    /**
     * @var Actor Actor текущего тестового набора.
     */
    protected Actor $tester;

    /*public function _fixtures()
    {
        return [
            '{{snake_case}}' => [
                'class' => {{CamelCase}}FormFixture::class,
                'dataFile' => '{{snake_case}}.php'
            ]
        ];
    }*/

    /**
     * @return void
     */
    public function testOne(): void
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
