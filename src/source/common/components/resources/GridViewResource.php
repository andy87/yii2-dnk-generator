<?php

namespace common\components\resources;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\components\core\BaseResource;

/**
 *
 */
class GridViewResource extends BaseResource
{
    /** @var Model $searchForm */
    public Model $searchForm;

    /** @var ActiveDataProvider $activeDataProvider */
    public ActiveDataProvider $activeDataProvider;
    
    /**
     *
     */
    public function __construct( Model $searchForm, ActiveDataProvider $activeDataProvider, array $config = [] )
    {
        parent::__construct($config);

        $this->searchForm = $searchForm;

        $this->activeDataProvider = $activeDataProvider;
    }
}