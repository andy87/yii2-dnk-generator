<?php

namespace frontend\tests\unit\services;

use Yii;
use yii\base\InvalidConfigException;
use common\tests\UnitTester;
use frontend\services\{{CamelCase}}Service;

/**
 * {{CamelCase}}Service test
 */
class {{CamelCase}}ServiceTest extends \Codeception\Test\Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function testOne()
    {
        /** @var {{CamelCase}}Servcie $service */
        $service = Yii::createObject({{CamelCase}}Servcie::class);

        verify($service->method())->false();
        verify($service->property)->true();
        //verify($model->property)->instanceOf('yii\mail\MessageInterface');
        //verify($model->property->getTo())->arrayHasKey('admin@example.com');
        //verify($model->property->getSubject())->equals('very important letter subject');
        //verify($model->property->toString())->stringContainsString('body of current message');
    }
}
