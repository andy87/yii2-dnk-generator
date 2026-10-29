<?php

use andy87\dnk\source\interfaces\models\common\ModelInterface;

/** Models a validated confirmation field that is not a database column. */
class BackendConfirmationForm extends backend\models\forms\UserForm
{
    public ?string $confirmation = null;
    public string $serverValue = 'trusted';

    /** @return array Required confirmation and a property that POST must not overwrite. */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['confirmation'], 'required'],
            [['confirmation'], 'compare', 'compareAttribute' => 'name'],
        ]);
    }

    /** @return string Form scope used by the generated view. */
    public function formName(): string
    {
        return 'UserForm';
    }
}

/** Ensures the controller still invokes the application's create override. */
class RecordingBackendCreateService extends backend\services\UserService
{
    public static array $received = [];

    /**
     * Records the creation input and delegates normal persistence to the service.
     * @param array $params Safe values forwarded by the controller.
     * @param bool $is_save Whether to persist the new model.
     * @return ModelInterface Saved form or form with validation errors.
     * @throws yii\base\InvalidConfigException When the configured form is invalid.
     * @throws yii\base\InvalidArgumentException When scoped form input is malformed.
     */
    public function create(array $params = [], bool $is_save = true): ModelInterface
    {
        self::$received[] = $params;
        return parent::create($params, $is_save);
    }
}

/**
 * Submits a real generated backend action with an application-specific form.
 *
 * @param array $fields POST values under UserForm.
 * @param callable|null $prepare Optional resource customization before POST loading.
 * @return array Final view model and the parameters received by create().
 * @throws Throwable When the action or an assertion fails.
 */
function submitConfirmationForm(array $fields, ?callable $prepare = null): array
{
    Yii::$container->set(backend\models\forms\UserForm::class, BackendConfirmationForm::class);
    Yii::$container->set(backend\services\UserService::class, RecordingBackendCreateService::class);
    RecordingBackendCreateService::$received = [];
    try {
        $controller = generatedController('backend');
        $resource = null;
        $controller->addMutation(new andy87\dnk\source\resources\MutationResource(
            backend\components\controllers\BackendController::EVENT_RESOURCE_CREATED,
            andy87\dnk\source\base\BaseService::CREATE,
            static function ($created) use (&$resource, $prepare) {
                $resource = $created;
                if ($prepare !== null) $prepare($resource);
                return $resource;
            }
        ));
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['UserForm' => $fields]);
        $html = $controller->actionCreate();
        expect($html !== '', 'The form did not render.');
        expect(strpos($html, 'name="UserForm[confirmation]"') !== false, 'The safe virtual field did not render.');
        expect(strpos($html, 'name="UserForm[serverValue]"') === false, 'An unsafe virtual field was rendered.');
        expect(count(RecordingBackendCreateService::$received) === 1, 'The application create() override was bypassed or repeated.');
        expect($resource->item instanceof BackendConfirmationForm, 'The view received the wrong form class.');
        return [$resource->item, RecordingBackendCreateService::$received[0]];
    } finally {
        Yii::$container->clear(backend\models\forms\UserForm::class);
        Yii::$container->clear(backend\services\UserService::class);
    }
}

return [
    'backend form: virtual fields reach create while unsafe fields remain protected' => function (): void {
        [$model, $params] = submitConfirmationForm([
            'name' => 'Confirmed name', 'confirmation' => 'Confirmed name',
            'id' => 900001, 'serverValue' => 'forged',
        ]);
        expect(!$model->isNewRecord, 'Valid virtual form data did not create a record.');
        expect($model->confirmation === 'Confirmed name' && $params['confirmation'] === 'Confirmed name', 'The virtual field was lost.');
        expect((int) $model->id !== 900001 && !array_key_exists('id', $params), 'The unsafe primary key was forwarded.');
        expect($model->serverValue === 'trusted' && !array_key_exists('serverValue', $params), 'An unsafe virtual field was forwarded.');
        expect(backend\models\items\User::findOne($model->id)->name === 'Confirmed name', 'Stored data is incorrect.');
        expect((int) backend\models\items\User::find()->count() === 1, 'Creation wrote an unexpected number of records.');
    },
    'backend form: invalid virtual data remains available with validation errors' => function (): void {
        [$model] = submitConfirmationForm(['name' => 'Original name', 'confirmation' => 'Different name']);
        expect($model->isNewRecord && $model->hasErrors('confirmation'), 'The invalid confirmation was accepted.');
        expect($model->confirmation === 'Different name', 'The view lost the submitted virtual value.');
        expect((int) backend\models\items\User::find()->count() === 0, 'Invalid virtual data reached the database.');
    },
    'backend form: safe virtual values prepared by a resource mutation are retained' => function (): void {
        [$model, $params] = submitConfirmationForm(['name' => 'Prepared name'], static function ($resource): void {
            $resource->item->confirmation = 'Prepared name';
        });
        expect(!$model->isNewRecord && $model->confirmation === 'Prepared name', 'A resource-prepared virtual value was lost.');
        expect($params['confirmation'] === 'Prepared name', 'The prepared value did not reach create().');
    },
];
