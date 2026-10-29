# Yii2 DNK Generator

<a id="languages"></a>
## Тілдер

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | **Қазақша** | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[Тілдерді таңдау негіздемесі және аудармаларды жаңарту тәртібі](../../docs/i18n/README.md).

DNK **Yii 2 Advanced** қолданбасының кодын жасайды және сол код пайдаланатын базалық кластарды ұсынады: модельдер, формалар, іздеу модельдері, сервистер, контроллерлер, ресурстар, көріністер, миграциялар және тест дайындамалары. Үлгілер [src/templates](../../src/templates) ішінде, орындалу кезінде қолданылатын кластар [src/source](../../src/source) ішінде орналасқан. Бұл нұсқаулық ағымдағы `master` тармағын сипаттайды; бұрынғы шығарылымдар өзгеше болуы мүмкін.

<a id="contents"></a>
## Мазмұны

- [Талаптар](#requirements)
- [Жылдам бастау](#quick-start)
- [Пәрмендер және баптау](#commands)
- [Жасалатын файлдар мен архитектура](#generated-files)
- [Сервистер мен ресурстар](#api)
- [Контроллерлерді кеңейту және форма сценарийлері](#extension)
- [Қолжетімділік, маршруттар және JSON](#security)
- [Миграциялар](#migrations)
- [Қайта генерациялау және жаңарту](#upgrades)
- [Тесттер](#tests)
- [Ақауларды жою](#troubleshooting)

<a id="requirements"></a>
## Талаптар

- PHP **8.0 немесе одан жаңа нұсқа**, Composer және орнатылған Yii нұсқасы талап ететін PHP кеңейтімдері.
- Инициализацияланған Yii 2 Advanced қолданбасы: `@common`, `@backend`, `@frontend`, `@console` алиастары, дерекқорға қосылу және веб-ресурстар дұрыс бапталуы керек.
- `gii-model` және `gii-crud` үшін **консольдік** қолданбада қосылған `yiisoft/yii2-gii` қажет.
- Әдепкі миграциялар **MySQL** жүйесіне арналған: таңбалар жиыны, салыстыру ережесі, InnoDB және `ON UPDATE CURRENT_TIMESTAMP` нақты дерекқор жүйесіне тәуелді. Басқа дерекқорды пайдаланбас бұрын миграцияның базалық класын және уақыт белгілерінің анықтамаларын бейімдеңіз. SQLite регрессиялық тесттері осы әдепкі миграциялардың басқа жүйелерде жұмыс істейтінін дәлелдемейді.

<a id="quick-start"></a>
## Жылдам бастау: Book нысаны

Пәрмендерді `yii` файлы орналасқан Yii Advanced қолданбасының түбірлік каталогынан орындаңыз. Мысал жаңа `book` кестесін және бапталған әзірлеу дерекқорын көздейді.

### 1. Орнату және баптау

Осы нұсқаулықта сипатталған тармақты репозиторийден тікелей орнату:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Қолданбаның қолданыстағы параметрлерін сақтай отырып, келесі баптауды `console/config/main.php` файлына қосыңыз. `command` толық shell пәрмендерін емес, Gii аргументтерін қамтиды. Аргументтер мен нысан атауларын әзірлеуші басқаруы керек.

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

Gii әлі қосылмаған болса, әзірлеу ортасының консольдік конфигурациясында `$config` қайтарылғанға дейін оны қосыңыз:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Ортақ кластарды жасау және нысанды тіркеу

```sh
php yii dnk/setup
```

Қолданыстағы тұрақтыларды сақтап, `common\components\Entity` класының ішіне мына тұрақтыны қосыңыз:

```php
public const BOOK = 'book';
```

`common\components\db\Tables::NAMES` ішіне мына элементті қосыңыз:

```php
Entity::BOOK => 'book',
```

Оқуға ыңғайлы атаулар үшін `Entity::DATA` ішіне мына элементті қосыңыз:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

Бұл үзінділер жеке PHP файлдары емес, қолданыстағы кластар мен массивтердің ішіне қосылады. Атаулар міндетті емес: олар болмаса, `getLabelOne()` және `getLabelMany()` тіркелген кестенің атын пайдаланады. Жарамды атауы немесе кесте баптауы жоқ белгісіз нысан `InvalidConfigException` қатесін тудырады.

### 3. Код жасау, схеманы кеңейту және Gii іске қосу

```sh
php yii dnk/run book
```

Алғашында `BookSource` тек дайындама болады. Модельдерді пайдаланбас бұрын жаңа `console/migrations/m…_create_table__book.php` файлын өзгертіңіз: `constructTable()` ішіндегі `id` және уақыт белгісі бағандарына келесі элементті қосыңыз. Содан кейін орындалмаған миграцияларды тексеріп, қолданыңыз:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii `common/models/sources/BookSource.php` файлын схемасы, тексеру ережелері және атаулары бар ActiveRecord класына ауыстырады. Source қайта жасалғанда арнайы домен логикасы жойылмауы үшін оны item/form/service кластарына орналастырыңыз. Бұрыннан бар кесте үшін оның атын тіркеп, таңдамалы генерацияны қолданыңыз; оған жаңа кесте жасау миграциясын қолданбаңыз.

`backend/views/book/book-list.php` және `frontend/views/book/book-list.php` файлдарын бейімдеңіз: әдепкі тізімдерде схемаға негізделген бағандар емес, үлгі ретіндегі `parent_id`, `name`, `url`, `category_image` бағандары бар. `columns` ішіндегі атрибуттарды төмендегілермен ауыстырыңыз; сілтемелер жұмыс істеуі үшін қолданыстағы `SerialColumn` және `ActionColumn` анықтамаларын сақтаңыз:

```php
'id',
 'title',
```

`backend/views/book/book-read.php` және `frontend/views/book/book-read.php` файлдарында DetailView атрибуттарын көрсетіңіз:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Қолжетімділікті баптау және беттерді ашу

Жасалған `backend\controllers\BookController` класына мына қасиетті қосыңыз:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Yii `authManager` компонентін баптаңыз, `manageBooks` рөлін немесе рұқсатын жасаңыз және қажетті есептік жазбаға тағайындаңыз. DNK есептік жазбаларды немесе RBAC тағайындауларын жасамайды. Бапталған есептік жазбамен кіріңіз.

Сұрау параметрлері қолданылатын әдепкі URL пішімінде backend веб-түбіріне қатысты `index.php?r=book/list` мекенжайын ашыңыз. Содан кейін `book/create`, `book/read&id=1` және `book/update&id=1` маршруттарын бар ID мәнімен пайдаланыңыз. Frontend жағында `book/list` және `book/read&id=1` бар. Нақты жолдар қолданбаның URL менеджеріне тәуелді.

<a id="commands"></a>
## Пәрмендер және баптау

`entity` аргументі `book`, `book,author` немесе тырнақша ішіндегі `"*"` мәнін қабылдайды. Соңғысы `entityList` тізімінің барлық элементтерін қамтиды. Жекеше түрде snake_case атауларын қолданыңыз: `book_review` үшін `BookReview` класы және `book-review` контроллер маршруты жасалады. Қосымша `overwrite` — позициялық логикалық аргумент: әдепкіде `0`, файлдарды ауыстыру үшін `1`. Gii параметрлері бөлек беріледі.

| `php yii` пәрменінен кейінгі бөлік | Нәтиже |
| --- | --- |
| `dnk/setup [overwrite]` | Қолданбаның ортақ кластары; нысан аргументі қажет емес. |
| `dnk/run entity [overwrite]` | Төмендегі сәйкестік кестесінің барлық нысан файлдары; setup кластары, тесттер және Gii орындалуы кірмейді. |
| `dnk/list entity map [overwrite]` | Үтірмен бөлінген генерация кілттеріне сәйкес файлдар ғана. |
| `dnk/gen-models entity [overwrite]` | Қолданбаның тиісті бөліктеріне арналған source, item, form және search модельдері. |
| `dnk/gen-services entity [overwrite]` | Common, backend және frontend сервистері. |
| `dnk/gen-controllers entity [overwrite]` | Backend және frontend контроллерлері. |
| `dnk/gen-backend-views entity [overwrite]` | Backend формасы және жасау, жаңарту, қарау, тізім көріністері. |
| `dnk/gen-frontend-views entity [overwrite]` | Frontend тізім және қарау көріністері. |
| `dnk/gen-tests entity [overwrite]` | Codeception модульдік тесттерінің сегіз дайындамасы. |
| `dnk/gii-model entity` | Әр нысан үшін бапталған `gii/model` пәрменін орындау. |
| `dnk/gii-crud entity` | Әр нысан үшін бапталған `gii/crud` пәрменін орындау. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` міндетті емес: DNK беттерінде өз ресурстары мен көріністері бар. Мысалдағы Gii баптауы `yii\web\Controller` негізінде бөлек `BookCrudController` жасайды, оның көріністері `backend/views/book-crud` ішінде орналасады. Gii `index/view/create/update/delete` әрекеттерін қолданады; DNK `list/read/create/update` әрекеттерін қолданады және жою әрекетін ұсынбайды. Gii контроллеріне жеке қолжетімділік саясаты қажет. Gii еншілес процесі сәтсіз аяқталса, нысандар циклі тоқтап, оның нөлден өзге аяқталу коды қайтарылады.

| Генератор қасиеті | Мақсаты |
| --- | --- |
| `entityList` | `"*"` арқылы қолданылатын тізім; әдепкіде бос. |
| `command` | `COMMAND_MODEL` және `COMMAND_CRUD` кілттері бар аргумент жолдары; тиісті Gii пәрменіне міндетті. |
| `baseClasses` | Төмендегі тұрақтылар арқылы әдепкі кластардың бір бөлігін ауыстыру; көрсетілмеген мәндер сақталады. Жарамсыз класс атаулары генерация басталмай тұрып қабылданбайды. |

| `GenerateController` тұрақтысы | Әдепкі класс |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Үлгі және Gii маркерлері: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}`, `{{BaseResourceClassName}}`. Бейімдеу үшін жасалған файлдарды немесе `baseClasses` параметрін өзгертіңіз; ашық `root` және `parts` параметрлері жоқ. Үлгілер пакеттің ішінде жеткізіледі.

<a id="generated-files"></a>
## Жасалатын файлдар мен архитектура

Орындалу ағыны: **контроллер → сервис → ActiveRecord/форма**, көрсету ағыны: **ресурс → көрініс**. `BaseController` Yii контейнері арқылы `SERVICE` ішінде көрсетілген класты жасайды. Ортақ source модельдері Gii нәтижесін сақтайды; common item модельдері олардан мұра алады, ал backend/frontend item модельдері өз бөлігіне тән мінез-құлықты қосады. Формалар тиісті item моделінен мұра алады. Backend/frontend сервистері формаларды, common сервисі ортақ item моделін жасайды.

Іздеу модельдері кесте схемасына сүйеніп, міндетті емес сүзгілерді құрады. `MODEL` тиісті item класын таңдайды; `search()` және `findWhere()` оның `find()` шарттарын `andWhere`/`andFilterWhere` арқылы сақтайды. Көріну ережелерін тиісті item/form сұрауларында анықтаңыз; генератор қолданбаға тән авторизацияны автоматты түрде жасамайды.

`dnk/setup` мына файлдарды жасайды; `@` таңбасымен басталатын жолдар — Yii алиастары:

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` және интерфейстер пакетте қалады; setup оларды `common/components` каталогына көшірмейді.

`dnk/list` кілттерінің толық кестесі `dnk/run` нәтижесін де сипаттайды:

| Генерация кілті | Нәтижелік жол үлгісі |
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

`dnk/gen-tests` мыналарды жасайды:

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
## Сервистер мен ресурстар

Book мысалын орындағаннан кейін жасалған кластарды былай пайдалануға болады:

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

Жасалған common сервистері тек `MODEL` параметрін баптайды. Формалар мен ресурстар API-сі үшін backend/frontend сервисін пайдаланыңыз немесе `MODEL_FORM` және `RESOURCES` параметрлерін анық көрсетіңіз.

`create(array $params = [], bool $is_save = true)` және `update($model, array $params = [], bool $is_save = true)` сәттілік белгісін емес, модельді қайтарады. `hasErrors()` / `getErrors()` нәтижелерін тексеріп, сақтау қателерін өңдеңіз; дерекқор ерекшеліктері шақырушы кодқа берілуі мүмкін. `false` мәні `save()` мен оның автоматты валидациясын өткізіп жібереді; қажет болса `validate()` әдісін анық шақырыңыз. Жаңарту мысалы жазба сәтті жасалды және дерекқорда бар деп есептейді.

Екі әдіс те атрибуттардың жай массивін немесе модельдің нақты `formName()` кілтіне салынған деректерді қабылдайды; белсенді сценарийдің қауіпсіз атрибуттары ғана толтырылады. Форма кілті астындағы мән массив болмаса, `yii\base\InvalidArgumentException` туындайды. `createInScenario($params, $scenario, $is_save)` атрибуттарды толтырмас бұрын сенімді сценарийді орнатып, жұмысты `create()` әдісіне береді; базалық іске асырумен үйлесімді қайта анықтаулар сақталады. Сценарий тек осы шақыруға қолданылады.

| API | Мақсаты |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Бапталған модель және форма кластарының атаулары. |
| `getModel($params)` / `getForm($params)` | Бапталған кластың данасын жасау; `$params` — атрибут деректері емес, конструктор аргументтері. |
| `getEntity()` | Ортақ item моделі немесе backend/frontend сервистеріндегі форма. |
| `findWhere($criteria)` | `one()`, `all()` және басқа әдістері бар `ActiveQuery`; `findByID()` әдісі жоқ. |
| `getResource($key, $params)` | Конструктор аргументтерімен `RESOURCES` ішіндегі класты жасау; сәйкес кілт болмаса `null` қайтарылады. |

Ресурс кілттері: `BaseService::CREATE`, `READ`, `UPDATE`, `LIST`. Frontend сервистері тек `LIST` және `READ` ресурстарын баптайды; `FORM` тұрақтысына әдепкі ресурс берілмеген. Қарау ресурсы бар жазбаны қабылдайды; төмендегі `$controller` — ағымдағы веб-контроллер, ал `$found` мәні `null` болмауы керек:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` нәтижесі — `['R' => $resource]`. `render($controller, $template = null, $params = [])` `TEMPLATE` көрінісін көрсетіп, оған `$R` айнымалысын береді. `GridViewResource($searchForm, $activeDataProvider)` тізімге арналған іздеу моделі мен деректер провайдерін сақтайды. `json($params = [])` жауап пішімін JSON етіп орнатып, ресурс қасиеттерін `$params` деректерімен біріктіреді; frontend `JSON_FIELDS` рұқсат тізімі **қолданылмайды**. API үшін жария деректерді анық құрастырыңыз. `content()` әдісі жоқ.

<a id="extension"></a>
## Контроллерлерді кеңейту және форма сценарийлері

Backend кеңейту нүктелері: `beforeList()`, `beforeCreate()`, `beforeRead(int $id)`, `beforeUpdate(int $id)`. `SEARCH_MODEL` іздеу моделін таңдайды; `SEARCH` жасалған контроллерлерде үйлесімділік үшін балама атау ретінде сақталған. `setTitle()` және `setBreadcrumbs()` бет метадеректерін орнатады.

`addMutation(new MutationResource($event, $action, $callback))` backend ресурсын жасау процесін бейімдейді. `EVENT_RESOURCE_INIT` кезінде бірінші өңдегіш конструктор параметрлерін алып, ресурсты қайтарады; кейінгі өңдегіштер сол ресурсты алады. `EVENT_RESOURCE_CREATED` өңдегіштері жасалған ресурсты өзгертеді. **Әр өңдегіш ресурсты қайтаруы керек.**

Келесі әдісті `backend\controllers\BookController` ішіне қосыңыз. Алдымен `BookForm::scenarios()` ішінде `create` сценарийін және оның қауіпсіз өрістерін анықтаңыз; сценарий атауы POST деректерінен емес, қолданба кодынан алынуы керек:

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

Backend жазба жасағанда форма сценарийін `createInScenario()` әдісіне береді. Жасалған формалар қауіпсіз скаляр атрибуттарды, соның ішінде скаляр виртуалды өрістерді көрсетеді; автоматты өсетін бағандар мен SQL өрнегі түріндегі әдепкі мәндерді өткізіп жібереді. Жазба жасау кезінде толтырылмаған бағандар дерекқордың әдепкі мәндерін сақтайды. Массивтер, объектілер, байланыстар және файл жүктеу үшін арнайы виджеттер мен қолданба логикасы қажет.

<a id="security"></a>
## Қолжетімділік, маршруттар және JSON

Backend әдепкіде жабық: `accessRoles = []` ешкімге рұқсат бермейді; қонақтарға әрқашан кіруге тыйым салынады. Жылдам бастау мысалындағыдай RBAC рөлдерін немесе рұқсаттарын анық көрсетіңіз. `['@']` барлық аутентификацияланған пайдаланушыларға әдейі рұқсат береді; оны тек сондай саясат қажет болғанда пайдаланыңыз. Yii қонақты кіру бетіне бағыттай алады; аутентификацияланған, бірақ құқығы жоқ пайдаланушы 403 алады. Қолданба шектеу қоспаса, frontend әрекеттері жалпыға ашық.

DNK әдепкі әрекеті — `list`. Жоқ жазбаны қарау немесе жаңарту 404 қайтарады. Жасалған frontend `BookController` HTML және іздеу сүзгілері үшін `actionList()` / `actionRead()` әдістерін қайта анықтайды. Оларға `?type=json` қосу базалық контроллердің JSON тәртібін өздігінен қоспайды.

Мұраланған HTML/JSON әрекеттерін пайдалану үшін `frontend/controllers/BookApiController.php` файлын жасаңыз:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` `items` және `totalCount` қайтарады; `index.php?r=book-api/read&id=1&type=json` `item` қайтарады. Базалық тізім item сұрауы мен беттеуді пайдаланады, бірақ жасалған search формасының сүзгілеу логикасын қолданбайды. `JSON_FIELDS` әдепкіде `['id']`; бос массив ешбір өрісті ашпайды. Yii моделінің `fields()` шектеулері де сақталады. Құпиясөздер, токендер және жеке өрістер рұқсат тізіміне кірмеуі керек.

<a id="migrations"></a>
## Миграциялар

Миграциялар `BaseMigration` класынан мұра алып, `$entity = Entity::…` мәнін орнатады және `constructTable(): array` әдісін іске асырады. `safeUp()` кесте жасалмай тұрып атауды анықтайды, кейін кестеге түсініктеме қосады; `safeDown()` кестені жояды. `Tables::NAMES` кесте атын, ал `Setup` кесте параметрлері мен уақыт белгілерінің SQL анықтамаларын береді. Орындау немесе кері қайтару алдында миграцияларды тексеріңіз. Сәйкес `*_create_table__<entity>.php` файлы бар болса, қайта жазу қосылғанда да басқа жасау миграциясы құрылмайды.

<a id="upgrades"></a>
## Қайта генерациялау және жаңарту

| Операция | Қолданыстағы файлдар |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Сұрамай өткізіп жіберіледі. |
| `run` / `list` / модель, сервис, контроллер немесе көрініс генераторлары, overwrite `0` | Әр файл үшін сұрайды; тек дәл `yes` жауабы ауыстыруға рұқсат береді. |
| DNK overwrite `1` | Қолмен енгізілген өзгерістерді біріктірмей ауыстырады. Бар жасау миграциялары өткізіп жіберіледі. |
| Gii пәрмендері | `command` ішіндегі `--interactive` және `--overwrite` параметрлерін қолданады; DNK overwrite аргументін қабылдамайды. |

Қайта генерациялау алдында өзгерістерді commit етіңіз немесе сақтық көшірме жасаңыз. Ауыстыру бір файл үшін атомарлық түрде орындалады; бірнеше файлды генерациялау транзакция емес. Composer пакетін жаңарту кітапхана кластарын өзгертеді, бірақ қолданбада бұрын жасалған файлдарды жаңартпайды.

Қолданыстағы қолданбада жаңа үлгілерді ортақ контроллерлермен, source/item/search/form кластарымен, сервистермен, ресурстармен және көріністермен салыстырыңыз. Өзгерістерді таңдап енгізіңіз, `accessRoles` баптаңыз, сценарийді қолдайтын `create()` делегациясын сақтаңыз және форма өрістері мен жария JSON деректерін тексеріңіз. Қолмен өңделетін source кластарын Gii арқылы қайта жазбаңыз. DNK және Gii CRUD контроллерлері мен көріністерін бөлек ұстаңыз.

<a id="tests"></a>
## Тесттер

Жасалған қолданбадан емес, **осы кітапхананың жергілікті көшірмесінен** орындаңыз:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` сыртқы тәуелділіктерсіз жергілікті сілтемелерді, аударма бөлімдерін, код мысалдарының сәйкестігін және генератор пәрмендері мен файл жолдарын тексереді. Бұл тексеру `test:all` құрамында да бірінші орындалады.

Регрессиялық тесттерге `pdo_sqlite` қажет; олар нақты үлгілерден уақытша қолданба жасайды. `DNK_TEST_VENDOR_PATH` сыртқы vendor каталогын көрсетуге мүмкіндік береді. Codeception интеграциясына орнатылған үйлесімді іске қосқыш немесе оның PHP кіру файлына/PHAR файлына көрсететін `DNK_TEST_CODECEPTION` қажет; іске қосқыш болмаса тексеру сәтсіз аяқталады. `test:all` екі тест жиынтығын орындайды. Толығырақ: [tests/README.md](../../tests/README.md).

`dnk/gen-tests` мақсатты қолданбаның common/backend/frontend жиынтықтарында тесттер жасайды. Бұлар дайындамалар ғана: домен логикасының тексерулерін, фикстураларды және жиынтық конфигурациясын қосу қажет. Actor типі `Codeception\Actor` класын, соның ішінде `Support` атаулар кеңістіктерін қолдайды. Тесттерді жасау оларды орындамайды.

<a id="troubleshooting"></a>
## Ақауларды жою

| Белгісі | Нені тексеру керек |
| --- | --- |
| Белгісіз `dnk` / `gii` пәрмені | Консольдік `controllerMap`, Gii орнатылуы және консоль модулінің баптауы. |
| Белгісіз `parts` немесе `root` қасиеті | Ескі қасиетті алып тастап, `entityList` және Yii алиастарын қолдану. |
| Backend кіру бетіне бағыттайды / 403 | Сессия пайдаланушысы, анық `accessRoles`, `authManager` тағайындаулары. |
| Қарау/жаңарту кезінде 404 | ID бар-жоғы, item/form көріну ережелері және контроллер маршруты. |
| `"*"` үшін бос тізім | `entityList` толтыру және жұлдызшаны тырнақшаға алу. |
| Нысанға қатысты `InvalidConfigException` | Тұрақты, `Tables::NAMES` элементі және дұрыс қосымша атаулар. |
| Кесте, ережелер немесе форма өрістері жоқ | Схеманы қолдану, `gii-model` орындау, форма ережелерін, сценарийлерін және қауіпсіз атрибуттарын тексеру. |
| Басқа дерекқор жүйесінде миграция SQL қатесі | MySQL-ге тән базалық класс, параметрлер және уақыт белгілерін бейімдеу. |
| JSON орнына HTML қайтарылады | Нақты frontend контроллері базалық әрекеттерді қайта анықтайтынын тексеру. |
| Codeception іске қосқышы табылмады | `tests/README.md` ішіндегі интеграция баптауын орындау. |

Пакет лицензиясы — MIT, [composer.json](../../composer.json) файлында көрсетілген.
