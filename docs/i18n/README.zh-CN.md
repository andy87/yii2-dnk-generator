# Yii2 DNK Generator

<a id="languages"></a>
## 语言

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | **简体中文** | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[语言选择依据与翻译维护规则](../../docs/i18n/README.md)。

DNK 为 **Yii 2 Advanced** 生成应用代码，并提供这些代码使用的基类，包括模型、表单、搜索模型、服务、控制器、资源、视图、迁移和测试骨架。模板位于 [src/templates](../../src/templates)，运行时类位于 [src/source](../../src/source)。本指南对应当前 `master` 分支，旧版本的行为可能不同。

<a id="contents"></a>
## 目录

- [环境要求](#requirements)
- [快速开始](#quick-start)
- [命令与配置](#commands)
- [生成文件与架构](#generated-files)
- [服务与资源](#api)
- [控制器扩展与表单场景](#extension)
- [访问控制、路由与 JSON](#security)
- [数据库迁移](#migrations)
- [重新生成与升级](#upgrades)
- [测试](#tests)
- [故障排查](#troubleshooting)

<a id="requirements"></a>
## 环境要求

- PHP **8.0 或更高版本**、Composer，以及所安装 Yii 版本要求的 PHP 扩展。
- 已初始化的 Yii 2 Advanced 应用；`@common`、`@backend`、`@frontend`、`@console` 别名、数据库连接和 Web 静态资源均已配置。
- 使用 `gii-model`、`gii-crud` 时，需要安装 `yiisoft/yii2-gii` 并在**控制台应用**中启用。
- 默认迁移针对 **MySQL**，包含其字符集、排序规则、InnoDB 和 `ON UPDATE CURRENT_TIMESTAMP`。使用其他数据库前，必须调整迁移基类和时间戳定义。SQLite 回归测试不能证明这些默认迁移可跨数据库运行。

<a id="quick-start"></a>
## 快速开始：Book 实体

在包含 `yii` 文件的 Yii Advanced 应用根目录执行命令。本例假定创建新表 `book`，且已配置开发数据库。

### 1. 安装与配置

直接从仓库安装本指南对应的分支：

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

将以下配置合并到 `console/config/main.php`，保留应用现有配置。`command` 保存 Gii 参数，而不是完整的 shell 命令。参数和实体名称应由开发者控制。

```php
use andy87\dnk\GenerateController;

return [
    'controllerMap' => [
        'dnk' => [
            'class' => GenerateController::class,
            'entityList' => ['book'],
            'command' => [
                GenerateController::COMMAND_MODEL =>
                    '--tableName={{snake_case}} --modelClass={{CamelCase}}Source '
                    . '--ns=' . escapeshellarg('common\models\sources') . ' '
                    . '--baseClass=' . escapeshellarg('common\components\core\BaseModel') . ' '
                    . '--interactive=0 --overwrite=1',
                GenerateController::COMMAND_CRUD =>
                    '--modelClass=' . escapeshellarg('backend\models\items\{{CamelCase}}') . ' '
                    . '--searchModelClass=' . escapeshellarg('backend\models\search\{{CamelCase}}Search') . ' '
                    . '--controllerClass=' . escapeshellarg('backend\controllers\{{CamelCase}}CrudController') . ' '
                    . '--baseControllerClass=' . escapeshellarg('yii\web\Controller') . ' '
                    . '--viewPath=@backend/views/{{kebab-case}}-crud '
                    . '--interactive=0 --overwrite=0',
            ],
        ],
    ],
];
```

如果尚未启用 Gii，在开发环境的控制台配置中、返回 `$config` 前添加：

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. 生成公共类并注册实体

```sh
php yii dnk/setup
```

在 `common\components\Entity` 类内部添加此常量，保留现有常量：

```php
public const BOOK = 'book';
```

在 `common\components\db\Tables::NAMES` 中添加：

```php
Entity::BOOK => 'book',
```

在 `Entity::DATA` 中添加可读名称：

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

以上片段应插入现有类或数组，不是独立的 PHP 文件。名称可省略：`getLabelOne()` 和 `getLabelMany()` 会回退到已注册的表名。未知实体没有有效名称或表配置时，会抛出 `InvalidConfigException`。

### 3. 生成代码、扩展表结构并运行 Gii

```sh
php yii dnk/run book
```

初始的 `BookSource` 只是占位类。使用模型前，编辑新生成的 `console/migrations/m…_create_table__book.php`，在 `constructTable()` 的 `id` 和时间戳字段旁加入以下字段，然后检查并执行待处理迁移：

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii 将 `common/models/sources/BookSource.php` 替换为包含表结构、验证规则和标签的 ActiveRecord。自定义领域逻辑应放在 item/form/service 类中，避免重新生成 source 时被覆盖。对于现有表，应注册表名并选择性生成代码，不要对其执行新的建表迁移。

调整 `backend/views/book/book-list.php` 和 `frontend/views/book/book-list.php`：默认列表使用示例列 `parent_id`、`name`、`url`、`category_image`，并非根据表结构生成。将 `columns` 中的属性项替换为以下内容，同时保留已有的 `SerialColumn` 和 `ActionColumn`，以保持链接可用：

```php
'id',
 'title',
```

在 `backend/views/book/book-read.php` 和 `frontend/views/book/book-read.php` 中设置 DetailView 属性：

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. 配置访问权限并打开页面

在生成的 `backend\controllers\BookController` 中添加：

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

配置 Yii `authManager`，创建 `manageBooks` 角色或权限，并分配给目标账户。DNK 不创建账户或 RBAC 授权。使用该账户登录。

默认查询参数 URL 下，在 backend Web 根路径打开 `index.php?r=book/list`，然后使用 `book/create`、`book/read&id=1`、`book/update&id=1`，其中 ID 必须存在。Frontend 提供 `book/list` 和 `book/read&id=1`。实际路径由应用 URL 管理器决定。

<a id="commands"></a>
## 命令与配置

`entity` 支持 `book`、`book,author`，或带引号的 `"*"`，后者展开为 `entityList` 的所有条目。使用单数 snake_case 名称，例如 `book_review` 对应类 `BookReview` 和控制器路由 `book-review`。可选的位置参数 `overwrite` 是布尔值，默认 `0`，`1` 表示替换文件。Gii 使用独立的选项。

| `php yii` 后的命令 | 作用 |
| --- | --- |
| `dnk/setup [overwrite]` | 生成应用公共类，不接收实体参数。 |
| `dnk/run entity [overwrite]` | 生成下表中的所有实体文件，不包含 setup 公共类、测试或 Gii 执行。 |
| `dnk/list entity map [overwrite]` | 仅生成以逗号分隔的映射键对应的文件。 |
| `dnk/gen-models entity [overwrite]` | 生成各应用层对应的 source、item、form、search 模型。 |
| `dnk/gen-services entity [overwrite]` | 生成 common、backend、frontend 服务。 |
| `dnk/gen-controllers entity [overwrite]` | 生成 backend、frontend 控制器。 |
| `dnk/gen-backend-views entity [overwrite]` | 生成 backend 表单、新建、更新、查看和列表视图。 |
| `dnk/gen-frontend-views entity [overwrite]` | 生成 frontend 列表和查看视图。 |
| `dnk/gen-tests entity [overwrite]` | 生成八个 Codeception 单元测试骨架。 |
| `dnk/gii-model entity` | 对每个实体执行配置的 `gii/model`。 |
| `dnk/gii-crud entity` | 对每个实体执行配置的 `gii/crud`。 |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` 是可选的，DNK 页面已有自己的资源和视图。示例 Gii 配置使用 `yii\web\Controller` 创建独立的 `BookCrudController`，视图位于 `backend/views/book-crud`。Gii 使用 `index/view/create/update/delete`，DNK 使用 `list/read/create/update`，不提供删除操作。Gii 控制器需要单独配置访问策略。Gii 子进程失败后会停止实体循环，并返回其非零退出码。

| 生成器属性 | 用途 |
| --- | --- |
| `entityList` | `"*"` 展开的实体列表，默认空数组。 |
| `command` | 以 `COMMAND_MODEL`、`COMMAND_CRUD` 为键的参数字符串；执行对应 Gii 命令前必须配置。 |
| `baseClasses` | 以下常量对应的可选部分覆盖，未配置项保留默认值；无效类名在生成前被拒绝。 |

| `GenerateController` 常量 | 默认类 |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

模板和 Gii 标记：`{{snake_case}}`、`{{CamelCase}}`、`{{UPPER_CASE}}`、`{{kebab-case}}`、`{{migrateFileName}}`、`{{BaseMigrationClassName}}`、`{{BaseModelClassName}}`、`{{BaseControllerClassName}}`、`{{BaseServiceClassName}}`、`{{BaseResourceClassName}}`。可定制生成的文件或 `baseClasses`；没有公开的 `root`、`parts` 配置。模板随包提供。

<a id="generated-files"></a>
## 生成文件与架构

调用流程为**控制器 → 服务 → ActiveRecord/表单**，展示流程为**资源 → 视图**。`BaseController` 通过 Yii 容器实例化 `SERVICE` 指定的类。公共 source 模型保存 Gii 输出；公共 item 模型继承 source；backend/frontend item 模型增加各自行为。表单继承对应的 item 模型。Backend/frontend 服务创建表单，common 服务创建公共 item 模型。

搜索模型根据表结构生成可选过滤规则。`MODEL` 指定 item 类；`search()` 和 `findWhere()` 通过 `andWhere`/`andFilterWhere` 保留该类 `find()` 中的限制。应在相关 item/form 查询中定义可见性规则，生成器不会自动实现应用专属的授权策略。

`dnk/setup` 创建以下文件；`@` 表示 Yii 别名：

```text
@common/components/Entity.php
@common/components/db/Setup.php
@common/components/db/Tables.php
@common/components/core/BaseModel.php
@backend/components/controllers/BackendController.php
@frontend/components/controllers/FrontendController.php
@common/components/resources/crud/CreateResource.php
@common/components/resources/crud/ListResource.php
@common/components/resources/crud/ReadResource.php
@common/components/resources/crud/UpdateResource.php
```

`BaseService`、`BaseResource`、`BaseController`、`BaseMigration`、`GridViewResource`、`MutationResource` 和接口仍保留在包中；setup 不会将其复制到 `common/components`。

完整的 `dnk/list` 映射也对应 `dnk/run` 的输出：

| 映射键 | 目标路径模板 |
| --- | --- |
| `migration` | `@console/migrations/{{migrateFileName}}.php` |
| `common-model-source` | `@common/models/sources/{{CamelCase}}Source.php` |
| `common-model-item` | `@common/models/items/{{CamelCase}}.php` |
| `common-model-search` | `@common/models/search/{{CamelCase}}Search.php` |
| `common-service` | `@common/services/{{CamelCase}}Service.php` |
| `backend-model-item` | `@backend/models/items/{{CamelCase}}.php` |
| `backend-model-search` | `@backend/models/search/{{CamelCase}}Search.php` |
| `backend-model-form` | `@backend/models/forms/{{CamelCase}}Form.php` |
| `backend-service` | `@backend/services/{{CamelCase}}Service.php` |
| `backend-create-resource` | `@backend/resources/{{snake_case}}/{{CamelCase}}CreateResource.php` |
| `backend-list-resource` | `@backend/resources/{{snake_case}}/{{CamelCase}}ListResource.php` |
| `backend-update-resource` | `@backend/resources/{{snake_case}}/{{CamelCase}}UpdateResource.php` |
| `backend-read-resource` | `@backend/resources/{{snake_case}}/{{CamelCase}}ReadResource.php` |
| `backend-model-controller` | `@backend/controllers/{{CamelCase}}Controller.php` |
| `backend-views-form` | `@backend/views/{{kebab-case}}/_form-{{snake_case}}.php` |
| `backend-views-create` | `@backend/views/{{kebab-case}}/{{snake_case}}-create.php` |
| `backend-views-list` | `@backend/views/{{kebab-case}}/{{snake_case}}-list.php` |
| `backend-views-read` | `@backend/views/{{kebab-case}}/{{snake_case}}-read.php` |
| `backend-views-update` | `@backend/views/{{kebab-case}}/{{snake_case}}-update.php` |
| `frontend-model-item` | `@frontend/models/items/{{CamelCase}}.php` |
| `frontend-model-search` | `@frontend/models/search/{{CamelCase}}Search.php` |
| `frontend-model-form` | `@frontend/models/forms/{{CamelCase}}Form.php` |
| `frontend-service` | `@frontend/services/{{CamelCase}}Service.php` |
| `frontend-list-resource` | `@frontend/resources/{{snake_case}}/{{CamelCase}}ListResource.php` |
| `frontend-read-resource` | `@frontend/resources/{{snake_case}}/{{CamelCase}}ReadResource.php` |
| `frontend-model-controller` | `@frontend/controllers/{{CamelCase}}Controller.php` |
| `frontend-views-list` | `@frontend/views/{{kebab-case}}/{{snake_case}}-list.php` |
| `frontend-views-read` | `@frontend/views/{{kebab-case}}/{{snake_case}}-read.php` |

`dnk/gen-tests` 创建：

```text
@common/tests/unit/services/{{CamelCase}}ServiceTest.php
@common/tests/unit/models/items/{{CamelCase}}Test.php
@backend/tests/unit/services/{{CamelCase}}ServiceTest.php
@backend/tests/unit/models/items/{{CamelCase}}Test.php
@backend/tests/unit/models/forms/{{CamelCase}}Test.php
@frontend/tests/unit/services/{{CamelCase}}ServiceTest.php
@frontend/tests/unit/models/items/{{CamelCase}}Test.php
@frontend/tests/unit/models/forms/{{CamelCase}}Test.php
```

<a id="api"></a>
## 服务与资源

完成 Book 快速开始后，可以使用以下生成类：

```php
$service = new backend\services\BookService();
$book = $service->create(['title' => 'DNK guide']);
if ($book->hasErrors()) {
    $errors = $book->getErrors();
}

$draft = $service->create(['BookForm' => ['title' => 'Draft']], false);
$found = $service->findWhere(['id' => $book->id])->one();

$form = backend\models\forms\BookForm::findOne($book->id);
if ($form === null) {
    throw new yii\web\NotFoundHttpException('Book not found.');
}
$updated = $service->update($form, ['title' => 'Updated title']);
```

生成的 common 服务只配置 `MODEL`。使用表单或资源 API 时，应选择 backend/frontend 服务，或显式配置 `MODEL_FORM` 和 `RESOURCES`。

`create(array $params = [], bool $is_save = true)` 和 `update($model, array $params = [], bool $is_save = true)` 返回模型，而不是成功标志。应检查 `hasErrors()` / `getErrors()` 并处理持久化失败；数据库异常可能向上传播。传入 `false` 会跳过 `save()` 及其自动验证，需要时显式调用 `validate()`。更新示例假定创建成功且记录存在。

两者都支持平铺属性或以模型实际 `formName()` 为键的数据，只赋值当前场景的安全属性。表单键对应的值不是数组时，抛出 `yii\base\InvalidArgumentException`。`createInScenario($params, $scenario, $is_save)` 在赋值前设置可信场景并委托给 `create()`，保留与基类实现兼容的重写逻辑。场景只影响本次调用。

| API | 用途 |
| --- | --- |
| `getClassModel()` / `getClassForm()` | 返回配置的模型/表单类名。 |
| `getModel($params)` / `getForm($params)` | 实例化配置类；`$params` 是构造参数，不是属性数据。 |
| `getEntity()` | 返回公共 item 模型，或 backend/frontend 服务的表单。 |
| `findWhere($criteria)` | 返回 `ActiveQuery`，可调用 `one()`、`all()` 等；不存在 `findByID()`。 |
| `getResource($key, $params)` | 使用构造参数创建 `RESOURCES` 中的类；未映射的键返回 `null`。 |

资源键为 `BaseService::CREATE`、`READ`、`UPDATE`、`LIST`。Frontend 仅映射 `LIST`、`READ`；`FORM` 常量没有默认资源。查看资源必须接收现有记录；下例中的 `$controller` 是当前 Web 控制器，`$found` 不能为 `null`：

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` 返回 `['R' => $resource]`。`render($controller, $template = null, $params = [])` 渲染 `TEMPLATE`，并将 `$R` 传给视图。`GridViewResource($searchForm, $activeDataProvider)` 携带列表的搜索模型和数据提供器。`json($params = [])` 设置 JSON 响应格式，返回资源属性与 `$params` 的合并结果；它**不会**应用 frontend 的 `JSON_FIELDS` 允许列表。API 应显式构造公开数据。不存在 `content()` 方法。

<a id="extension"></a>
## 控制器扩展与表单场景

Backend 扩展点包括 `beforeList()`、`beforeCreate()`、`beforeRead(int $id)`、`beforeUpdate(int $id)`。`SEARCH_MODEL` 选择搜索模型，生成的控制器保留 `SEARCH` 作为兼容别名。`setTitle()` 和 `setBreadcrumbs()` 设置页面元数据。

`addMutation(new MutationResource($event, $action, $callback))` 定制 backend 资源的构造过程。`EVENT_RESOURCE_INIT` 的第一个回调接收构造参数并返回资源，后续回调接收该资源。`EVENT_RESOURCE_CREATED` 的回调修改已经创建的资源。**每个回调都必须返回资源。**

将下列方法加入 `backend\controllers\BookController`。先在 `BookForm::scenarios()` 中定义 `create` 场景和安全字段；场景名称必须由应用代码决定，不能直接来自 POST：

```php
/**
 * Select the trusted form scenario before loading POST data.
 *
 * @return void
 */
public function beforeCreate(): void
{
    parent::beforeCreate();
    $this->addMutation(new \andy87\dnk\source\resources\MutationResource(
        self::EVENT_RESOURCE_CREATED,
        \andy87\dnk\source\base\BaseService::CREATE,
        /**
         * @param \common\components\resources\crud\CreateResource $resource Form resource.
         * @return \common\components\resources\crud\CreateResource Resource in the chosen scenario.
         */
        static function ($resource) {
            $resource->item->setScenario('create');
            return $resource;
        }
    ));
}
```

Backend 创建记录时将表单场景传给 `createInScenario()`。生成的表单展示安全的标量属性，包括标量虚拟字段，并跳过自增列和 SQL 表达式默认值。创建时保留未赋值列的数据库默认值。数组、对象、关联和文件上传需要专门的控件与应用逻辑。

<a id="security"></a>
## 访问控制、路由与 JSON

Backend 默认拒绝访问：`accessRoles = []` 不授权任何人，访客始终被拒绝。应配置明确的 RBAC 角色或权限。`['@']` 会允许所有已登录用户，仅在策略确实如此时使用。Yii 可能将访客重定向到登录页面；已登录但无权限的用户收到 403。Frontend 默认为公开访问，除非应用添加限制。

DNK 的默认操作是 `list`。查看或更新不存在的记录返回 404。生成的 frontend `BookController` 重写了 `actionList()` / `actionRead()`，用于 HTML 和搜索过滤。单独增加 `?type=json` 不会启用基类的 JSON 行为。

要继承 HTML/JSON 操作，创建 `frontend/controllers/BookApiController.php`：

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` 返回 `items` 和 `totalCount`；`index.php?r=book-api/read&id=1&type=json` 返回 `item`。基类列表使用 item 查询和分页，不执行生成的 search 表单过滤逻辑。`JSON_FIELDS` 默认为 `['id']`；空数组不公开字段。Yii 模型的 `fields()` 限制仍然有效。不要将密码、令牌或私有字段加入允许列表。

<a id="migrations"></a>
## 数据库迁移

迁移继承 `BaseMigration`，设置 `$entity = Entity::…`，实现 `constructTable(): array`。`safeUp()` 在建表前解析名称，然后添加表注释；`safeDown()` 删除表。`Tables::NAMES` 提供表名，`Setup` 提供表选项和时间戳 SQL。执行和回滚前都应检查迁移。如果已有匹配的 `*_create_table__<entity>.php`，即使启用覆盖，也不会再生成建表迁移。

<a id="upgrades"></a>
## 重新生成与升级

| 操作 | 已有文件的处理方式 |
| --- | --- |
| `setup` / `gen-tests`，overwrite `0` | 直接跳过，不询问。 |
| `run` / `list` / 模型、服务、控制器、视图生成器，overwrite `0` | 逐文件询问，只有输入完整的 `yes` 才会替换。 |
| DNK overwrite `1` | 替换文件，不合并手工修改；仍跳过已有建表迁移。 |
| Gii 命令 | 使用 `command` 内的 `--interactive` 和 `--overwrite`，不接收 DNK 的 overwrite 参数。 |

重新生成前，先提交或备份自定义内容。替换操作对单个文件是原子的，多文件生成不是事务。Composer 升级会更新包内运行时类，不会自动更新应用中已经生成的文件。

现有应用应比较新模板与公共控制器、source/item/search/form、服务、资源和视图。选择性应用变更，设置 `accessRoles`，保留支持场景的 `create()` 委托逻辑，并检查表单与公开 JSON。不要用 Gii 覆盖手工维护的 source 类。DNK 和 Gii CRUD 的控制器、视图应相互独立。

<a id="tests"></a>
## 测试

从**本库的检出目录**执行，而非生成的应用目录：

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` 无需外部依赖，可检查本地链接、翻译章节、代码示例一致性以及生成器命令和文件映射。它也是 `test:all` 的第一步。

回归测试需要 `pdo_sqlite`，会用真实模板生成临时应用。可通过 `DNK_TEST_VENDOR_PATH` 指定外部 vendor 目录。Codeception 集成要求已安装兼容的运行器，或用 `DNK_TEST_CODECEPTION` 指定其 PHP 入口/PHAR；缺少运行器时检查失败。`test:all` 运行两套测试。详见 [tests/README.md](../../tests/README.md)。

`dnk/gen-tests` 则在目标应用的 common/backend/frontend 测试套件中生成文件。这些只是骨架，需要添加领域断言、夹具和套件配置。Actor 类型支持 `Codeception\Actor`，包括 `Support` 命名空间布局。生成测试不等于执行测试。

<a id="troubleshooting"></a>
## 故障排查

| 现象 | 检查项 |
| --- | --- |
| 找不到 `dnk` / `gii` 命令 | 控制台 `controllerMap`、Gii 安装和控制台模块配置。 |
| 未知属性 `parts` 或 `root` | 删除旧属性，改用 `entityList` 和 Yii 别名。 |
| Backend 跳转登录或返回 403 | 登录身份、明确的 `accessRoles`、`authManager` 授权。 |
| 查看/更新返回 404 | ID 是否存在、item/form 可见性规则和路由。 |
| `"*"` 对应空列表 | 填写 `entityList`，在 shell 中给星号加引号。 |
| 实体触发 `InvalidConfigException` | 实体常量、`Tables::NAMES` 条目和有效的可选名称。 |
| 缺少表、验证规则或表单字段 | 应用表结构、执行 `gii-model`、检查表单规则、场景与安全属性。 |
| 其他数据库出现迁移 SQL 错误 | 调整 MySQL 专用的基类、选项和时间戳。 |
| 请求 JSON 却收到 HTML | 检查具体 frontend 控制器是否重写基类操作。 |
| 找不到 Codeception 运行器 | 按 `tests/README.md` 配置集成环境。 |

包许可证为 MIT，见 [composer.json](../../composer.json)。
