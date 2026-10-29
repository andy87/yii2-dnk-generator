<?php

namespace frontend\components\controllers;

use yii\base\InvalidConfigException;
use yii\web\Response;
use yii\web\ErrorAction;
use yii\data\ActiveDataProvider;
use {{BaseControllerClassName}} as BaseControllerClass;
use common\components\resources\crud\ListResource;
use common\components\resources\crud\ReadResource;
use andy87\dnk\source\resources\GridViewResource;
use andy87\dnk\source\base\BaseService;
use andy87\dnk\source\interfaces\controllers\frontend\ControllerFrontendInterface;

/**
 * Base Controller for environment `frontend`
 */
abstract class FrontendController extends BaseControllerClass implements ControllerFrontendInterface
{
    // константы

    /** @var string className Сервиса */
    public const SERVICE = BaseService::class;

    /**
     * @return array
     */
    public function actions(): array
    {
            return [
                'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }



    // Methods

    /**
     * Показывает список записей или возвращает JSON с items и totalCount.
     *
     * @return Response|string JSON-ответ или HTML списка.
     * @throws InvalidConfigException Если сервис или ресурс настроен неверно.
     */
    public function actionList(): Response|string
    {
        $form = $this->service->getForm();

        /** @var ListResource $R */
        $R = $this->service->getResource(BaseService::LIST, [
            'gridViewResource' => new GridViewResource($form, new ActiveDataProvider([
                'query' => $this->service->findWhere([])
            ]))
        ]);

        if ( $this->isJsonResponse() ) {
            $provider = $R->gridViewResource->activeDataProvider;
            return $this->asJson([
                'items' => $provider->getModels(),
                'totalCount' => $provider->getTotalCount(),
            ]);
        }

        return $R->render($this);
    }


    /**
     * Показывает запись или возвращает её в JSON-поле item.
     *
     * @param int $id Идентификатор записи.
     * @return Response|string JSON-ответ или HTML записи.
     * @throws InvalidConfigException Если сервис или ресурс настроен неверно.
     */
    public function actionRead(int $id): Response|string
    {
        /** @var ReadResource $R */
        $R = $this->service->getResource(BaseService::READ, [
            'item' => $this->service->findWhere(['id' => $id])->one()
        ]);

        if ( $this->isJsonResponse() ) return $this->asJson(['item' => $R->item]);

        return $R->render($this);
    }

    /**
     * Проверяет запрошенный формат без изменения объекта ответа.
     *
     * @return bool Запрошен ли JSON через параметр type.
     */
    public function isJsonResponse(): bool
    {
        return $this->request->get('type') === 'json';
    }
}
