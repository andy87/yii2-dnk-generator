<?php

namespace frontend\controllers;

use yii\data\ActiveDataProvider;
use common\components\resources\GridViewResource;
use frontend\components\controllers\FrontendController;
use frontend\services\{{CamelCase}}Service;
use frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use frontend\resources\{{snake_case}}\{{CamelCase}}ReadResource;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @param {{CamelCase}}Service $servcie
 */
class {{CamelCase}}Controller extends FrontendController
{
    public const SERVICE = {{CamelCase}}Service::class;

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
        /** @var {{CamelCase}}ReadResource $R */
        $R = new $this->service->getResource( {{CamelCase}}Service::READ );

        $R->item = $this->service->findWhere(['id' => $id])->one();

        return $R->content();
    }
}