<?php

namespace common\components\resources\crud;

use {{BaseResourceClassName}} as BaseResourceClass;
use andy87\dnk\source\interfaces\models\common\ModelFormInterface;
use andy87\dnk\source\interfaces\resources\crud\CreateResourcesInterface;

/**
 * Base file for `CreateResource`
 */
class CreateResource extends BaseResourceClass implements CreateResourcesInterface
{
    /** @var string  */
    public string $title;

    /** @var ModelFormInterface */
    public ModelFormInterface $form;

    /**
     * @param ModelFormInterface $item
     * @param array $config
     */
    public function __construct( ModelFormInterface $form, array $config = [] )
    {
        $this->form = $form;

        parent::__construct($config);
    }
}