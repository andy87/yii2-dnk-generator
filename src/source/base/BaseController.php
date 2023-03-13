<?php

namespace andy87\dnk\source\base;

use Yii;
use yii\web\Controller;
use yii\base\InvalidConfigException;
use andy87\dnk\source\interfaces\services\common\ServiceCommonInterface;
use andy87\dnk\source\interfaces\controllers\common\ControllerCommonInterface;

/**
 *  Базовый клас для контроллеров.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими контроллерам
 */
abstract class BaseController extends Controller implements  ControllerCommonInterface
{
    // константы

    /** @var string Имя класса сервиса */
    public const SERVICE = ServiceCommonInterface::class;



    // свойства

    /** @var ServiceCommonInterface Экземпляр класса сервиса */
    protected ServiceCommonInterface $service;



    // методы

    /**
     * Инициализация
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->setupService();
    }

    /**
     * Установка сервиса
     *
     * @return void
     * @throws InvalidConfigException
     */
    private function setupService(): void
    {
        /** @var ServiceCommonInterface $service */
        $service = Yii::createObject(static::SERVICE);

        $this->service = $service;
    }
}