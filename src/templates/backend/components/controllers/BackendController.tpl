<?php

namespace backend\components\controllers;

use yii\data\ActiveDataProvider;
use common\components\core\BaseService;
use common\components\resources\GridViewResource;
use {{BaseControllerClassName}} as BaseControllerClass;
use common\components\interfaces\controllers\backend\ControllerBackendInterface;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\CreateResource;
use common\components\resources\crud\UpdateResource;
use common\components\resources\crud\ReadResource;

/**
 * Base Controller for environment `backend`
 */
abstract class BackendController extends BaseControllerClass implements ControllerBackendInterface
{
    // константы

    /** @var string className Сервиса */
    public const SERVICE = BaseService::class;



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
    *  action `Create`
    */
    public function actionCreate(int $id)
    {
        $formClass = $this->service->getClassForm();

        /** @var CreateResource $R */
        $R = $this->service->getResource(BaseService::CREATE, [
            'item' => $formClass::findOne($id)
        ]);

        if ( $this->request->isPost ){
            $R->item->load(Yii::$app->request->post());
            $this->service->create($R->item);
        }

        return $R->render();
    }


    /**
     *  action `Update`
     *
     *  @param int $id
     *
     * @return string|array
     */
    public function actionUpdate(int $id): array|string
    {
        $formClass = $this->service->getClassForm();

        /** @var UpdateResource $R */
        $R = $this->service->getResource(BaseService::UPDATE, [
            'item' => $formClass::findOne($id)
        ]);

        if ( $this->request->isPost ){
            $R->item->load(Yii::$app->request->post());
            $this->service->update($R->item);
        }

        return $R->render();
    }
}