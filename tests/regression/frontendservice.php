<?php

/** Supplies a frontend-only validation rule through the supported Yii container. */
class RestrictedFrontendForm extends frontend\models\forms\UserForm
{
    /**
     * Restricts the status that a frontend form may save.
     * @return array Parent validation rules plus the frontend status restriction.
     */
    public function rules(): array
    {
        return array_merge(parent::rules(), [[['status_id'], 'in', 'range' => [1], 'strict' => true]]);
    }

    /**
     * Retains the form scope used by the generated HTML form.
     * @return string POST key containing the form attributes.
     */
    public function formName(): string
    {
        return 'UserForm';
    }
}

return [
    'frontend service: scoped create saves the form and ignores unsafe attributes' => function (): void {
        $service = new frontend\services\UserService();
        $model = $service->create(['UserForm' => ['name' => 'Frontend input', 'id' => 900001]]);
        expect($model instanceof frontend\models\forms\UserForm, 'create() returned an item instead of a form.');
        expect(!$model->isNewRecord && $model->name === 'Frontend input', 'Scoped form input was not saved.');
        expect((int) $model->id !== 900001, 'The unsafe primary key was assigned.');
        expect(frontend\models\items\User::findOne($model->id)->name === 'Frontend input', 'Stored data differs from the form.');
        expect(get_class($service->getModel()) === frontend\models\items\User::class, 'The read model factory changed.');
    },
    'frontend service: flat and scoped create enforce configured form rules' => function (): void {
        Yii::$container->set(frontend\models\forms\UserForm::class, RestrictedFrontendForm::class);
        try {
            $service = new frontend\services\UserService();
            $invalid = ['name' => 'Forbidden status', 'status_id' => 2];
            foreach ([$invalid, ['UserForm' => $invalid]] as $params) {
                $model = $service->create($params);
                expect($model->isNewRecord && $model->hasErrors('status_id'), 'The frontend form restriction was bypassed.');
            }
            expect((int) frontend\models\items\User::find()->count() === 0, 'A forbidden record reached the database.');
            $valid = $service->create(['UserForm' => ['name' => 'Allowed status', 'status_id' => 1]]);
            expect(!$valid->isNewRecord, 'Valid scoped input was rejected.');
            expect((int) frontend\models\items\User::findOne($valid->id)->status_id === 1, 'The allowed status was not persisted.');
        } finally {
            Yii::$container->clear(frontend\models\forms\UserForm::class);
        }
    },
    'frontend service: save=false fills a form without writing a record' => function (): void {
        $model = (new frontend\services\UserService())->create(['name' => 'Unsaved frontend'], false);
        expect($model instanceof frontend\models\forms\UserForm, 'save=false returned the wrong model class.');
        expect($model->isNewRecord && $model->name === 'Unsaved frontend', 'The unsaved form lost its data.');
        expect((int) frontend\models\items\User::find()->count() === 0, 'save=false wrote to the database.');
    },
];
