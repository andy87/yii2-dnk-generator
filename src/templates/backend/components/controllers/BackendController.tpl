<?php

namespace backend\components\controllers;

use ReflectionException;
use yii\data\ActiveDataProvider;
use andy87\dnk\source\base\BaseService;
use andy87\dnk\source\resources\GridViewResource;
use andy87\dnk\source\interfaces\controllers\backend\ControllerBackendInterface;
use {{BaseControllerClassName}} as BaseControllerClass;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;
use common\components\resources\crud\CreateResource;
use common\components\resources\crud\UpdateResource;

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
     * @throws ReflectionException
     */
    public function actionList(): string
    {
        $form = $this->service->getForm();

        $activeDataProvider = new ActiveDataProvider();

        /** @var ListResource $R */
        $R = $this->service->getResource(BaseService::LIST, [
            'gridViewResource' => new GridViewResource($form, $activeDataProvider)
        ]);

        return $R->render();
    }

    /**
     *  action `Read`
     *
     * @throws ReflectionException
     */
    public function actionRead(int $id): string
    {
        /** @var ReadResource $R */
        $R = $this->service->getResource(BaseService::READ, [
            'item' => $this->service->findWhere(['id' => $id])->one()
        ]);

        return $R->render();
    }

    /**
     *  action `Create`
     *
     * @throws ReflectionException
     */
    public function actionCreate(int $id): string
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
     * @param int $id
     *
     * @return string
     * @throws ReflectionException
     */
    public function actionUpdate(int $id): string
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