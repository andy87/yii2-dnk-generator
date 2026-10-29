<?php

/**
 * Exercises applications that customize the HTML form name or remove its scope.
 */
class ScopedUserForm extends backend\models\forms\UserForm
{
    /** @var string HTML form scope configured by the application. */
    public string $scope = 'Account';

    /**
     * Supplies a custom scope without changing the ActiveRecord table.
     *
     * @return string Form name, or an empty string for flat input.
     */
    public function formName(): string
    {
        return $this->scope;
    }
}

return [
    'service: ActiveForm POST creates a record and ignores unsafe attributes' => function (): void {
        $service = new backend\services\UserService();
        $model = $service->create(['UserForm' => ['name' => 'Submitted', 'id' => 900001]]);
        expect(!$model->isNewRecord, 'ActiveForm POST was not saved.');
        expect($model->name === 'Submitted', 'Submitted name was lost.');
        expect((int) $model->id !== 900001, 'Unsafe primary key was assigned.');
        expect(backend\models\items\User::findOne($model->id)->name === 'Submitted', 'Wrong stored name.');
    },
    'service: scoped update persists fields and ignores fields outside the scope' => function (): void {
        $service = new backend\services\UserService();
        $model = $service->create(['name' => 'Original']);
        $service->update($model, ['name' => 'Outside scope', 'UserForm' => ['name' => 'Updated']]);
        expect(backend\models\items\User::findOne($model->id)->name === 'Updated', 'Scoped update was ignored.');
    },
    'service: flat input and save=false keep their existing behavior' => function (): void {
        $service = new backend\services\UserService();
        $model = $service->create(['name' => 'Unsaved'], false);
        expect($model->isNewRecord, 'save=false unexpectedly persisted the model.');
        expect((int) backend\models\items\User::find()->count() === 0, 'Unexpected database write.');
        $service->update($model, ['name' => 'Flat update']);
        expect(backend\models\items\User::findOne($model->id)->name === 'Flat update', 'Flat input stopped working.');
    },
    'service: custom form names and empty form names are supported' => function (): void {
        $service = new backend\services\UserService();
        $model = new ScopedUserForm();
        $service->update($model, ['Account' => ['name' => 'Custom scope']]);
        expect(!$model->isNewRecord, 'Custom formName was ignored.');
        $model->scope = '';
        $service->update($model, ['name' => 'Unscoped']);
        expect(ScopedUserForm::findOne($model->id)->name === 'Unscoped', 'Empty formName stopped accepting flat data.');
    },
    'service: validation errors remain available without inserting a record' => function (): void {
        $model = (new backend\services\UserService())->create(['UserForm' => ['name' => '']]);
        expect($model->isNewRecord && $model->hasErrors('name'), 'Validation errors were lost.');
        expect((int) backend\models\items\User::find()->count() === 0, 'Invalid record was inserted.');
    },
    'service: malformed form scopes are rejected without changing the record' => function (): void {
        $service = new backend\services\UserService();
        $model = $service->create(['name' => 'Original']);
        foreach ([null, 'invalid'] as $value) {
            $rejected = false;
            try {
                $service->update($model, ['UserForm' => $value]);
            } catch (yii\base\InvalidArgumentException $error) {
                $rejected = true;
            }
            expect($rejected, 'Malformed form scope was silently accepted.');
            expect(backend\models\items\User::findOne($model->id)->name === 'Original', 'Malformed input changed the record.');
        }
    },
];
