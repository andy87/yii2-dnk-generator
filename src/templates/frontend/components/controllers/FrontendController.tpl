<?php

namespace frontend\components\controllers;

use common\components\core\BaseService;
use common\components\resources\GridViewResource;
use {{BaseControllerClassName}} as BaseControllerClass;

/**
 * Base Controller for environment `frontend`
 */
abstract class FrontendController extends BaseControllerClass
{
    // const

    /** @var string className Сервиса */
    public const SERVICE = BaseService::class;



    // methods

    /**
     *  action `List`
     */
    public function actionList()
    {
        $form = $this->service->getForm();

        $activeDataProvider = new ActiveDataProvider();

        $gridViewResource = new GridViewResource($form, $activeDataProvider);

        /** @var {{CamelCase}}ListResource $R */
        $R = new $this->service->getResource(BaseService::LIST)($gridViewResource);

        return $R->render();
    }


    /**
    *  action `Read`
    */
    public function actionRead(int $id)
    {
        $item = $this->service->findWhere(['id' => $id])->one();

        /** @var {{CamelCase}}ReadResource $R */
        $R = $this->service->getResource(BaseService::READ)($item);

        return $R->render();
    }
}