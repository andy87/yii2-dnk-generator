<?php

namespace common\components\core;

use Yii;
use yii\web\Controller;
use yii\base\InvalidConfigException;
use common\components\interfaces\ServiceInterface;
use common\components\interfaces\ControllerInterface;

/**
 *
 */
abstract class BaseController extends Controller implements  ControllerInterface
{
    /** @var string  */
    public const SERVICE = ServiceInterface::class;



    /** @var ServiceInterface  */
    protected ServiceInterface $service;



    /**
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->setupService();
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    private function setupService(): void
    {
        /** @var ServiceInterface $service */
        $service = Yii::createObject(static::SERVICE);

        $this->service = $service;
    }
}