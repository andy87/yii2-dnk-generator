<?php

/**
 * Executes a real DNK console action against a controlled external process.
 *
 * @param string $action DNK Gii action name.
 * @param string $entities Comma-separated entity names.
 * @return array Exit status, child calls and console output.
 * @throws Throwable On filesystem or process initialization failure.
 */
function runGiiConsole(string $action, string $entities): array
{
    $root = Yii::getAlias('@runtime') . '/' . uniqid('gii cli-');
    yii\helpers\FileHelper::createDirectory($root);
    copy(__DIR__ . '/../fixtures/gii-child.php', $root . '/yii');
    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../fixtures/gii-console.php')
        . ' ' . escapeshellarg($action) . ' ' . escapeshellarg($entities) . ' 2>&1';
    $previous = getcwd();
    try {
        chdir($root);
        exec($command, $output, $status);
    } finally {
        chdir($previous);
    }
    $calls = array_map(static fn ($line) => json_decode($line, true, 512, JSON_THROW_ON_ERROR), file($root . '/calls.jsonl', FILE_IGNORE_NEW_LINES));
    return [$status, $calls, implode(PHP_EOL, $output)];
}

return [
    'gii: missing command configuration is rejected before launching a child' => function (): void {
        foreach (['actionGiiModel', 'actionGiiCrud'] as $method) {
            foreach ([null, ''] as $value) {
                $rejected = false;
                try {
                    (new RegressionGenerator('dnk', Yii::$app, ['command' => [1 => $value, 2 => $value]]))->$method('user');
                } catch (yii\base\InvalidConfigException $error) {
                    $rejected = true;
                }
                expect($rejected, 'Missing command configuration launched Gii.');
            }
        }
    },
    'gii: successful model generation processes all entities' => function (): void {
        [$status, $calls] = runGiiConsole('gii-model', 'user,profile');
        expect($status === 0 && count($calls) === 2, 'Successful batch generation failed.');
        expect($calls[0]['arguments'] === ['gii/model', '--tableName=user'], 'Model command substitution failed.');
        expect($calls[1]['arguments'] === ['gii/model', '--tableName=profile'], 'The second entity was not processed.');
        expect(strcasecmp($calls[0]['php'], PHP_BINARY) === 0, 'The child used a different PHP runtime.');
    },
    'gii: a failed model command propagates its status and stops the batch' => function (): void {
        [$status, $calls, $output] = runGiiConsole('gii-model', 'bad,later');
        expect($status === 23, 'The outer CLI hid the child failure.');
        expect(count($calls) === 1, 'Generation continued after a failed command.');
        expect(strpos($output, 'Controlled child failure') !== false, 'The child error was hidden.');
    },
    'gii: CRUD failure propagates to the outer CLI' => function (): void {
        [$status, $calls] = runGiiConsole('gii-crud', 'user_profile');
        expect($status === 23, 'CRUD failure was reported as success.');
        expect($calls[0]['arguments'] === ['gii/crud', '--modelClass=UserProfile', '--viewPath=@backend/views/user-profile-crud'], 'CRUD command substitution failed.');
    },
];
