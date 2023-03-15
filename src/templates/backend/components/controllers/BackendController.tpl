<?php

namespace backend\components\controllers;

use Yii;
use yii\web\Request;
use yii\base\{Model,InvalidConfigException};
use andy87\dnk\source\base\BaseService;
use andy87\dnk\source\resources\{MutationResource,GridViewResource};
use andy87\dnk\source\interfaces\controllers\backend\ControllerBackendInterface;
use common\components\Entity;
use common\components\resources\crud\{ListResource,ReadResource,CreateResource,UpdateResource};
use {{BaseControllerClassName}} as BaseControllerClass;
use andy87\dnk\source\base\BaseResource;
use backend\components\resources\LinkResource;

/**
 * Base Controller for environment `backend`
 *
 * @property Request $request
 * @property BaseService $service
 */
abstract class BackendController extends BaseControllerClass implements ControllerBackendInterface
{
    // const

    /** @var int entity ID */
    public const ENTITY = null;


    /** @var int Event ID Resource Init */
    public const EVENT_RESOURCE_INIT = 0;
    /** @var int Event ID Resource Created */
    public const EVENT_RESOURCE_CREATED = 1;


    /** @var string className модели поиска */
    public const SEARCH_MODEL = Model::class;
    /** @var string className Сервиса */
    public const SERVICE = BaseService::class;



    // Property

    /** @var array list of callback func for mutation some params */
    public array $mutationList = [
        self::EVENT_RESOURCE_INIT => [
            BaseService::LIST => [],
            BaseService::CREATE => [],
            BaseService::UPDATE => [],
            BaseService::READ => [],
        ],
        self::EVENT_RESOURCE_CREATED => [
            BaseService::LIST => [],
            BaseService::CREATE => [],
            BaseService::UPDATE => [],
            BaseService::READ => [],
        ]
    ];



    // Methods

    /**
     *  action `List`
     * @throws InvalidConfigException
     */
    public function actionList(): string
    {
        $this->beforeList();

        $class = $this->getSearchClass();

        $searchForm = new $class();

        /** @var ListResource $R */
        $R = $this->resourceConstruct(BaseService::LIST, [
            'gridViewResource' => new GridViewResource(
                $searchForm,
                $searchForm->search($this->request->queryParams)
            )
        ]);

        $title = Entity::getLabelMany(static::ENTITY);

        $this->setTitle($title);

        $this->setBreadcrumbs([ $title ]);

        return $R->render($this);
    }

    /**
     * @return void
     */
    public function beforeList(): void
    {

    }


    /**
     *  action `Create`
     * @throws InvalidConfigException
     */
    public function actionCreate(): string
    {
        $this->beforeCreate();

        /** @var CreateResource $R */
        $R = $this->resourceConstruct(BaseService::CREATE, [
            'item' => $this->service->getEntity()
        ]);

        if ( $this->request->isPost ){
            $R->item->load(Yii::$app->request->post());
            $this->service->create($R->item->getAttributes());
        }

        $title = Entity::getLabelOne(static::ENTITY) . ': create';

        $this->setTitle($title);

        $this->setBreadcrumbs([
            [
                'label' => Entity::getLabelMany(static::ENTITY),
                'url' => ['index']
            ],
            $title
        ]);

        return $R->render($this);
    }

    /**
     * @return void
     */
    public function beforeCreate(): void
    {

    }

    /**
     *  action `Update`
     *
     * @param int $id
     * @return string
     * @throws InvalidConfigException
     */
    public function actionUpdate(int $id): string
    {
        $this->beforeUpdate($id);

        $formClass = $this->service->getClassForm();

        /** @var UpdateResource $R */
        $R = $this->resourceConstruct(BaseService::UPDATE, [
            'item' => $formClass::findOne($id)
        ]);

        if ( $this->request->isPost ){
            $R->item->load(Yii::$app->request->post());
            $this->service->update($R->item);
        }

        $title = Entity::getLabelOne(static::ENTITY) . ": $id update";

        $this->setTitle($title);

        $this->setBreadcrumbs([
            [
                'label' => Entity::getLabelMany(static::ENTITY),
                'url' => ['index']
            ],
            $title
        ]);

        return $R->render($this);
    }

    /**
     * @param int $id
     * @return void
     */
    public function beforeUpdate(int $id): void
    {

    }

    /**
     *  action `Read`
     * @throws InvalidConfigException
     */
    public function actionRead(int $id): string
    {
        $this->beforeRead($id);

        /** @var ReadResource $R */
        $R = $this->resourceConstruct(BaseService::READ, [
            'item' => $this->service->findWhere(['id' => $id])->one()
        ]);

        $title = Entity::getLabelOne(static::ENTITY) . ": $id view";

        $this->setTitle($title);

        $this->setBreadcrumbs([
            [
                'label' => Entity::getLabelMany(static::ENTITY),
                'url' => ['index']
            ],
            $title
        ]);

        return $R->render($this);
    }

    /**
     * @param int $id
     * @return void
     */
    public function beforeRead(int $id): void
    {

    }



    /**
     * Служит для создания обработчика событий при создании экземпляра класса, который позволяет:
     *      - заменить создаваемый экземпляра класа
     *      - обновить/установит свойства созданного экземпляра класа
     *
     * @param MutationResource $resource
     *
     * @return void
     */
    public function addMutation(MutationResource $resource): void
    {
        $this->mutationList[$resource->event][$resource->action][] = $resource->func;
    }

    /**
     * Конструктор и обработчик магии связанной с созданием ресурсов
     *
     * Type: magic
     *
     * @param int $view
     * @param array $params
     * @return BaseResource
     * @throws InvalidConfigException
     */
    private function resourceConstruct( int $view, array $params = [] ): BaseResource
    {
        if (empty($this->mutationList[self::EVENT_RESOURCE_INIT][$view])){

            $R = $this->service->getResource( $view, $params );

        } else {

            $R = null;

            foreach ( $this->mutationList[self::EVENT_RESOURCE_INIT][$view] as $index => $func ){
                $R = ( $index ) ? $func($R) : $func($params);
            }
        }

        if ( !empty($this->mutationList[self::EVENT_RESOURCE_CREATED][$view]) ){
            foreach ($this->mutationList[self::EVENT_RESOURCE_CREATED][$view] as $func){
                $R = $func($R);
            }
        }

        return $R;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle( string $title ): void
    {
        $this->view->title = $title;
    }

    /**
     * setup default breadcrumbs
     *
     * @param array $breadcrumbs
     * @return void
     */
    public function setBreadcrumbs( array $breadcrumbs ): void
    {
        foreach ( $breadcrumbs as $breadcrumb )
        {
            $this->view->params['breadcrumbs'][] = $breadcrumb;
        }
    }

    /**
     * @return string
     */
    protected function getSearchClass(): string
    {
        return self::SEARCH_MODEL;
    }
}