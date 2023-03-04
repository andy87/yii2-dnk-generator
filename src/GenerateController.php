<?php

namespace andy87\dnk;

use common\components\Part;
use Yii;
use Exception;
use yii\helpers\Inflector;
use yii\console\Controller;
use yii\helpers\BaseConsole;

/**
 *  Class for generate files:
 *
 */
class GenerateController extends Controller
{
    //const

    /** @var string Base class for `Migration` */
    public const BASE_MIGRATE_CLASS = 1;

    /** @var string Base class for `Controller` */
    public const BASE_CONTROLLER_CLASS = 2;

    /** @var string Base class for `Model` */
    public const BASE_MODEL_CLASS = 3;

    /** @var string Base class for `Service` */
    public const BASE_SERVICE_CLASS = 4;

    /** @var string Base class for `Resource` */
    public const BASE_RESOURCE_CLASS = 5;


    /** @var string Key for `command` */
    public const COMMAND_MODEL = 1;

    /** @var string Key for `command` */
    public const COMMAND_CRUD = 2;


    /** @var int mapping key */
    private const SOURCE = 1;

    /** @var int mapping key */
    private const TARGET = 2;


    /** @var array mapping for action `gen` */
    private const MAPPING = [
        'migration' => [
            self::SOURCE => 'console/migrations/migration.tpl',
            self::TARGET => "@console/migrations/{{migrateFileName}}.php"
        ],
            'common-model-source' => [
                self::SOURCE => 'common/models/sources/common-model-source.tpl',
                self::TARGET => "@common/models/sources/{{CamelCase}}Source.php"
            ],
            'common-model-item' => [
                self::SOURCE => 'common/models/items/common-model-item.tpl',
                self::TARGET => "@common/models/items/{{CamelCase}}.php"
            ],
                'common-service' => [
                    self::SOURCE => 'common/services/common-service.tpl',
                    self::TARGET => "@common/services/{{CamelCase}}Service.php"
                ],
        'backend-model-item' => [
            self::SOURCE => 'backend/models/items/backend-model-item.tpl',
            self::TARGET => "@backend/models/items/{{CamelCase}}.php"
        ],
        'backend-model-form' => [
            self::SOURCE => 'backend/models/forms/backend-model-form.tpl',
            self::TARGET => "@backend/models/forms/{{CamelCase}}Form.php"
        ],
            'backend-service' => [
                self::SOURCE => 'backend/services/backend-service.tpl',
                self::TARGET => "@backend/services/{{CamelCase}}Service.php"
            ],
                'backend-create-resource' => [
                    self::SOURCE => 'backend/resources/model/backend-create-resource.tpl',
                    self::TARGET => "@backend/resources/{{snake_case}}/{{CamelCase}}CreateResource.php"
                ],
                'backend-list-resource' => [
                    self::SOURCE => 'backend/resources/model/backend-list-resource.tpl',
                    self::TARGET => "@backend/resources/{{snake_case}}/{{CamelCase}}ListResource.php"
                ],
                'backend-update-resource' => [
                    self::SOURCE => 'backend/resources/model/backend-update-resource.tpl',
                    self::TARGET => "@backend/resources/{{snake_case}}/{{CamelCase}}UpdateResource.php"
                ],
                'backend-read-resource' => [
                    self::SOURCE => 'backend/resources/model/backend-read-resource.tpl',
                    self::TARGET => "@backend/resources/{{snake_case}}/{{CamelCase}}ReadResource.php"
                ],
                    'backend-model-controller' => [
                        self::SOURCE => 'backend/controllers/cruds/backend-model-controller.tpl',
                        self::TARGET => "@backend/controllers/{{CamelCase}}Controller.php"
                    ],
                        'backend-views-form' => [
                            self::SOURCE => 'backend/views/model/_form-model.tpl',
                            self::TARGET => "@backend/views/{{snake_case}}/_form-{{snake_case}}.php"
                        ],
                        'backend-views-create' => [
                            self::SOURCE => 'backend/views/model/model-create.tpl',
                            self::TARGET => "@backend/views/{{snake_case}}/{{snake_case}}-create.php"
                        ],
                        'backend-views-list' => [
                            self::SOURCE => 'backend/views/model/model-list.tpl',
                            self::TARGET => "@backend/views/{{snake_case}}/{{snake_case}}-list.php"
                        ],
                        'backend-views-read' => [
                            self::SOURCE => 'backend/views/model/model-read.tpl',
                            self::TARGET => "@backend/views/{{snake_case}}/{{snake_case}}-read.php"
                        ],
                        'backend-views-update' => [
                            self::SOURCE => 'backend/views/model/model-update.tpl',
                            self::TARGET => "@backend/views/{{snake_case}}/{{snake_case}}-update.php"
                        ],
        'frontend-model-item' => [
            self::SOURCE => 'frontend/models/items/frontend-model-item.tpl',
            self::TARGET => "@frontend/models/items/{{CamelCase}}.php"
        ],
        'frontend-model-form' => [
            self::SOURCE => 'frontend/models/forms/frontend-model-form.tpl',
            self::TARGET => "@frontend/models/forms/{{CamelCase}}Form.php"
        ],
            'frontend-service' => [
                self::SOURCE => 'frontend/services/frontend-service.tpl',
                self::TARGET => "@frontend/services/{{CamelCase}}Service.php"
            ],
                'frontend-list-resource' => [
                    self::SOURCE => 'frontend/resources/model/frontend-list-resource.tpl',
                    self::TARGET => "@frontend/resources/{{snake_case}}/{{CamelCase}}ListResource.php"
                ],
                'frontend-read-resource' => [
                    self::SOURCE => 'frontend/resources/model/frontend-read-resource.tpl',
                    self::TARGET => "@frontend/resources/{{snake_case}}/{{CamelCase}}ReadResource.php"
                ],
                    'frontend-model-controller' => [
                        self::SOURCE => 'frontend/controllers/frontend-model-controller.tpl',
                        self::TARGET => "@frontend/controllers/{{CamelCase}}Controller.php"
                    ],
                        'frontend-views-list' => [
                            self::SOURCE => 'frontend/views/model/model-list.tpl',
                            self::TARGET => "@frontend/views/{{snake_case}}/{{snake_case}}-list.php"
                        ],
                        'frontend-views-read' => [
                            self::SOURCE => 'frontend/views/model/model-read.tpl',
                            self::TARGET => "@frontend/views/{{snake_case}}/{{snake_case}}-read.php"
                        ],
    ];

    /** @var array mapping for `resources` */
    public const BASE_RESOURCES = [
        'backend-components-controller' => [
            self::SOURCE => 'backend/components/controllers/BackendController.tpl',
            self::TARGET => "@backend/components/controllers/BackendController.php"
        ],
        'frontend-components-controller' => [
            self::SOURCE => 'frontend/components/controllers/FrontendController.tpl',
            self::TARGET => "@frontend/components/controllers/FrontendController.php"
        ],
        'common-components-resources-crud-create' => [
            self::SOURCE => 'common/components/resources/crud/CreateResource.tpl',
            self::TARGET => "@common/components/resources/crud/CreateResource.php"
        ],
        'common-components-resources-crud-list' => [
            self::SOURCE => 'common/components/resources/crud/ListResource.tpl',
            self::TARGET => "@common/components/resources/crud/ListResource.php"
        ],
        'common-components-resources-crud-read' => [
            self::SOURCE => 'common/components/resources/crud/ReadResource.tpl',
            self::TARGET => "@common/components/resources/crud/ReadResource.php"
        ],
        'common-components-resources-crud-update' => [
            self::SOURCE => 'common/components/resources/crud/UpdateResource.tpl',
            self::TARGET => "@common/components/resources/crud/UpdateResource.php"
        ]
    ];



    // Property

    /** @var string path to project root (setup from config) */
    public string $root = '';

    /** @var array Commands */
    public array $command = [
        self::COMMAND_MODEL => null,
        self::COMMAND_CRUD => null,
    ];

    /** @var array Список таблиц */
    public array $parts = [];

    /** @var array bases classname for generated files (setup from config) */
    public array $baseClasses = [
        self::BASE_MIGRATE_CLASS => null,
        self::BASE_CONTROLLER_CLASS => null,
        self::BASE_MODEL_CLASS => null,
        self::BASE_SERVICE_CLASS => null,
        self::BASE_RESOURCE_CLASS => null,
    ];



    // Methods


    /**
     * Copy directory with based classes (call once)
     *
     * example:
     *      php yii generate/setup
     *
     * @param bool $overwrite
     * @return void
     * @throws Exception
     */
    public function actionSetup( bool $overwrite = false ): void
    {
        $root = $this->getTemplatePath();

        $this->copyDirectoryStructure(
            "common/components/interfaces/",
            Yii::getAlias('@common/components/interfaces/')
        );

        $this->copyDirectoryStructure(
            "common/components/resources/",
            Yii::getAlias('@common/components/resources/')
        );

        $copyFiles = [
            'common-base-model' => [
                self::SOURCE => 'common/components/core/common-base-model.tpl',
                self::TARGET => '@common/components/core/BaseModel.php'
            ],
            'common-components-db-table' => [
                self::SOURCE => 'common/components/db/Tables.php',
                self::TARGET => '@common/components/db/Tables.php'
            ],
            'common-components-parts' => [
                self::SOURCE => 'common/components/Part.php',
                self::TARGET => '@common/components/Part.php'
            ],
        ];

        foreach ( $copyFiles as $template )
        {
            $sourcePath = $root.$template[self::SOURCE];

            echo "\r\n Generate file: $sourcePath.";

            $targetPath = Yii::getAlias($template[self::TARGET]);

            $this->createDirectories($targetPath);

            $this->copy($sourcePath, $targetPath);
        }

        $params = $this->getParams('model','Model');

        foreach ( self::BASE_RESOURCES as $template )
        {
            $sourcePath = $root.$template[self::SOURCE];

            echo "\r\n Generate file: $sourcePath.";

            $targetPath = Yii::getAlias($template[self::TARGET]);

            if ( file_exists($targetPath) )
            {
                if ($overwrite)
                {
                    unlink($targetPath);
                    $this->stdout("\r\n\t overwrite file.", BaseConsole::FG_RED);

                } else {

                    $this->stdout("\r\n\t copied skip (file exist) .\n", BaseConsole::FG_RED);
                    continue;
                }
            }

            $status = $this->generateFileFromTpl($sourcePath, $targetPath, $params);

            if ($status) {

                $this->stdout("\r\n\t copied successfully.\n", BaseConsole::FG_GREEN);

            } else {

                $this->stdout("\r\n\r copied failed.\n", BaseConsole::FG_RED);
            }
        }

        echo "\r\n Dnk `setup`: copied finish.";
    }

    /**
     * Generate new all files for entity
     *
     * example:
     *      php yii generate/gen user
     *
     * @param string $entity
     * @param bool $overwrite
     * @return void
     */
    public function actionGen(string $entity, bool $overwrite = false ): void
    {
        $entityList = $this->getEntity($entity);

        foreach ($entityList as $entity)
        {
            $this->generator($entity, '', $overwrite);
        }
    }

    /**
     * @param string $entity
     * @return array
     */
    private function getEntity(string $entity): array
    {
        if ( strpos($entity, ',') !== false )
        {
            $entityList = explode(',', $entity);

        } else {

            $entityList = [$entity];
        }

        if ( $entity === '*' ) $entityList = $this->parts;

        return $entityList;
    }

    /**
     * Generate some files for entity
     *
     * example:
     *      php yii generate/gen user common-model-source,backend-model-form,frontend-model-form
     *
     * @param string $entity
     * @param string $filter
     * @return void
     */
    public function actionList(string $entity, string $filter): void
    {
        $this->generator($entity, $filter );
    }

    /**
     * Generate services files for entity
     *
     * example:
     *      php yii generate/gen-services user
     *
     * @param string $entity
     * @return void
     */
    public function actionGenServices(string $entity)
    {
        $this->actionList($entity, 'common-service,backend-service,frontend-service');
    }

    /**
     * Generate services files for entity
     *
     * example:
     *      php yii generate/gen-controllers user
     *
     * @param string $entity
     * @return void
     */
    public function actionGenControllers(string $entity)
    {
        $this->actionList($entity, 'backend-model-controller,frontend-model-controller');
    }

    /**
     * Generate services files for entity
     *
     * example:
     *      php yii generate/gen-models user
     *
     * @param string $entity
     * @return void
     */
    public function actionGenModels(string $entity)
    {
        $this->actionList($entity, 'common-model-source,common-model-item,backend-model-item,backend-model-form,frontend-model-item,frontend-model-form');
    }

    /**
     * Generate backend/views files for entity
     *
     * example:
     *      php yii generate/gen-backend-views user
     *
     * @param string $entity
     * @return void
     */
    public function actionGenBackendViews(string $entity)
    {
        $this->actionList($entity, 'backend-views-form,backend-views-create,backend-views-list,backend-views-read,backend-views-update');
    }

    /**
     * Generate frontend/views files for entity
     *
     * example:
     *      php yii generate/gen-frontend-views user
     *
     * @param string $entity
     * @return void
     */
    public function actionGenFrontendViews(string $entity)
    {
        $this->actionList($entity, 'frontend-views-list,frontend-views-read');
    }

    /**
     * @return void
     */
    public function actionSourceModels(string $entity)
    {
        $this->generateGii($entity, 'php yii gii/model ' . $this->command[ self::COMMAND_MODEL ]);
    }

    /**
     * @return void
     */
    public function actionSourceCruds(string $entity)
    {
        $this->generateGii($entity, 'php yii gii/crud ' . $this->command[ self::COMMAND_CRUD ]);
    }

    /**
     * @param string $entity
     * @param string $sourceCommand
     * @return void
     */
    private function generateGii(string $entity, string $sourceCommand)
    {
        $entityList = $this->getEntity($entity);

        foreach ($entityList as $entity )
        {
            $snakeCase = strtolower($entity);
            $camelCase = Inflector::id2camel($snakeCase,'_');

            $params = $this->getParams($snakeCase, $camelCase);

            $from = array_keys($params);
            $to = array_values($params);


            $command = str_replace($from, $to, $sourceCommand);

            echo "\r\nEntity: $camelCase table: $snakeCase";
            echo "\r\nCommand: \r\n\t $command";
            echo "\r\n\tresult: " . shell_exec( $command );
        }
    }

    /**
     * generator core
     *
     * @param string $entity
     * @param string $filter
     * @param bool $overwrite
     * @return void
     */
    private function generator(string $entity, string $filter = '', bool $overwrite = false): void
    {
        $root = $this->getTemplatePath();

        $filter = ( strlen($filter) ) ? explode(',', $filter) : [];

        $snakeCase = strtolower($entity);
        $camelCase = Inflector::id2camel($snakeCase,'_');

        $params = $this->getParams($snakeCase, $camelCase);

        $templatesMap = [];

        $from = array_keys($params);
        $to = array_values($params);

        foreach (self::MAPPING as $item => $data )
        {
            if ( !empty($filter) && !in_array($item, $filter) ) continue;

            $key = $data[self::SOURCE];
            $value = $data[self::TARGET];

            $templatesMap[$key] = str_replace($from, $to, $value);
        }

        $migrationStep = true;

        foreach ($templatesMap as $sourcePath => $targetPath)
        {
            if ( $migrationStep )
            {
                $migrationStep = false;

                $pathFiles = glob(Yii::getAlias('@console/migrations/*_create_table_*.php'));

                foreach ( $pathFiles as $pathFile )
                {
                    if ( strpos($pathFile, "_create_table__$snakeCase") !== false )
                    {
                        continue 2;
                    }
                }
            }

            $sourcePath = $root.$sourcePath;
            $targetPath = Yii::getAlias($targetPath);


            echo "\r\n\r\nNew generate iteration $camelCase \r\n Template: ";
            $this->stdout($sourcePath, BaseConsole::FG_CYAN);
            echo "\r\n Generated file: ";
            $this->stdout($targetPath, BaseConsole::FG_GREEN);


            if ( file_exists($targetPath) )
            {
                if ( $overwrite )
                {
                     unlink($targetPath);

                }  else {

                    $this->stdout("\r\n File exists!", BaseConsole::FG_RED);

                    echo "\r\nEnter `yes` if you want reGenerate this file:";

                    while (true)
                    {
                        $input = BaseConsole::stdin('');

                        if ($input === 'yes')
                        {
                            unlink($targetPath);
                            break;

                        } else {

                            $this->stdout("\r\nDnk `setup`: copied skipped.\n", BaseConsole::FG_RED);

                            continue 2;
                        }
                    }
                }
            }

            $status = $this->generateFileFromTpl($sourcePath, $targetPath, $params);

            if ($status) {

                $this->stdout("\r\n Dnk `setup`: copied successfully.\n", BaseConsole::FG_GREEN);

            } else {

                $this->stdout("\r\n Dnk `setup`: copied failed.\n", BaseConsole::FG_RED);
            }
        }
    }

    /**
     * @param string $snakeCase
     * @param string $camelCase
     * @return array
     */
    private function getParams( string $snakeCase, string $camelCase): array
    {
        $timestamp = date('ymd_his');
        $migrateFileName = "m{$timestamp}__create_table__$snakeCase" ;

        $params = [
            '{{migrateFileName}}' => $migrateFileName,
            '{{snake_case}}' => $snakeCase,
            '{{UPPER_CASE}}' => strtoupper($snakeCase),
            '{{CamelCase}}' => $camelCase,
        ];

        $from = array_keys($params);
        $to = array_values($params);

        $params['{{BaseMigrationClassName}}'] = str_replace($from, $to, $this->baseClasses[self::BASE_MIGRATE_CLASS]);
        $params['{{BaseModelClassName}}'] =str_replace($from, $to, $this->baseClasses[self::BASE_MODEL_CLASS]);
        $params['{{BaseControllerClassName}}'] =str_replace($from, $to, $this->baseClasses[self::BASE_CONTROLLER_CLASS]);
        $params['{{BaseServiceClassName}}'] =str_replace($from, $to, $this->baseClasses[self::BASE_SERVICE_CLASS]);
        $params['{{BaseResourceClassName}}'] =str_replace($from, $to, $this->baseClasses[self::BASE_RESOURCE_CLASS]);

        return $params;
    }

    /**
     * Copy directory structure
     *
     * @throws Exception
     */
    private function copyDirectoryStructure(string $root, string $target, bool $overwrite = false)
    {
        if (!is_dir($root)) throw new Exception('Директория-источник не найдена: ' . $root);

        if (!is_dir($target)) mkdir($target, 0777, true);

        $dir = opendir($root);

        while (($file = readdir($dir)) !== false)
        {
            if ($file == '.' || $file == '..') continue;

            $source = $root . '/' . $file;
            $destination = $target . '/' . $file;

            if (is_dir($source))
            {
                $this->copyDirectoryStructure($source, $destination, $overwrite);

            } else {

                $status = $this->copy($source, $destination, $overwrite);

                if ( $status === null ) continue;
            }
        }

        closedir($dir);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param bool $overwrite
     * @return ?bool
     */
    private function copy(string $source, string $destination, bool $overwrite = false): ?bool
    {
        echo "\r\n Copy file: $source.";

        if ( file_exists($destination) )
        {
            if ( $overwrite )
            {
                unlink($destination);

            } else {

                $this->stdout("\r\n\t file exists.\n", BaseConsole::FG_PURPLE);

                return null;
            }
        }

        if (copy($source, $destination)) {
            $this->stdout("\r\n\t successfully.\n", BaseConsole::FG_GREEN);

            return true;
        } else {

            $this->stdout("\r\n\t failed.\n", BaseConsole::FG_RED);

            return false;
        }
    }

    /**
     * Get template path
     *
     * @return string
     */
    private function getTemplatePath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    }


    /**
     * Generate file from `tpl`
     *
     * @param string $sourcePath
     * @param string $targetPath
     * @param array $params
     * @return bool
     */
    private function generateFileFromTpl(string $sourcePath, string $targetPath, array $params = []): bool
    {
        $content = file_get_contents($sourcePath);

        foreach ($params as $mark => $value) {
            $content = str_replace($mark, $value, $content);
        }

        return $this->createFile($targetPath, $content);
    }


    /**
     * Create file
     *
     * @param $filename
     * @param $content
     * @return bool
     */
    private function createFile($filename, $content): bool
    {
        $this->createDirectories($filename);

        if (!file_exists($filename)) {
            $dirname = dirname($filename);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0755, true);
            }
            return (bool) file_put_contents($filename, $content);
        }

        return false;
    }

    /**
     * Create directories if not exist
     *
     * @param $path
     * @return void
     */
    private function createDirectories($path): void
    {
        $path = str_replace(['\\','/'], '/', $path);

        $dir = '';

        $pathInfo = pathinfo($path);
        $mapping = explode('/', $pathInfo['dirname']);

        foreach ($mapping as $step)
        {
            $dir .= ($step . DIRECTORY_SEPARATOR);

            if (!is_dir($dir)) mkdir($dir);
        }
    }
}