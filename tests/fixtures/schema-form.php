<?php

require __DIR__ . '/../bootstrap.php';

$kind = $argv[1] ?? '';
expect(in_array($kind, ['minimal', 'columns'], true), 'Unknown schema fixture.');
$root = prepareApplication();
$status = 0;
try {
    $db = Yii::$app->db;
    $db->createCommand()->dropTable('user')->execute();
    $columns = $kind === 'columns' ? "title TEXT NOT NULL, quantity INTEGER NOT NULL DEFAULT 7, internal_code TEXT NOT NULL DEFAULT 'server'," : '';
    $db->createCommand('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT, ' . $columns . '
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)')->execute();
    $db->schema->refresh();
    $rules = [[['created_at', 'updated_at'], 'safe']];
    if ($kind === 'columns') {
        $rules = array_merge($rules, [
            [['title'], 'required'], [['title'], 'string'],
            [['quantity'], 'default', 'value' => 7], [['quantity'], 'integer'],
        ]);
    }
    $source = <<<'PHP'
<?php
namespace common\models\sources;

/** Models the fixture schema with the rules supplied by Gii and application policy. */
class UserSource extends \common\components\core\BaseModel
{
    /** @return string Isolated database table. */
    public static function tableName(): string { return 'user'; }

    /** @return array Timestamp safety, required inputs and scalar defaults. */
    public function rules(): array { return {{rules}}; }
}
PHP;
    expect(!class_exists(common\models\sources\UserSource::class, false), 'Fixture model was already loaded.');
    file_put_contents($root . '/common/models/sources/UserSource.php', str_replace('{{rules}}', var_export($rules, true), $source));
    if ($kind === 'columns') {
        $formFile = $root . '/backend/models/forms/UserForm.php';
        $customForm = <<<'PHP'
/** @var string[] A non-scalar virtual value requiring an application widget. */
    public array $tags = ['first'];

    /** @return array Source rules and a safe array field. */
    public function rules(): array
    {
        return array_merge(parent::rules(), [[['tags'], 'safe']]);
    }
PHP;
        file_put_contents($formFile, str_replace('// custom code ...', $customForm, file_get_contents($formFile)));
    }
    $controller = generatedController('backend');
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $html = $controller->runAction('create');
    expect($html !== '', 'Create form did not render.');
    foreach (['id', 'name', 'description', 'status_id', 'internal_code', 'created_at', 'updated_at', 'tags'] as $attribute) {
        expect(strpos($html, 'name="UserForm[' . $attribute . ']"') === false, 'An absent, unsafe or database-generated field was rendered: ' . $attribute);
    }
    if ($kind === 'columns') {
        foreach (['title', 'quantity'] as $attribute) {
            expect(strpos($html, 'name="UserForm[' . $attribute . ']"') !== false, 'A safe schema field was not rendered: ' . $attribute);
        }
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['UserForm' => ['title' => '']]);
        $invalid = $controller->runAction('create');
        expect(strpos($invalid, 'Title cannot be blank.') !== false, 'Required-field validation errors disappeared.');
        expect((int) $db->createCommand('SELECT COUNT(*) FROM user')->queryScalar() === 0, 'Invalid data was saved.');
    }
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $input = $kind === 'columns' ? ['title' => 'Schema title', 'id' => 900001, 'internal_code' => 'forged'] : [];
    Yii::$app->request->setBodyParams(['UserForm' => $input]);
    expect($controller->runAction('create') !== '', 'Create POST did not render.');
    $rows = $db->createCommand('SELECT * FROM user')->queryAll();
    expect(count($rows) === 1, 'Create did not save exactly one record.');
    $row = $rows[0];
    expect((int) $row['id'] !== 900001, 'The unsafe ID was assigned.');
    expect(preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row['created_at']) === 1, 'The database timestamp default was lost.');
    expect($row['updated_at'] !== null, 'The update timestamp default was lost.');
    if ($kind === 'columns') {
        expect($row['title'] === 'Schema title' && (int) $row['quantity'] === 7 && $row['internal_code'] === 'server', 'Input, numeric default or unsafe-field protection failed.');
    }
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $html = $controller->runAction('update', ['id' => $row['id']]);
    expect($html !== '', 'Update form did not render.');
    if ($kind === 'columns') expect(strpos($html, 'value="Schema title"') !== false, 'The update form lost its saved value.');
    $_SERVER['REQUEST_METHOD'] = 'POST';
    Yii::$app->request->setBodyParams(['UserForm' => $kind === 'columns'
        ? ['title' => 'Updated title', 'quantity' => '9', 'internal_code' => 'forged again'] : []]);
    $controller->runAction('update', ['id' => $row['id']]);
    $updated = $db->createCommand('SELECT * FROM user WHERE id=:id', [':id' => $row['id']])->queryOne();
    expect($updated['created_at'] === $row['created_at'], 'Update changed an omitted timestamp.');
    if ($kind === 'columns') {
        expect($updated['title'] === 'Updated title' && (int) $updated['quantity'] === 9 && $updated['internal_code'] === 'server', 'Update did not preserve validation and safe assignment.');
    }
    echo 'Schema form passed: ', $kind, PHP_EOL;
} catch (Throwable $error) {
    while (ob_get_level()) ob_end_clean();
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . PHP_EOL);
    $status = 1;
} finally {
    Yii::$app->response->data = null;
    Yii::$app->response->content = null;
    expect(strpos(realpath($root), realpath(__DIR__ . '/../runtime') . DIRECTORY_SEPARATOR) === 0, 'Unsafe fixture cleanup.');
    yii\helpers\FileHelper::removeDirectory($root);
}
exit($status);
