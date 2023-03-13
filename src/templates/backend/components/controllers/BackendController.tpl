<?php

namespace backend\components\controllers;

use Yii;
use yii\base\{Model,InvalidConfigException};
use common\components\Entity;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;
use common\components\resources\crud\CreateResource;
use common\components\resources\crud\UpdateResource;
use {{BaseControllerClassName}} as BaseControllerClass;
use andy87\dnk\source\base\BaseService;
use andy87\dnk\source\resources\GridViewResource;
use andy87\dnk\source\interfaces\controllers\backend\ControllerBackendInterface;

/**
 * Base Controller for environment `backend`
 */
abstract class BackendController extends BaseControllerClass implements ControllerBackendInterface
{
    // константы

    /** @var int entity ID */
    public const ENTITY = null;

    /** @var string className модели поиска */
    public const SEARCH_MODEL = Model::class;

    /** @var string className Сервиса */
    public const SERVICE = BaseService::class;



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

    /**
     *  action `Create`
     *
     * @throws InvalidConfigException
     */
    public function actionCreate(): string
    {
        /** @var CreateResource $R */
        $R = $this->service->getResource(BaseService::CREATE, [
            'item' => $this->service->getForm()
        ]);

        if ( $this->request->isPost ){
            $R->item->load(Yii::$app->request->post());
            $this->service->create($R->item->getAttributes());
        }

        $this->view->title = Entity::getLabelOne(static::ENTITY) . ': create';

        $this->view->params['breadcrumbs'][] = [
            'label' => Entity::getLabelMany(static::ENTITY),
            'url' => ['index']
        ];

        $this->view->params['breadcrumbs'][] = $this->view->title;

        return $R->render($this);
    }

    /**
     *  action `Update`
     *
     * @param int $id
     *
     * @return string
     * @throws InvalidConfigException
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

        $this->view->title = Entity::getLabelOne(static::ENTITY) . ": $id update";

        $this->view->params['breadcrumbs'][] = [
            'label' => Entity::getLabelMany(static::ENTITY),
            'url' => ['index']
        ];

        $this->view->params['breadcrumbs'][] = $this->view->title;

        return $R->render($this);
    }
}