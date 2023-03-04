<?php

namespace andy87\dnk\common\components\resources;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\components\core\BaseResource;

/**
 *  Ресурсы для компонента `GridView`
 */
class GridViewResource extends BaseResource
{
    /** @var Model $searchForm Модель - форма поиска */
    public Model $searchForm;

    /** @var ActiveDataProvider $activeDataProvider Дата провайдер */
    public ActiveDataProvider $activeDataProvider;

    /**
     * Конструктор
     *
     * @param Model $searchForm Модель - форма поиска
     * @param ActiveDataProvider $activeDataProvider Дата провайдер
     * @param array $config Настройки
     */
    public function __construct( Model $searchForm, ActiveDataProvider $activeDataProvider, array $config = [] )
    {
        parent::__construct($config);

        $this->searchForm = $searchForm;

        $this->activeDataProvider = $activeDataProvider;
    }
}