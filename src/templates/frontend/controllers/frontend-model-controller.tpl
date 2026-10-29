<?php

namespace frontend\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;
use andy87\dnk\source\base\BaseService;
use andy87\dnk\source\resources\GridViewResource;
use common\components\Entity;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;
use frontend\services\{{CamelCase}}Service;
use frontend\models\search\{{CamelCase}}Search;
use frontend\components\controllers\FrontendController;

/**
 * Controller for model `{{CamelCase}}` environment `frontend`
 *
 * @property {{CamelCase}}Service $service
 */
class {{CamelCase}}Controller extends FrontendController
{
    // константы

    /** @var int entity ID */
    public const ENTITY = Entity::{{UPPER_CASE}};

    /** @var string Имя модели поиска */
    public const SEARCH_MODEL = {{CamelCase}}Search::class;

    /** @var string Совместимость с прежним именем константы. */
    public const SEARCH = self::SEARCH_MODEL;

    /** @var string className Сервиса */
    public const SERVICE = {{CamelCase}}Service::class;


    // Methods

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
     * Показывает существующую запись или сообщает об её отсутствии.
     *
     * @param int $id Идентификатор записи.
     * @return string HTML страницы записи.
     * @throws InvalidConfigException
     * @throws NotFoundHttpException Если запись не найдена.
     */
    public function actionRead(int $id): string
    {
        $item = $this->service->findWhere(['id' => $id])->one();
        if ($item === null) throw new NotFoundHttpException('The requested page does not exist.');

        /** @var ReadResource $R */
        $R = $this->service->getResource(BaseService::READ, [
            'item' => $item
        ]);

        $this->view->title = Entity::getLabelOne(static::ENTITY) . ": $id view";

        $this->view->params['breadcrumbs'][] = [
            'label' => Entity::getLabelMany(static::ENTITY),
            'url' => ['list']
        ];

        $this->view->params['breadcrumbs'][] = $this->view->title;

        return $R->render($this);
    }
}
