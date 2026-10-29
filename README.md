# Yii2 DNK Generator

<a id="languages"></a>
## Languages

**English** | [Русский](docs/i18n/README.ru.md) | [Қазақша](docs/i18n/README.kk.md) | [简体中文](docs/i18n/README.zh-CN.md) | [Español](docs/i18n/README.es.md) | [Português (Brasil)](docs/i18n/README.pt-BR.md) | [Deutsch](docs/i18n/README.de.md) | [Français](docs/i18n/README.fr.md) | [日本語](docs/i18n/README.ja.md) | [Bahasa Indonesia](docs/i18n/README.id.md)

[Language selection and translation policy](docs/i18n/README.md).

DNK generates application code for **Yii 2 Advanced** and provides the base classes used by that code: models, forms, search models, services, controllers, resources, views, migrations and test skeletons. Templates are in [src/templates](src/templates); runtime classes are in [src/source](src/source). This guide describes the current `master` branch, which can differ from older releases.

<a id="contents"></a>
## Contents

- [Requirements](#requirements)
- [Quick start](#quick-start)
- [Commands and configuration](#commands)
- [Generated files and architecture](#generated-files)
- [Services and resources](#api)
- [Controller extension and form scenarios](#extension)
- [Access, routes and JSON](#security)
- [Migrations](#migrations)
- [Regeneration and upgrades](#upgrades)
- [Tests](#tests)
- [Troubleshooting](#troubleshooting)

<a id="requirements"></a>
## Requirements

- PHP **8.0 or newer**, Composer and the PHP extensions required by the installed Yii version.
- An initialized Yii 2 Advanced application with working `@common`, `@backend`, `@frontend` and `@console` aliases, database configuration and web assets.
- `yiisoft/yii2-gii`, enabled in the **console** application, for `gii-model` and `gii-crud`.
- The default migration classes target **MySQL**: charset, collation, InnoDB and `ON UPDATE CURRENT_TIMESTAMP` are database-specific. Adapt the migration base and timestamp definitions before using another database. SQLite regression tests do not establish portability of these migration defaults.

<a id="quick-start"></a>
## Quick start: a Book entity

Run commands from the Yii Advanced application root, where `yii` is located. This example assumes a new `book` table and a configured development database.

### 1. Install and configure

To install the branch described by this guide directly from the repository:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Merge the following into `console/config/main.php`; preserve the application's existing settings. `command` contains Gii arguments, not complete shell commands. Keep these arguments and entity names under developer control.

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

In the development console configuration, enable Gii before returning `$config` if it is not already enabled:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Create the shared classes and register the entity

```sh
php yii dnk/setup
```

Add this member inside `common\components\Entity`, keeping existing constants:

```php
public const BOOK = 'book';
```

Add this entry inside `common\components\db\Tables::NAMES`:

```php
Entity::BOOK => 'book',
```

Add this entry inside `Entity::DATA` for readable labels:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

These are additions to existing class/array bodies, not standalone PHP files. Labels are optional: `getLabelOne()` and `getLabelMany()` fall back to the registered table name. An unknown entity without a usable label/table raises `InvalidConfigException`.

### 3. Generate, extend the schema and run Gii

```sh
php yii dnk/run book
```

The generated `BookSource` is initially a placeholder. Before using the models, edit the new `console/migrations/m…_create_table__book.php`: add the following entry to `constructTable()` alongside `id` and the timestamp columns, then review and apply the pending migrations:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii replaces `common/models/sources/BookSource.php` with the ActiveRecord schema, rules and labels. Put custom domain logic in item/form/service classes so regenerating the source does not erase it. For an existing table, register its name and use selective generation; do not apply a new create-table migration to it.

Adapt `backend/views/book/book-list.php` and `frontend/views/book/book-list.php`: the shipped list views contain example columns `parent_id`, `name`, `url` and `category_image`, not schema-derived columns. Replace their attribute entries in `columns` with the following; keep the existing `SerialColumn` and `ActionColumn` definitions so links still work:

```php
'id',
 'title',
```

In both `backend/views/book/book-read.php` and `frontend/views/book/book-read.php`, set the DetailView attributes:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Configure access and open the pages

Add this property to the generated `backend\controllers\BookController`:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Configure Yii `authManager`, create the `manageBooks` role or permission, and assign it to the intended account. DNK does not create accounts or RBAC assignments. Log in with that account.

With the default query-string URL format, open `index.php?r=book/list` under the backend web root, then `book/create`, `book/read&id=1` and `book/update&id=1` with an existing ID. The frontend provides `book/list` and `book/read&id=1`. URL paths depend on the application's URL manager.

<a id="commands"></a>
## Commands and configuration

`entity` accepts `book`, `book,author` or the quoted `"*"` (all entries in `entityList`). Use singular snake_case names such as `book_review`; generated classes use `BookReview` and controller routes use `book-review`. Optional `overwrite` is a positional boolean: `0` by default, `1` to replace files. Gii has separate options.

| Command after `php yii` | Result |
| --- | --- |
| `dnk/setup [overwrite]` | Shared application classes; no entity argument. |
| `dnk/run entity [overwrite]` | All entity files in the mapping below; excludes shared setup classes, tests and Gii execution. |
| `dnk/list entity map [overwrite]` | Only the comma-separated mapping keys. |
| `dnk/gen-models entity [overwrite]` | Source, item, form and search models for the applicable application sides. |
| `dnk/gen-services entity [overwrite]` | Common, backend and frontend services. |
| `dnk/gen-controllers entity [overwrite]` | Backend and frontend controllers. |
| `dnk/gen-backend-views entity [overwrite]` | Backend form, create, update, read and list views. |
| `dnk/gen-frontend-views entity [overwrite]` | Frontend list and read views. |
| `dnk/gen-tests entity [overwrite]` | Eight Codeception unit-test skeletons. |
| `dnk/gii-model entity` | Execute configured `gii/model` for each entity. |
| `dnk/gii-crud entity` | Execute configured `gii/crud` for each entity. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` is optional: DNK's pages already use its own resources and views. The example Gii configuration creates a separate `BookCrudController` with views in `backend/views/book-crud`, using `yii\web\Controller`. Gii uses `index/view/create/update/delete`; DNK uses `list/read/create/update` and does not supply a delete action. Gii's controller needs its own access policy. A failed Gii child process stops the entity loop and its nonzero exit code is returned.

| Generator property | Purpose |
| --- | --- |
| `entityList` | List expanded by `"*"`; empty by default. |
| `command` | Argument strings keyed by `COMMAND_MODEL` and `COMMAND_CRUD`; required for the corresponding Gii command. |
| `baseClasses` | Optional partial overrides keyed by the constants below; omitted entries retain defaults. Invalid class names are rejected before generation. |

| `GenerateController` constant | Default class |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Template/Gii markers: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}` and `{{BaseResourceClassName}}`. Customize generated files or `baseClasses`; there is no public `root` or `parts` setting. Templates are shipped inside the package.

<a id="generated-files"></a>
## Generated files and architecture

Flow: **controller → service → ActiveRecord/form**, with a **resource → view** for presentation. `BaseController` instantiates the class in `SERVICE` through Yii's container. Common source models hold Gii output; common item models extend them; backend/frontend item models add side-specific behavior. Forms extend their side's item model. Services in backend/frontend create forms; the common service creates the common item model.

Search models build optional filters from the table schema. Their `MODEL` selects the appropriate item class; `search()` and `findWhere()` preserve conditions added by that class's `find()` using `andWhere`/`andFilterWhere`. Define visibility rules in the relevant item/form query rather than assuming the generator provides application-specific authorization.

`dnk/setup` creates these files (paths beginning with `@` are Yii aliases):

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` and the interfaces remain in the package; setup does not copy them into `common/components`.

The complete `dnk/list` mapping also describes the output of `dnk/run`:

| Mapping key | Destination template |
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

`dnk/gen-tests` creates:

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
## Services and resources

After the Book quick start, these service calls use the generated classes:

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

Generated common services configure only `MODEL`. For form/resource APIs, use a backend/frontend service or explicitly configure `MODEL_FORM` and `RESOURCES`.

`create(array $params = [], bool $is_save = true)` and `update($model, array $params = [], bool $is_save = true)` return the model, not a success boolean. Check `hasErrors()` / `getErrors()` and handle persistence failures; database exceptions can propagate. `false` skips both `save()` and its automatic validation; call `validate()` explicitly when needed. The update example assumes creation succeeded and the record exists.

Both methods accept flat attributes or a payload under the model's actual `formName()`; only safe attributes in the active scenario are assigned. A non-array value under that form key raises `yii\base\InvalidArgumentException`. `createInScenario($params, $scenario, $is_save)` applies a trusted scenario before assignment and delegates to `create()`, preserving overrides compatible with the base implementation. Its scenario applies only to that call.

| API | Purpose |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Configured model/form class names. |
| `getModel($params)` / `getForm($params)` | Instantiate the configured class; `$params` are constructor arguments, not an attribute payload. |
| `getEntity()` | Common item model, or the form in backend/frontend services. |
| `findWhere($criteria)` | An `ActiveQuery`; use `one()`, `all()`, etc. No `findByID()` method exists. |
| `getResource($key, $params)` | Construct a class from `RESOURCES` with constructor arguments; return `null` for an unmapped key. |

Resource keys are `BaseService::CREATE`, `READ`, `UPDATE` and `LIST`. Frontend services map only `LIST` and `READ`; `FORM` is a constant without a default resource mapping. A read resource takes an existing item; `$controller` below is the current web controller and `$found` must not be `null`:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` returns `['R' => $resource]`. `render($controller, $template = null, $params = [])` renders `TEMPLATE`, exposing `$R` in the view. `GridViewResource($searchForm, $activeDataProvider)` carries the search model and provider for list resources. `json($params = [])` sets JSON response format and returns resource properties merged with `$params`; it does **not** apply the frontend `JSON_FIELDS` allowlist. Use an explicit public payload for an API. There is no `content()` method.

<a id="extension"></a>
## Controller extension and form scenarios

Backend hooks are `beforeList()`, `beforeCreate()`, `beforeRead(int $id)` and `beforeUpdate(int $id)`. `SEARCH_MODEL` selects the backend search class (`SEARCH` remains a compatibility alias in generated controllers). `setTitle()` and `setBreadcrumbs()` set page metadata.

`addMutation(new MutationResource($event, $action, $callback))` customizes backend resource construction. At `EVENT_RESOURCE_INIT`, the first callback receives constructor parameters and must return a resource; further callbacks receive that resource. At `EVENT_RESOURCE_CREATED`, callbacks transform the constructed resource. **Every callback must return the resource.**

For example, add this method inside `backend\controllers\BookController`. First define the `create` scenario and its safe fields in `BookForm::scenarios()`; the scenario name must come from application code, not POST data:

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

Backend creation passes the selected form scenario to `createInScenario()`. Generated forms render safe scalar attributes, including scalar virtual fields, and skip auto-increment columns and SQL-expression defaults. Creation preserves database defaults for untouched columns. Array/object fields, relations and uploads need explicit widgets and application logic.

<a id="security"></a>
## Access, routes and JSON

Backend access is denied by default: `accessRoles = []` grants nobody access; guests are always denied. Set explicit RBAC roles/permissions as in the quick start. `['@']` deliberately grants all authenticated users access and should only be used when that is the intended policy. A denied guest can be redirected to login by Yii; an authenticated user without permission receives 403. Frontend actions are public unless the application adds access restrictions.

DNK's default action is `list`. Read/update return 404 when the record is missing. The generated frontend `BookController` overrides `actionList()` / `actionRead()` to render HTML and apply search filters. Merely adding `?type=json` to those overrides does not enable the base JSON behavior.

To use the inherited HTML/JSON actions, create `frontend/controllers/BookApiController.php`:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` returns `items` and `totalCount`; `index.php?r=book-api/read&id=1&type=json` returns `item`. The base list uses the item query and pagination, not the generated search form's filtering logic. `JSON_FIELDS` defaults to `['id']`; an empty array exposes no fields. Yii model `fields()` restrictions still apply. Keep passwords, tokens and private fields out of the allowlist.

<a id="migrations"></a>
## Migrations

Generated migrations extend `BaseMigration`, set `$entity = Entity::…` and implement `constructTable(): array`. `safeUp()` resolves the label before creating the table, then adds a table comment; `safeDown()` drops that table. `Tables::NAMES` supplies the table name, while `Setup` supplies table options and timestamp SQL. Review generated migrations before execution or rollback. A matching `*_create_table__<entity>.php` prevents generation of another creation migration, even with overwrite enabled.

<a id="upgrades"></a>
## Regeneration and upgrades

| Operation | Existing files |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Skip without asking. |
| `run` / `list` / model, service, controller or view generators, overwrite `0` | Ask per file; only the literal `yes` authorizes replacement. |
| DNK overwrite `1` | Replace generated files; no merge with manual changes. Existing creation migrations are still skipped. |
| Gii commands | Use `--interactive` and `--overwrite` inside `command`; they do not take a DNK overwrite argument. |

Commit or back up customizations before regeneration. File replacement is atomic per file; a multi-file generation is not a transaction. Updating the Composer package changes runtime classes but does not update files already generated in the application.

For existing applications, compare the new templates with shared controllers, source/item/search/form classes, services, resources and views. Apply relevant changes selectively, configure `accessRoles`, preserve scenario-aware `create()` delegation, and recheck form fields and public JSON. Gii's source overwrite should not be used on manually maintained classes. Keep DNK and Gii CRUD controllers/views separate.

<a id="tests"></a>
## Tests

From a checkout of **this library**, not the generated application:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` checks local links, translated sections, identical code examples and the generator command/file mapping without external dependencies. It also runs first in `test:all`.

The regression suite requires `pdo_sqlite` and generates a temporary application from real templates. `DNK_TEST_VENDOR_PATH` can select an external vendor directory. Codeception integration needs a compatible installed runner or `DNK_TEST_CODECEPTION` pointing to its PHP entry point / PHAR; a missing runner fails the check. `test:all` runs both suites. See [tests/README.md](tests/README.md) for details.

`dnk/gen-tests` instead creates tests in the target application's common/backend/frontend suites. These are skeletons: add domain assertions, fixtures and suite configuration. Actor typing accepts `Codeception\Actor`, including `Support` namespace layouts. Generating tests does not run them.

<a id="troubleshooting"></a>
## Troubleshooting

| Symptom | Check |
| --- | --- |
| Unknown `dnk` / `gii` command | Console `controllerMap` / Gii installation and console module configuration. |
| Unknown property `parts` or `root` | Remove the legacy property; use `entityList` and Yii aliases. |
| Backend login redirect / 403 | Session identity, explicit `accessRoles`, `authManager` assignments. |
| Read/update 404 | Existing ID, item/form visibility rules and correct controller route. |
| Empty entity list for `"*"` | Populate `entityList`; quote the asterisk in the shell. |
| `InvalidConfigException` for an entity | Add its constant, `Tables::NAMES` entry and valid optional labels. |
| Missing table / rules / form fields | Apply the schema, run `gii-model`, inspect form rules/scenarios and safe attributes. |
| Migration SQL error on another DBMS | Adapt the MySQL-specific base/options/timestamps. |
| JSON requested but HTML returned | Check whether the concrete frontend controller overrides the base actions. |
| Codeception runner not found | Follow the integration setup in `tests/README.md`. |

Package license: MIT, as declared in [composer.json](composer.json).
