<?php

namespace frontend\components\controllers;

use yii\web\Response;
use yii\web\ErrorAction;
use common\components\core\BaseService;
use common\components\resources\GridViewResource;
use {{BaseControllerClassName}} as BaseControllerClass;
use common\components\interfaces\controllers\frontend\ControllerFrontendInterface;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;

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
     */
    public function actionList()
    {
        $form = $this->service->getForm();

        $activeDataProvider = new ActiveDataProvider();

        $gridViewResource = new GridViewResource($form, $activeDataProvider);

        /** @var ListResource $R */
        $R = new $this->service->getResource(BaseService::LIST, [
            'gridViewResource' => $gridViewResource
        ]);

        return $R->render();
    }


    /**
    *  action `Read`
    */
    public function actionRead(int $id)
    {
        /** @var ReadResource $R */
        $R = $this->service->getResource(BaseService::READ, [
            'item' => $this->service->findWhere(['id' => $id])->one()
        ]);

        return $R->render();
    }

    /**
     * @return bool
     */
    public function isJsonResponse(): bool
    {
        if ($this->request->get('type') === 'json')
        {
            Yii::$app->response = Response::FORMAT_JSON;

            return true;
        }

        return false;
    }
}