<?php

namespace common\components\core;

use Yii;
use yii\base\Component;
use common\components\interfaces\ResourcesInterface;
use yii\web\Response;
use yii\web\View;

/**
 * Базовый клас ресурсов страницы/экшена/блока(html)
 */
abstract class BaseResource extends Component implements ResourcesInterface
{
    // const

    /** @var string template filename */
    public const TEMPLATE = 'index';

    /** @var string Переменная которая будет содержать ресурсы в шаблоне при вызове release() */
    public const KEY = 'R';



    // methods

    /**
     * @param ?string $template
     * @param array $params
     * @return string
     */
    public function render( ?string $template = null, array $params = [] ): string
    {
        $template = $template || static::TEMPLATE;
        $params = array_merge( $this->release(), $params );

        return Yii::$app->view->render($template, $params);
    }

    /**
     * @param array $params
     * @return array
     */
    public function json(array $params = [] ): array
    {
        $params = array_merge( (array) $this, $params );

        Yii::$app->response->format = Response::FORMAT_JSON;

        return $params;
    }

    /**
     * @return self[]
     */
    public function release(): array
    {
        return [ self::KEY => $this ];
    }
}