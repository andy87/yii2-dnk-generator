<?php

namespace common\components\core;

use Yii;
use yii\web\Response;
use yii\base\Component;
use common\components\interfaces\resources\ResourcesInterface;

/**
 *  Базовый клас для ресурсов страниц, экшенов, компонентов.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими ресурсами
 */
abstract class BaseResource extends Component implements ResourcesInterface
{
    // константы

    /** @var string Имя шаблона */
    public const TEMPLATE = 'index';

    /** @var string Переменная которая будет содержать ресурсы в шаблоне при вызове release() */
    public const KEY = 'R';



    // методы

    /**
     * Получение массива для реализации
     *
     * @return self[]
     */
    public function release(): array
    {
        return [ self::KEY => $this ];
    }

    /**
     * @param ?string $template
     * @param array $params
     * @return string
     */
    public function render(?string $template = null, array $params = []): string
    {
        $template = $template || static::TEMPLATE;
        $params = array_merge( $this->release(), $params );

        return Yii::$app->view->render($template, $params);
    }

    /**
     * @param array $params
     * @return array
     */
    public function json(array $params = []): array
    {
        $params = array_merge( (array) $this, $params );

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $params;
    }

}