<?php

namespace backend\components\controllers;

use common\components\core\BaseService;
use common\components\resources\GridViewResource;
use {{BaseControllerClassName}} as BaseControllerClass;

/**
 * Base Controller for environment `backend`
 */
abstract class BackendController extends BaseControllerClass
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


    /**
    *  action `Create`
    */
    public function actionCreate(int $id)
    {
        $formClass = $this->service->getClassForm();

        $item = $formClass::findOne($id);

        /** @var {{CamelCase}}CreateResource $R */
        $R = $this->service->getResource(BaseService::CREATE)($item);

        if ( $this->request->isPost )
        {
            $R->item->load(Yii::$app->request->post());

            $this->service->create($R->item);
        }

        return $R->render();
    }


    /**
    *  action `Update`
    */
    public function actionUpdate(int $id)
    {
        $formClass = $this->service->getClassForm();

        $item = $formClass::findOne($id);

        /** @var {{CamelCase}}UpdateResource $R */
        $R = $this->service->getResource(BaseService::UPDATE)($item);

        if ( $this->request->isPost )
        {
            $R->item->load(Yii::$app->request->post());

            $this->service->update($R->item);
        }

        return $R->render();
    }

}