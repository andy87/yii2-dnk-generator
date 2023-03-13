<?php

namespace frontend\controllers;

use Yii;
use yii\base\{Model,InvalidConfigException};
use common\components\Entity;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;
use frontend\services\{{CamelCase}}Service;
use frontend\models\search\{{CamelCase}}Search;
use frontend\components\controllers\FrontendController;
use andy87\dnk\source\resources\GridViewResource;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @property {{CamelCase}}Service $service
 */
class {{CamelCase}}Controller extends FrontendController
{
    // константы

    /** @var int entity ID */
    public const ENTITY = null;

    /** @var string Имя модели поиска */
    public const SEARCH = {{CamelCase}}Search::class;

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;


    // методы

    /**
     *  action `List`
     * @throws InvalidConfigException
     */
    public function actionList(): string
    {
        $class = static::SEARCH_MODEL;

        $searchForm = new $class();

        /** @var ListResource $R */
        $R = $this->service->getResource(BaseService::LIST, [
            'gridViewResource' => new GridViewResource(
                $searchForm,
                $searchForm->search($this->request->queryParams)
            )
        ]);

        $this->view->title = Entity::getLabelMany(static::ENTITY);

        $this->view->params['breadcrumbs'][] = $this->view->title;

        return $R->render($this);
    }

    /**
     *  action `Read`
     *
     * @throws InvalidConfigException
     */
    public function actionRead(int $id): string
    {
        /** @var ReadResource $R */
        $R = $this->service->getResource(BaseService::READ, [
            'item' => $this->service->findWhere(['id' => $id])->one()
        ]);

        $this->view->title = Entity::getLabelOne(static::ENTITY) . ": $id view";

        $this->view->params['breadcrumbs'][] = [
            'label' => Entity::getLabelMany(static::ENTITY),
            'url' => ['index']
        ];

        $this->view->params['breadcrumbs'][] = $this->view->title;

        return $R->render($this);
    }
}