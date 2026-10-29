<?php

return [
    'migration: generated class loads and initializes with the runtime base' => function (): void {
        $files = glob(Yii::getAlias('@console/migrations/*_create_table__user.php'));
        expect(count($files) === 1, 'Expected one generated migration.');
        require $files[0];
        $class = pathinfo($files[0], PATHINFO_FILENAME);
        $migration = new $class(['db' => Yii::$app->db]);
        expect($migration instanceof andy87\dnk\source\base\BaseMigration, 'Wrong migration base.');
        expect($migration->entity === common\components\Entity::USER, 'Wrong migration entity.');
        $columns = $migration->constructTable();
        expect(isset($columns['id'], $columns['created_at'], $columns['updated_at']), 'Missing migration columns.');
        $migration->entity = 42;
        expect($migration->entity === 42, 'Numeric entity keys are no longer supported.');
    },
];
