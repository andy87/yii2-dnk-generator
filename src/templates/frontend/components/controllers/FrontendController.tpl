<?php

namespace frontend\components\controllers;

use ReflectionException;
use yii\web\Response;
use yii\web\ErrorAction;
use yii\data\ActiveDataProvider;
use common\components\core\BaseService;
use common\components\resourc\GridViewResource;
use common\components\resourc\crud\ListResource;
use common\components\resourc\crud\ReadResource;
use {{BaseControllerClassName}} as BaseControllerClass;
use andy87\dnk\common\components\interfaces\controllers\frontend\ControllerFrontendInterface;

/**
 * Base Controller for environment `frontend`
 */
abstract class FrontendController extends BaseControllerClass implements ControllerFrontendInterface
{
    // константы

    /** @var string className Сервиса */
    public const SERVICE = BaseService::class;

    /**
     * @return array
     */
    public function actions(): array
    {
            return [
                'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }



    // методы

    /**
     *  action `List`
     *
     * @return ListResource|string
     * @throws ReflectionException
     */
    public function actionList(): ListResource|string
    {
        $form = $this->service->getForm();

        /** @var ListResource $R */
        $R = new $this->service->getResource(BaseService::LIST, [
            'gridViewResource' => new GridViewResource($form, new ActiveDataProvider())
        ]);

        if ( $this->isJsonResponse() ) return $R;

        return $R->render();
    }


    /**
     *  action `Read`
     *
     * @param int $id
     * @return ReadResource|string
     * @throws ReflectionException
     */
    public function actionRead(int $id): ReadResource|string
    {
        /** @var ReadResource $R */
        $R = $this->service->getResource(BaseService::READ, [
            'item' => $this->service->findWhere(['id' => $id])->one()
        ]);

        if ( $this->isJsonResponse() ) return $R;

        return $R->render();
    }

    /**
     * @return bool
     */
    public function isJsonResponse(): bool
    {
        if ($this->request->get('type') === 'json')
        {
            $this->response = Response::FORMAT_JSON;

            return true;
        }

        return false;
    }
}