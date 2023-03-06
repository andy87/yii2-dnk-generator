<?php

namespace andy87\dnk\source\tests\unit;

use ReflectionException;
use Codeception\Test\Unit;
use andy87\dnk\source\base\BaseService as BaseServiceAlias;
use andy87\dnk\source\tests\unit\services\BaseService;

/**
 * Tests
 */
class BaseServiceTest extends Unit
{
    /**
     * @return void
     * @throws ReflectionException
     */
    public function testSendEmail(): void
    {
        $service = new BaseService();

        verify($service->getClassModel())->equals('yii\base\Model');
        verify($service->getClassForm())->equals('yii\base\Model');

        verify($service->getModel())->instanceOf('yii\base\Model');
        verify($service->getForm())->instanceOf('yii\base\Model');

        verify($service->getResource(BaseServiceAlias::LIST))->null();

        verify($service->createClass($service->getClassModel()))->instanceOf('yii\base\Model');

        verify($service->getEntity())->instanceOf('yii\base\Model');

        $model = $service->create();

        verify($model)->instanceOf('yii\base\Model');
        verify($service->update($model))->instanceOf('yii\base\Model');

        verify($service->findWhere([]))->instanceOf('yii\db\ActiveQuery');
    }
}
