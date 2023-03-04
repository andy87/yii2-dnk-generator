<?php

namespace common\components\core;

use Yii;
use yii\web\Controller;
use yii\base\InvalidConfigException;
use common\components\interfaces\ServiceInterface;
use common\components\interfaces\ControllerInterface;

/**
 *  Базовый клас для контроллеров.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими контроллерам
 */
abstract class BaseController extends Controller implements  ControllerInterface
{
    // Константы, Свойства, Методы наследуемые всеми рабочими контроллерами

    // константы

    /** @var string Имя класса сервиса */
    public const SERVICE = ServiceInterface::class;



    // свойства

    /** @var ServiceInterface Экземпляр класса сервиса */
    protected ServiceInterface $service;



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
        /** @var ServiceInterface $service */
        $service = Yii::createObject(static::SERVICE);

        $this->service = $service;
    }
}