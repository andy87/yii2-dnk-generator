# Yii2 DNK Generator

<a id="languages"></a>
## Языки

[English](../../README.md) | **Русский** | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[Выбор языков и правила обновления переводов](../../docs/i18n/README.md).

DNK генерирует код приложения **Yii 2 Advanced** и предоставляет используемые им базовые классы: модели, формы, модели поиска, сервисы, контроллеры, ресурсы, представления, миграции и заготовки тестов. Шаблоны находятся в [src/templates](../../src/templates), классы библиотеки — в [src/source](../../src/source). Руководство описывает текущую ветку `master`; старые выпуски могут отличаться.

<a id="contents"></a>
## Содержание

- [Требования](#requirements)
- [Быстрый старт](#quick-start)
- [Команды и конфигурация](#commands)
- [Генерируемые файлы и архитектура](#generated-files)
- [Сервисы и ресурсы](#api)
- [Расширение контроллеров и сценарии форм](#extension)
- [Доступ, маршруты и JSON](#security)
- [Миграции](#migrations)
- [Повторная генерация и обновление](#upgrades)
- [Тесты](#tests)
- [Устранение проблем](#troubleshooting)

<a id="requirements"></a>
## Требования

- PHP **8.0 или новее**, Composer и расширения PHP, необходимые установленной версии Yii.
- Инициализированное приложение Yii 2 Advanced с рабочими алиасами `@common`, `@backend`, `@frontend`, `@console`, подключением к БД и веб-ресурсами.
- `yiisoft/yii2-gii`, включённый в **консольном** приложении, для команд `gii-model` и `gii-crud`.
- Стандартные миграции рассчитаны на **MySQL**: кодировка, сравнение строк, InnoDB и `ON UPDATE CURRENT_TIMESTAMP` зависят от СУБД. Для другой БД необходимо адаптировать базовую миграцию и определения временных полей. Регрессионные тесты на SQLite не подтверждают переносимость стандартных миграций.

<a id="quick-start"></a>
## Быстрый старт: сущность Book

Команды выполняются из корня приложения Yii Advanced, где расположен файл `yii`. Пример предполагает новую таблицу `book` и настроенную БД для разработки.

### 1. Установка и конфигурация

Установка описываемой ветки непосредственно из репозитория:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Добавить следующую конфигурацию в `console/config/main.php`, сохранив существующие настройки приложения. `command` содержит аргументы Gii, а не полные команды оболочки. Аргументы и имена сущностей должны задаваться разработчиком.

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

В консольной конфигурации окружения разработки включить Gii перед возвратом `$config`, если модуль ещё не включён:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Создание общих классов и регистрация сущности

```sh
php yii dnk/setup
```

Добавить константу внутрь `common\components\Entity`, сохранив существующие константы:

```php
public const BOOK = 'book';
```

Добавить элемент в `common\components\db\Tables::NAMES`:

```php
Entity::BOOK => 'book',
```

Для читаемых названий добавить элемент в `Entity::DATA`:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

Это дополнения к существующим телам классов и массивов, а не самостоятельные PHP-файлы. Названия необязательны: `getLabelOne()` и `getLabelMany()` используют имя зарегистрированной таблицы при их отсутствии. Неизвестная сущность без подходящего названия или таблицы вызывает `InvalidConfigException`.

### 3. Генерация, расширение схемы и запуск Gii

```sh
php yii dnk/run book
```

Сначала `BookSource` является заготовкой. Перед использованием моделей изменить новую миграцию `console/migrations/m…_create_table__book.php`: добавить следующий элемент в `constructTable()` рядом с `id` и временными полями, затем проверить и применить ожидающие миграции:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii заменяет `common/models/sources/BookSource.php` моделью ActiveRecord со схемой, правилами и подписями. Пользовательскую доменную логику следует размещать в item/form/service-классах, чтобы повторная генерация исходной модели её не уничтожила. Для существующей таблицы зарегистрировать имя и использовать выборочную генерацию; новую миграцию создания такой таблицы применять нельзя.

Адаптировать `backend/views/book/book-list.php` и `frontend/views/book/book-list.php`: стандартные списки содержат примерные колонки `parent_id`, `name`, `url`, `category_image`, а не колонки из схемы. Заменить атрибуты в `columns` следующими; сохранить существующие определения `SerialColumn` и `ActionColumn`, чтобы ссылки продолжали работать:

```php
'id',
 'title',
```

В `backend/views/book/book-read.php` и `frontend/views/book/book-read.php` задать атрибуты DetailView:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Настройка доступа и открытие страниц

Добавить свойство в сгенерированный `backend\controllers\BookController`:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Настроить Yii `authManager`, создать роль или разрешение `manageBooks` и назначить нужной учётной записи. DNK не создаёт пользователей и назначения RBAC. Войти под настроенной учётной записью.

При стандартном формате URL с параметром запроса открыть `index.php?r=book/list` относительно корня backend, затем `book/create`, `book/read&id=1` и `book/update&id=1`, указав существующий ID. Во frontend доступны `book/list` и `book/read&id=1`. Пути зависят от URL manager приложения.

<a id="commands"></a>
## Команды и конфигурация

`entity` принимает `book`, `book,author` или `"*"` в кавычках — все элементы `entityList`. Использовать единственное число и snake_case, например `book_review`: класс будет называться `BookReview`, маршрут контроллера — `book-review`. Необязательный `overwrite` — позиционный логический аргумент: по умолчанию `0`, для замены файлов — `1`. У Gii отдельные параметры.

| Команда после `php yii` | Результат |
| --- | --- |
| `dnk/setup [overwrite]` | Общие классы приложения; аргумент сущности не нужен. |
| `dnk/run entity [overwrite]` | Все файлы сущности из таблицы ниже; без общих классов setup, тестов и запуска Gii. |
| `dnk/list entity map [overwrite]` | Только ключи генерации, перечисленные через запятую. |
| `dnk/gen-models entity [overwrite]` | Source-, item-, form- и search-модели для соответствующих частей приложения. |
| `dnk/gen-services entity [overwrite]` | Сервисы common, backend и frontend. |
| `dnk/gen-controllers entity [overwrite]` | Контроллеры backend и frontend. |
| `dnk/gen-backend-views entity [overwrite]` | Форма и представления создания, обновления, просмотра и списка backend. |
| `dnk/gen-frontend-views entity [overwrite]` | Представления списка и просмотра frontend. |
| `dnk/gen-tests entity [overwrite]` | Восемь заготовок unit-тестов Codeception. |
| `dnk/gii-model entity` | Настроенная команда `gii/model` для каждой сущности. |
| `dnk/gii-crud entity` | Настроенная команда `gii/crud` для каждой сущности. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` необязателен: страницы DNK уже используют собственные ресурсы и представления. Пример конфигурации Gii создаёт отдельный `BookCrudController` с представлениями в `backend/views/book-crud` и базовым классом `yii\web\Controller`. Gii использует `index/view/create/update/delete`; DNK — `list/read/create/update`, без действия удаления. Контроллеру Gii нужна собственная политика доступа. Ошибка дочернего процесса Gii останавливает цикл сущностей и возвращается как ненулевой код завершения.

| Свойство генератора | Назначение |
| --- | --- |
| `entityList` | Список для `"*"`; по умолчанию пустой. |
| `command` | Строки аргументов с ключами `COMMAND_MODEL` и `COMMAND_CRUD`; обязательны для соответствующей команды Gii. |
| `baseClasses` | Необязательные частичные переопределения по константам ниже; остальные значения сохраняются. Некорректные имена классов отклоняются до генерации. |

| Константа `GenerateController` | Класс по умолчанию |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Маркеры шаблонов и Gii: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}`, `{{BaseResourceClassName}}`. Для настройки доступны сгенерированные файлы и `baseClasses`; публичных настроек `root` и `parts` нет. Шаблоны поставляются внутри пакета.

<a id="generated-files"></a>
## Генерируемые файлы и архитектура

Поток: **контроллер → сервис → ActiveRecord/форма**, отображение — **ресурс → представление**. `BaseController` создаёт класс из `SERVICE` через контейнер Yii. Общие source-модели содержат результат Gii; common item-модели наследуют их, а backend/frontend item-модели добавляют поведение своей части приложения. Формы наследуют соответствующую item-модель. Сервисы backend/frontend создают формы, common-сервис — общую item-модель.

Модели поиска строят необязательные фильтры по схеме таблицы. `MODEL` выбирает соответствующий item-класс; `search()` и `findWhere()` сохраняют условия его `find()` через `andWhere`/`andFilterWhere`. Правила видимости задаются в запросах нужных item/form-классов; генератор не создаёт авторизацию, специфичную для приложения.

`dnk/setup` создаёт следующие файлы; пути с `@` — алиасы Yii:

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` и интерфейсы остаются в пакете; setup не копирует их в `common/components`.

Полная таблица ключей `dnk/list` также описывает результат `dnk/run`:

| Ключ генерации | Шаблон пути назначения |
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

`dnk/gen-tests` создаёт:

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
## Сервисы и ресурсы

После быстрого старта для Book доступны следующие вызовы сгенерированных классов:

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

Сгенерированный common-сервис задаёт только `MODEL`. Для API форм и ресурсов использовать backend/frontend-сервис либо явно настроить `MODEL_FORM` и `RESOURCES`.

`create(array $params = [], bool $is_save = true)` и `update($model, array $params = [], bool $is_save = true)` возвращают модель, а не признак успеха. Проверять `hasErrors()` / `getErrors()` и обрабатывать ошибки сохранения; исключения БД могут передаваться вызывающему коду. `false` пропускает `save()` вместе с автоматической валидацией; при необходимости вызывать `validate()` явно. Пример обновления предполагает успешное создание существующей записи.

Оба метода принимают плоский массив атрибутов либо данные под фактическим `formName()` модели; присваиваются только безопасные атрибуты активного сценария. Значение под ключом формы, не являющееся массивом, вызывает `yii\base\InvalidArgumentException`. `createInScenario($params, $scenario, $is_save)` устанавливает доверенный сценарий до присваивания и делегирует работу в `create()`, сохраняя переопределения, совместимые с базовой реализацией. Сценарий действует только для текущего вызова.

| API | Назначение |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Имена настроенных классов модели и формы. |
| `getModel($params)` / `getForm($params)` | Создание настроенного класса; `$params` — аргументы конструктора, а не данные атрибутов. |
| `getEntity()` | Общая item-модель либо форма в сервисах backend/frontend. |
| `findWhere($criteria)` | `ActiveQuery` для вызова `one()`, `all()` и других методов. Метода `findByID()` нет. |
| `getResource($key, $params)` | Создание класса из `RESOURCES` с аргументами конструктора; для отсутствующего ключа возвращается `null`. |

Ключи ресурсов: `BaseService::CREATE`, `READ`, `UPDATE`, `LIST`. Frontend-сервисы задают только `LIST` и `READ`; константа `FORM` не имеет стандартного ресурса. Ресурс просмотра принимает существующую запись; ниже `$controller` — текущий веб-контроллер, а `$found` не должен быть `null`:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` возвращает `['R' => $resource]`. `render($controller, $template = null, $params = [])` отображает `TEMPLATE`, передавая `$R` в представление. `GridViewResource($searchForm, $activeDataProvider)` содержит модель поиска и провайдер для ресурсов списка. `json($params = [])` устанавливает формат JSON и возвращает свойства ресурса, объединённые с `$params`; список разрешённых полей frontend `JSON_FIELDS` **не применяется**. Для API нужен явно сформированный публичный ответ. Метода `content()` нет.

<a id="extension"></a>
## Расширение контроллеров и сценарии форм

Точки расширения backend: `beforeList()`, `beforeCreate()`, `beforeRead(int $id)`, `beforeUpdate(int $id)`. `SEARCH_MODEL` выбирает модель поиска (`SEARCH` сохранён как совместимый псевдоним в сгенерированных контроллерах). `setTitle()` и `setBreadcrumbs()` задают метаданные страницы.

`addMutation(new MutationResource($event, $action, $callback))` настраивает создание backend-ресурса. Для `EVENT_RESOURCE_INIT` первый обработчик получает параметры конструктора и возвращает ресурс; следующие получают этот ресурс. Обработчики `EVENT_RESOURCE_CREATED` преобразуют уже созданный ресурс. **Каждый обработчик должен возвращать ресурс.**

Пример метода для `backend\controllers\BookController`. Сначала определить сценарий `create` и его безопасные поля в `BookForm::scenarios()`; имя сценария должно задаваться кодом приложения, а не данными POST:

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

При создании backend передаёт сценарий формы в `createInScenario()`. Сгенерированные формы отображают безопасные скалярные атрибуты, включая скалярные виртуальные поля, и пропускают автоинкрементные колонки и значения по умолчанию в виде SQL-выражений. При создании сохраняются значения БД по умолчанию для незаданных колонок. Массивам, объектам, связям и загрузке файлов нужны отдельные виджеты и логика приложения.

<a id="security"></a>
## Доступ, маршруты и JSON

Backend закрыт по умолчанию: `accessRoles = []` никому не предоставляет доступ; гости всегда отклоняются. Указать явные роли или разрешения RBAC, как в быстром старте. `['@']` намеренно разрешает доступ всем авторизованным пользователям и подходит только при соответствующей политике. Yii может перенаправить гостя на вход; авторизованный пользователь без прав получает 403. Frontend-действия публичны, пока приложение не добавит ограничения.

Действие DNK по умолчанию — `list`. Просмотр и обновление отсутствующей записи возвращают 404. Сгенерированный frontend `BookController` переопределяет `actionList()` / `actionRead()` для HTML и поисковых фильтров. Добавление `?type=json` к этим действиям само по себе не включает JSON базового контроллера.

Для наследуемых HTML/JSON-действий создать `frontend/controllers/BookApiController.php`:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` возвращает `items` и `totalCount`; `index.php?r=book-api/read&id=1&type=json` — `item`. Базовый список использует запрос item-модели и пагинацию, без фильтрации сгенерированной search-формы. `JSON_FIELDS` по умолчанию равен `['id']`; пустой массив не раскрывает поля. Ограничения Yii `fields()` также применяются. Пароли, токены и приватные данные не должны попадать в список разрешённых полей.

<a id="migrations"></a>
## Миграции

Миграции наследуют `BaseMigration`, задают `$entity = Entity::…` и реализуют `constructTable(): array`. `safeUp()` определяет название до создания таблицы, затем добавляет комментарий; `safeDown()` удаляет таблицу. `Tables::NAMES` задаёт имя таблицы, `Setup` — её опции и SQL временных полей. Проверять миграции перед выполнением и откатом. Наличие `*_create_table__<entity>.php` запрещает генерацию ещё одной миграции создания, даже при включённой перезаписи.

<a id="upgrades"></a>
## Повторная генерация и обновление

| Операция | Существующие файлы |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Пропускаются без вопроса. |
| `run` / `list` / генераторы моделей, сервисов, контроллеров и представлений, overwrite `0` | Запрос для каждого файла; замена разрешается только буквальным `yes`. |
| DNK overwrite `1` | Замена без объединения с ручными изменениями. Миграции создания по-прежнему пропускаются. |
| Команды Gii | Параметры `--interactive` и `--overwrite` внутри `command`; аргумента overwrite от DNK нет. |

Перед генерацией закоммитить изменения или сделать резервную копию. Замена атомарна для одного файла; генерация нескольких файлов не является транзакцией. Обновление пакета Composer изменяет классы библиотеки, но не обновляет уже сгенерированные файлы приложения.

Для существующего приложения сравнить новые шаблоны с общими контроллерами, source/item/search/form-классами, сервисами, ресурсами и представлениями. Применять изменения выборочно, настроить `accessRoles`, сохранить делегирование в `create()` с поддержкой сценариев, проверить поля форм и публичный JSON. Не перезаписывать через Gii source-классы с ручными изменениями. Контроллеры и представления DNK и Gii CRUD следует хранить отдельно.

<a id="tests"></a>
## Тесты

Выполнять из копии **этой библиотеки**, а не сгенерированного приложения:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` без внешних зависимостей проверяет локальные ссылки, разделы переводов, совпадение примеров кода, команды и пути генератора. Эта проверка также запускается первой в `test:all`.

Регрессионные тесты требуют `pdo_sqlite` и создают временное приложение из реальных шаблонов. `DNK_TEST_VENDOR_PATH` позволяет указать внешний каталог vendor. Интеграции Codeception нужен совместимый установленный runner либо `DNK_TEST_CODECEPTION` с путём к PHP-файлу запуска или PHAR; отсутствие runner приводит к ошибке. `test:all` запускает оба набора. Подробности — в [tests/README.md](../../tests/README.md).

Команда `dnk/gen-tests` создаёт тесты в наборах common/backend/frontend целевого приложения. Это заготовки: нужны проверки доменной логики, фикстуры и конфигурация наборов. Типизация actor поддерживает `Codeception\Actor`, включая пространства имён `Support`. Генерация тестов не запускает их.

<a id="troubleshooting"></a>
## Устранение проблем

| Симптом | Что проверить |
| --- | --- |
| Неизвестная команда `dnk` / `gii` | Консольный `controllerMap`, установку Gii и настройку консольного модуля. |
| Неизвестное свойство `parts` или `root` | Удалить старое свойство; использовать `entityList` и алиасы Yii. |
| Перенаправление backend на вход / 403 | Учётную запись сессии, явные `accessRoles` и назначения `authManager`. |
| 404 при просмотре/обновлении | Наличие ID, правила видимости item/form и маршрут контроллера. |
| Пустой список для `"*"` | Заполнить `entityList`, заключить звёздочку в кавычки. |
| `InvalidConfigException` для сущности | Добавить константу, элемент `Tables::NAMES` и корректные необязательные названия. |
| Нет таблицы, правил или полей формы | Применить схему, выполнить `gii-model`, проверить правила, сценарии и безопасные атрибуты формы. |
| Ошибка SQL миграции в другой СУБД | Адаптировать базовый класс, опции и временные поля MySQL. |
| Вместо JSON возвращается HTML | Проверить переопределение базовых действий frontend-контроллером. |
| Не найден runner Codeception | Выполнить настройку интеграции из `tests/README.md`. |

Лицензия пакета: MIT, согласно [composer.json](../../composer.json).
