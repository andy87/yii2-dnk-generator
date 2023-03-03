<?php

namespace backend\controllers;

use backend\components\controllers\BackendController;
use backend\services\{{CamelCase}}Service;
use backend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use backend\resources\{{snake_case}}\{{CamelCase}}ReadResource;
use backend\resources\{{snake_case}}\{{CamelCase}}CreateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}UpdateResource;

/**
 * Controller for model `{{CamelCase}}` environment `backend`
 *
 * @param {{CamelCase}}Service $services
 */
class {{CamelCase}}Controller extends BackendController
{
    public const SERVICE = {{CamelCase}}Service::class;

    // call console command:
    // shell_exec("./yii gii/crud --modelClass=backend\\models\\items\\{{CamelCase}}Source --controllerNamespace=backend\\controllers\\crud --baseControllerClass=backend\\controllers\\cruds\\{{CamelCase}}Controller --viewPath=@backend\\views\\cruds\\{{snake_case}} --enableI18N=1")


    public function actionList()
    {
        /** @var {{CamelCase}}ListResource $R */
        $R = new $this->service->getResource( {{CamelCase}}Service::LIST );

        $form = $this->service->getForm();
        $activeDataProvider = new ActiveDataProvider();

        $gridViewResource = new GridViewResource($form, $activeDataProvider);

        $R->gridViewResource = $gridViewResource;

        return $R->content();
    }


    public function actionRead(int $id)
    {
        /** @var {{CamelCase}}ListResource $R */
        $R = new $this->service->getResource( {{CamelCase}}Service::READ );

        $R->item = $this->service->findWhere(['id' => $id])->one();

        return $R->content();
    }

    public function actionUpdate(int $id)
    {
        /** @var {{CamelCase}}UpdateResource $R */
        $R = new $this->service->getResource( {{CamelCase}}Service::UPDATE );

        $formClass = $this->service->getClassForm();

        $R->item = $formClass::findOne($id);

        if ( $this->request->isPost )
        {
            $R->item->load(Yii::$app->request->post());
        }

        $this->service->update($R->item);

        return $R->content();
    }

    public function actionCreate()
    {
        /** @var {{CamelCase}}CreateResource $R */
        $R = new $this->service->getResource( {{CamelCase}}Service::CREATE );

        $formClass = $this->service->getClassForm();

        $R->item = new $formClass();

        if ( $this->request->isPost )
        {
            $R->item->load(Yii::$app->request->post());
        }

        $this->service->create($R->item);

        return $R->content();
    }
}