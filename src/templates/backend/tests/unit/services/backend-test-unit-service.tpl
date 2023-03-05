<?php

namespace backend\tests\unit\services;

use Yii;
use yii\base\InvalidConfigException;
use Codeception\Test\Unit;
use common\tests\UnitTester;
use backend\services\{{CamelCase}}Service;

/**
 * {{CamelCase}}Service test
 */
class {{CamelCase}}ServiceTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected UnitTester $tester;

    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function testOne(): void
    {
        /** @var {{CamelCase}}Service $service */
        $service = Yii::createObject({{CamelCase}}Service::class);

        verify($service->method())->false();
        verify($service->property)->true();
        //verify($model->property)->instanceOf('yii\mail\MessageInterface');
        //verify($model->property->getTo())->arrayHasKey('admin@example.com');
        //verify($model->property->getSubject())->equals('very important letter subject');
        //verify($model->property->toString())->stringContainsString('body of current message');
    }
}
