<?php

use andy87\dnk\source\interfaces\models\common\ModelInterface;

/** Requires confirmation only for the application's creation scenario. */
class ScenarioCreateForm extends backend\models\forms\UserForm
{
    public ?string $confirmation = null;

    /** @return array Creation rules in addition to the source model's rules. */
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            [['confirmation'], 'required', 'on' => 'create'],
            [['confirmation'], 'compare', 'compareAttribute' => 'name', 'on' => 'create'],
        ]);
    }

    /** @return string Application-specific POST scope. */
    public function formName(): string { return 'ScenarioInput'; }
}

/** Checks compatibility with an existing array-based create override. */
class ScenarioCreateService extends backend\services\UserService
{
    public static array $received = [];
    public static bool $fail = false;

    /**
     * Records input and preserves the application's normal creation hook.
     * @param array $params Submitted safe values.
     * @param bool $is_save Whether to persist the form.
     * @return ModelInterface Result of validation and persistence.
     * @throws RuntimeException When the fixture simulates an application failure.
     * @throws yii\base\InvalidConfigException When the form configuration is invalid.
     */
    public function create(array $params = [], bool $is_save = true): ModelInterface
    {
        self::$received[] = $params;
        if (self::$fail) throw new RuntimeException('Application create failure.');
        return parent::create($params, $is_save);
    }
}

/**
 * Binds a custom form and service only for one assertion group.
 * @param callable $check Assertions to run with the custom creation service.
 * @return void
 * @throws Throwable When an assertion or application operation fails.
 */
function withScenarioService(callable $check): void
{
    Yii::$container->set(backend\models\forms\UserForm::class, ScenarioCreateForm::class);
    Yii::$container->set(backend\services\UserService::class, ScenarioCreateService::class);
    ScenarioCreateService::$received = [];
    ScenarioCreateService::$fail = false;
    try {
        $check(new ScenarioCreateService());
    } finally {
        ScenarioCreateService::$fail = false;
        Yii::$container->clear(backend\models\forms\UserForm::class);
        Yii::$container->clear(backend\services\UserService::class);
    }
}

$tests = [];
foreach (['missing' => null, 'mismatched' => 'Wrong', 'valid' => 'Scenario record'] as $case => $confirmation) {
    $tests['scenario: ' . $case . ' confirmation uses the resource-selected scenario'] = static function () use ($case, $confirmation): void {
        withScenarioService(static function () use ($case, $confirmation): void {
            $controller = generatedController('backend');
            $resource = null;
            $controller->addMutation(new andy87\dnk\source\resources\MutationResource(
                backend\components\controllers\BackendController::EVENT_RESOURCE_CREATED,
                andy87\dnk\source\base\BaseService::CREATE,
                static function ($created) use (&$resource) {
                    $resource = $created;
                    $created->item->scenario = 'create';
                    return $created;
                }
            ));
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $fields = ['name' => 'Scenario record', 'scenario' => 'default', 'id' => 900001];
            if ($confirmation !== null) $fields['confirmation'] = $confirmation;
            Yii::$app->request->setBodyParams(['ScenarioInput' => $fields]);
            expect($controller->runAction('create') !== '', 'The form did not render.');
            $model = $resource->item;
            expect($model->scenario === 'create', 'The selected scenario was lost or changed by POST.');
            expect($model->confirmation === $confirmation, 'The virtual field was lost.');
            expect(count(ScenarioCreateService::$received) === 1, 'The create override was bypassed or called twice.');
            expect(!array_key_exists('scenario', ScenarioCreateService::$received[0]) && !array_key_exists('id', ScenarioCreateService::$received[0]), 'Unsafe values reached create().');
            if ($case === 'valid') {
                expect(!$model->isNewRecord && !$model->hasErrors(), 'Valid scenario data was not saved.');
                expect(backend\models\items\User::findOne($model->id)->name === 'Scenario record', 'Incorrect data was saved.');
                expect((int) backend\models\items\User::find()->count() === 1, 'Creation wrote more than one record.');
            } else {
                expect($model->isNewRecord && $model->hasErrors('confirmation'), 'Scenario validation was bypassed.');
                expect((int) backend\models\items\User::find()->count() === 0, 'Invalid data reached the database.');
            }
        });
    };
}
$tests['scenario: save=false and later ordinary creation retain their contracts'] = static function (): void {
    withScenarioService(static function (ScenarioCreateService $service): void {
        $form = $service->createInScenario(['name' => 'Scenario record', 'confirmation' => 'Scenario record'], 'create', false);
        expect($form->isNewRecord && $form->scenario === 'create' && $form->confirmation === 'Scenario record', 'save=false lost form data or scenario.');
        expect((int) backend\models\items\User::find()->count() === 0, 'save=false wrote a record.');
        $ordinary = $service->create(['name' => 'Ordinary creation']);
        expect(!$ordinary->isNewRecord && $ordinary->scenario === 'default', 'The previous scenario leaked into ordinary create().');
    });
};
$tests['scenario: an application exception does not leak the creation scenario'] = static function (): void {
    withScenarioService(static function (ScenarioCreateService $service): void {
        ScenarioCreateService::$fail = true;
        $caught = false;
        try {
            $service->createInScenario(['name' => 'Rejected'], 'create');
        } catch (RuntimeException $error) {
            $caught = $error->getMessage() === 'Application create failure.';
        }
        expect($caught, 'The application failure was hidden.');
        ScenarioCreateService::$fail = false;
        $ordinary = $service->create(['name' => 'After failure']);
        expect(!$ordinary->isNewRecord && $ordinary->scenario === 'default', 'A failed call leaked its scenario.');
        expect((int) backend\models\items\User::find()->count() === 1, 'The failed call wrote a record.');
    });
};
return $tests;
