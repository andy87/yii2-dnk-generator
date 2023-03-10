<?php

namespace frontend\components\controllers;

use Yii;
use ReflectionException;
use yii\web\Response;
use yii\web\ErrorAction;
use yii\data\ActiveDataProvider;
use {{BaseControllerClassName}} as BaseControllerClass;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;
use andy87\dnk\source\resources\GridViewResource;
use andy87\dnk\source\base\BaseService;
use andy87\dnk\source\interfaces\controllers\frontend\ControllerFrontendInterface;

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



    // Methods

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
        $R = $this->service->getResource(BaseService::LIST, [
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