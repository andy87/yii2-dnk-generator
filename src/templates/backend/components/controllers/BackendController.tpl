<?php

namespace backend\components\controllers;

use Yii;
use yii\web\Request;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
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
    /** @var string Список открывается при обращении к контроллеру без action. */
    public $defaultAction = 'list';

    /** @var string[] Разрешённые роли или права Yii RBAC; пустой список закрывает backend. */
    public array $accessRoles = [];

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
     * Закрывает backend для гостей и пользователей без явно разрешённых прав.
     *
     * @return array Фильтры родителя и обязательная проверка доступа перед action.
     */
    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'dnkAccess' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => false, 'roles' => ['?']],
                    ['allow' => $this->accessRoles !== [], 'roles' => $this->accessRoles],
                ],
            ],
        ]);
    }

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
     * Создаёт запись и передаёт результат сохранения или ошибки в форму.
     *
     * @return string HTML формы с актуальной моделью.
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
            $safeAttributes = $R->item->safeAttributes();
            // Незаданные колонки сохраняют defaults БД; виртуальные поля передаются отдельно.
            $attributes = $R->item->getDirtyAttributes($safeAttributes)
                + $R->item->getAttributes(array_diff($safeAttributes, $R->item->attributes()));
            $R->item = $this->service->createInScenario(
                $attributes,
                $R->item->scenario
            );
        }

        $title = Entity::getLabelOne(static::ENTITY) . ': create';

        $this->setTitle($title);

        $this->setBreadcrumbs([
            [
                'label' => Entity::getLabelMany(static::ENTITY),
                'url' => ['list']
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
     * Обновляет существующую запись или сообщает об её отсутствии.
     *
     * @param int $id Идентификатор обновляемой записи.
     * @return string HTML формы редактирования.
     * @throws InvalidConfigException
     * @throws NotFoundHttpException Если запись не найдена.
     */
    public function actionUpdate(int $id): string
    {
        $this->beforeUpdate($id);

        $formClass = $this->service->getClassForm();
        $item = $formClass::findOne($id);
        if ($item === null) throw new NotFoundHttpException('The requested page does not exist.');

        /** @var UpdateResource $R */
        $R = $this->resourceConstruct(BaseService::UPDATE, [
            'item' => $item
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
                'url' => ['list']
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
     * Показывает существующую запись или сообщает об её отсутствии.
     *
     * @param int $id Идентификатор записи.
     * @return string HTML страницы записи.
     * @throws InvalidConfigException
     * @throws NotFoundHttpException Если запись не найдена.
     */
    public function actionRead(int $id): string
    {
        $this->beforeRead($id);
        $item = $this->service->findWhere(['id' => $id])->one();
        if ($item === null) throw new NotFoundHttpException('The requested page does not exist.');

        /** @var ReadResource $R */
        $R = $this->resourceConstruct(BaseService::READ, [
            'item' => $item
        ]);

        $title = Entity::getLabelOne(static::ENTITY) . ": $id view";

        $this->setTitle($title);

        $this->setBreadcrumbs([
            [
                'label' => Entity::getLabelMany(static::ENTITY),
                'url' => ['list']
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
     * Возвращает модель поиска, выбранную конкретным контроллером.
     *
     * @return string Полное имя класса SearchModel.
     */
    protected function getSearchClass(): string
    {
        return static::SEARCH_MODEL;
    }
}
