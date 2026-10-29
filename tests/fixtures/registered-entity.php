<?php

require __DIR__ . '/../bootstrap.php';

$withLabels = ($argv[1] ?? '') === 'labels';
$root = prepareApplication();
$status = 0;
try {
    ob_start();
    try {
        (new RegressionGenerator('dnk', Yii::$app))->actionRun('post');
    } finally {
        ob_end_clean();
    }
    $entityFile = $root . '/common/components/Entity.php';
    $entity = str_replace("public const USER = 'user';", "public const USER = 'user';\n    public const POST = 2;", file_get_contents($entityFile));
    if ($withLabels) {
        $entity = str_replace('public const DATA = [', "public const DATA = [\n        self::POST => [self::TABLE_NAME => 'post', self::LABELS => [self::NAME_MANY => 'Posts', self::NAME_ONE => 'Post']],", $entity);
    }
    file_put_contents($entityFile, $entity);
    $tablesFile = $root . '/common/components/db/Tables.php';
    file_put_contents($tablesFile, str_replace("Entity::USER => 'user',", "Entity::USER => 'user',\n        Entity::POST => 'post',", file_get_contents($tablesFile)));
    $source = str_replace(['UserSource', "return 'user';"], ['PostSource', "return 'post';"], file_get_contents(__DIR__ . '/UserSource.php'));
    file_put_contents($root . '/common/models/sources/PostSource.php', $source);
    $db = Yii::$app->db;
    $tableSql = $db->createCommand("SELECT sql FROM sqlite_master WHERE name='user' AND type='table'")->queryScalar();
    $db->createCommand(str_replace('CREATE TABLE user', 'CREATE TABLE post', $tableSql))->execute();
    $db->createCommand()->insert('post', ['name' => 'Registered post'])->execute();
    $id = (int) $db->getLastInsertID();

    foreach (['backend', 'frontend'] as $side) {
        Yii::$app->setViewPath($root . '/' . $side . '/views');
        $class = $side . '\\controllers\\PostController';
        $controller = new $class('post', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        $actions = $side === 'backend' ? ['list', 'read', 'update', 'create'] : ['list', 'read'];
        foreach ($actions as $action) {
            $html = $controller->runAction($action, ['id' => $id]);
            expect(is_string($html) && $html !== '', 'The registered entity page failed to render.');
            $label = $withLabels ? ($action === 'list' ? 'Posts' : 'Post') : 'post';
            expect(strpos(Yii::$app->view->title, $label) === 0, 'The entity title does not match its metadata.');
        }
    }

    $db->createCommand()->dropTable('post')->execute();
    /** Exercises metadata lookup and real SQLite DDL while capturing unsupported table comments. */
    $migration = new class(['db' => $db]) extends andy87\dnk\source\base\BaseMigration {
        public mixed $entity = common\components\Entity::POST;
        public array $comments = [];

        /** @return void Applies SQLite options only to this isolated migration fixture. */
        public function init(): void
        {
            parent::init();
            $this->tableOptions = '';
        }

        /** @return array Minimal table definition for checking the metadata path. */
        public function constructTable(): array
        {
            return ['id' => $this->primaryKey()];
        }

        /**
         * Captures the comment because SQLite does not implement table comments.
         * @param string $table Table receiving the comment.
         * @param string $comment Entity label selected by the production migration.
         * @return void
         */
        public function addCommentOnTable($table, $comment): void
        {
            $this->comments[$table] = $comment;
        }
    };
    ob_start();
    try {
        $migration->safeUp();
    } finally {
        ob_end_clean();
    }
    expect($db->schema->getTableSchema('post', true) !== null, 'The registered table was not created.');
    expect($migration->comments['post'] === ($withLabels ? 'Posts' : 'post'), 'The migration lost the entity label.');
    echo 'Registered entity pages and migration passed.', PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . PHP_EOL);
    $status = 1;
} finally {
    // prepareApplication() owns this fresh test directory.
    expect(strpos(realpath($root), realpath(__DIR__ . '/../runtime') . DIRECTORY_SEPARATOR) === 0, 'Refusing to remove a directory outside the test runtime.');
    yii\helpers\FileHelper::removeDirectory($root);
}
exit($status);
