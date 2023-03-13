<?php

namespace andy87\dnk\source\base;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\base\Component;
use andy87\dnk\source\interfaces\resources\ResourcesInterface;

/**
 *  Базовый клас для ресурсов страниц, экшенов, компонентов.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими ресурсами
 */
abstract class BaseResource extends Component implements ResourcesInterface
{
    // константы
    /** @var string Имя шаблона */
    public const TEMPLATE = 'index';

    /** @var ?int ID CRUD экшена в котором используется ресурс */
    public const ACTION = null;

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
     * @param Controller $controller
     * @param ?string $template
     * @param array $params
     * @return string
     */
    public function render(Controller $controller, ?string $template = null, array $params = []): string
    {
        $template = ($template) ? $template : static::TEMPLATE;
        $params = array_merge( $this->release(), $params );

        return $controller->render($template, $params);
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