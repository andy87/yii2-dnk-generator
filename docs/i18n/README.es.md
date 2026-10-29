# Yii2 DNK Generator

<a id="languages"></a>
## Idiomas

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | **Español** | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[Selección de idiomas y política de traducción](../../docs/i18n/README.md).

DNK genera código para **Yii 2 Advanced** y proporciona las clases base que utiliza: modelos, formularios, modelos de búsqueda, servicios, controladores, recursos, vistas, migraciones y esqueletos de pruebas. Las plantillas están en [src/templates](../../src/templates) y las clases de ejecución en [src/source](../../src/source). Esta guía describe la rama `master` actual; las versiones anteriores pueden diferir.

<a id="contents"></a>
## Contenido

- [Requisitos](#requirements)
- [Inicio rápido](#quick-start)
- [Comandos y configuración](#commands)
- [Archivos generados y arquitectura](#generated-files)
- [Servicios y recursos](#api)
- [Extensión de controladores y escenarios](#extension)
- [Acceso, rutas y JSON](#security)
- [Migraciones](#migrations)
- [Regeneración y actualizaciones](#upgrades)
- [Pruebas](#tests)
- [Resolución de problemas](#troubleshooting)

<a id="requirements"></a>
## Requisitos

- PHP **8.0 o posterior**, Composer y las extensiones PHP requeridas por la versión de Yii instalada.
- Una aplicación Yii 2 Advanced inicializada, con alias `@common`, `@backend`, `@frontend`, `@console`, conexión a la base de datos y recursos web configurados.
- `yiisoft/yii2-gii` habilitado en la aplicación de **consola** para `gii-model` y `gii-crud`.
- Las migraciones predeterminadas utilizan opciones de **MySQL**: juego de caracteres, intercalación, InnoDB y `ON UPDATE CURRENT_TIMESTAMP`. Adapte la clase base y los campos temporales antes de usar otra base de datos. Las pruebas con SQLite no demuestran la portabilidad de estas migraciones.

<a id="quick-start"></a>
## Inicio rápido: una entidad Book

Ejecute los comandos desde la raíz de Yii Advanced, donde está `yii`. El ejemplo presupone una tabla nueva `book` y una base de datos de desarrollo configurada.

### 1. Instalar y configurar

Para instalar directamente desde el repositorio la rama descrita:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Incorpore esta configuración a `console/config/main.php` sin eliminar la existente. `command` contiene argumentos de Gii, no comandos completos de shell. Los argumentos y nombres de entidades deben estar bajo control del desarrollador.

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

En la configuración de consola para desarrollo, habilite Gii antes de devolver `$config` si aún no está habilitado:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Crear las clases compartidas y registrar la entidad

```sh
php yii dnk/setup
```

Añada este miembro a `common\components\Entity`, conservando las constantes existentes:

```php
public const BOOK = 'book';
```

Añada esta entrada a `common\components\db\Tables::NAMES`:

```php
Entity::BOOK => 'book',
```

Para etiquetas legibles, añada esta entrada a `Entity::DATA`:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

Son fragmentos para cuerpos de clases y arrays existentes, no archivos PHP independientes. Las etiquetas son opcionales: `getLabelOne()` y `getLabelMany()` usan el nombre de tabla registrado cuando faltan. Una entidad desconocida sin etiqueta o tabla válida produce `InvalidConfigException`.

### 3. Generar, ampliar el esquema y ejecutar Gii

```sh
php yii dnk/run book
```

`BookSource` es inicialmente un esqueleto. Antes de usar los modelos, edite `console/migrations/m…_create_table__book.php`: añada esta entrada a `constructTable()` junto a `id` y los campos temporales. Revise y aplique las migraciones pendientes:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii sustituye `common/models/sources/BookSource.php` por un ActiveRecord con esquema, reglas y etiquetas. Coloque la lógica de dominio en clases item/form/service para conservarla al regenerar. Para una tabla existente, registre su nombre y utilice generación selectiva; no ejecute otra migración de creación sobre ella.

Adapte `backend/views/book/book-list.php` y `frontend/views/book/book-list.php`: las listas incluyen columnas de ejemplo `parent_id`, `name`, `url` y `category_image`, no columnas derivadas del esquema. Sustituya sus atributos en `columns` por los siguientes, conservando `SerialColumn` y `ActionColumn` para mantener los enlaces:

```php
'id',
 'title',
```

En `backend/views/book/book-read.php` y `frontend/views/book/book-read.php`, configure los atributos de DetailView:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Configurar el acceso y abrir las páginas

Añada esta propiedad al `backend\controllers\BookController` generado:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Configure `authManager` de Yii, cree el rol o permiso `manageBooks` y asígnelo a la cuenta adecuada. DNK no crea cuentas ni asignaciones RBAC. Inicie sesión con esa cuenta.

Con las URL predeterminadas, abra `index.php?r=book/list` desde la raíz web del backend; después, `book/create`, `book/read&id=1` y `book/update&id=1`, usando un ID existente. El frontend ofrece `book/list` y `book/read&id=1`. Las rutas dependen del gestor de URL de la aplicación.

<a id="commands"></a>
## Comandos y configuración

`entity` acepta `book`, `book,author` o `"*"` entre comillas para todos los elementos de `entityList`. Use nombres singulares en snake_case: `book_review` genera `BookReview` y la ruta `book-review`. `overwrite` es un booleano posicional opcional: `0` por defecto, `1` para sustituir archivos. Gii tiene opciones propias.

| Comando después de `php yii` | Resultado |
| --- | --- |
| `dnk/setup [overwrite]` | Clases compartidas; no recibe entidad. |
| `dnk/run entity [overwrite]` | Todos los archivos del mapa inferior; excluye setup, pruebas y ejecución de Gii. |
| `dnk/list entity map [overwrite]` | Solo las claves del mapa separadas por comas. |
| `dnk/gen-models entity [overwrite]` | Modelos source, item, form y search de las partes correspondientes. |
| `dnk/gen-services entity [overwrite]` | Servicios common, backend y frontend. |
| `dnk/gen-controllers entity [overwrite]` | Controladores backend y frontend. |
| `dnk/gen-backend-views entity [overwrite]` | Formulario y vistas de creación, actualización, lectura y listado del backend. |
| `dnk/gen-frontend-views entity [overwrite]` | Vistas de listado y lectura del frontend. |
| `dnk/gen-tests entity [overwrite]` | Ocho esqueletos de pruebas unitarias Codeception. |
| `dnk/gii-model entity` | Ejecuta `gii/model` configurado para cada entidad. |
| `dnk/gii-crud entity` | Ejecuta `gii/crud` configurado para cada entidad. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` es opcional: DNK ya tiene recursos y vistas propios. La configuración de ejemplo crea un `BookCrudController` separado, basado en `yii\web\Controller`, con vistas en `backend/views/book-crud`. Gii usa `index/view/create/update/delete`; DNK usa `list/read/create/update` y no proporciona borrado. El controlador Gii necesita su propia política de acceso. Un fallo del proceso Gii detiene el bucle y devuelve su código de salida distinto de cero.

| Propiedad del generador | Propósito |
| --- | --- |
| `entityList` | Lista expandida por `"*"`; vacía por defecto. |
| `command` | Argumentos con claves `COMMAND_MODEL` y `COMMAND_CRUD`; necesarios para el comando Gii correspondiente. |
| `baseClasses` | Sustituciones parciales opcionales usando las constantes siguientes; las demás conservan sus valores. Los nombres inválidos se rechazan antes de generar. |

| Constante de `GenerateController` | Clase predeterminada |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Marcadores de plantillas/Gii: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}` y `{{BaseResourceClassName}}`. Personalice archivos generados o `baseClasses`; no hay propiedades públicas `root` ni `parts`. Las plantillas se distribuyen dentro del paquete.

<a id="generated-files"></a>
## Archivos generados y arquitectura

Flujo: **controlador → servicio → ActiveRecord/formulario**, y **recurso → vista** para presentar datos. `BaseController` crea la clase de `SERVICE` mediante el contenedor Yii. Los modelos source comunes contienen el resultado de Gii; los item comunes los extienden, y los item backend/frontend añaden comportamiento específico. Los formularios extienden el item correspondiente. Los servicios backend/frontend crean formularios; el servicio common crea el item común.

Los modelos de búsqueda obtienen filtros opcionales del esquema. `MODEL` selecciona la clase item; `search()` y `findWhere()` conservan sus condiciones de `find()` mediante `andWhere`/`andFilterWhere`. Defina las reglas de visibilidad en las consultas item/form correspondientes; el generador no configura la autorización propia de cada aplicación.

`dnk/setup` crea estos archivos; `@` indica un alias Yii:

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` e interfaces permanecen en el paquete; setup no los copia a `common/components`.

Mapa completo de `dnk/list`, también aplicable a `dnk/run`:

| Clave del mapa | Plantilla de destino |
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

`dnk/gen-tests` crea:

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
## Servicios y recursos

Tras completar el ejemplo Book, estas llamadas utilizan las clases generadas:

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

Los servicios common generados solo configuran `MODEL`. Para las API de formularios/recursos, use un servicio backend/frontend o configure explícitamente `MODEL_FORM` y `RESOURCES`.

`create(array $params = [], bool $is_save = true)` y `update($model, array $params = [], bool $is_save = true)` devuelven el modelo, no un booleano de éxito. Compruebe `hasErrors()` / `getErrors()` y gestione fallos de persistencia; las excepciones de base de datos pueden propagarse. `false` omite `save()` y su validación automática; invoque `validate()` cuando proceda. El ejemplo de actualización presupone una creación correcta y un registro existente.

Ambos métodos aceptan atributos planos o datos bajo el `formName()` real del modelo; solo asignan atributos seguros del escenario activo. Un valor no array bajo esa clave produce `yii\base\InvalidArgumentException`. `createInScenario($params, $scenario, $is_save)` aplica un escenario de confianza antes de asignar y delega en `create()`, conservando sobrescrituras compatibles con la implementación base. El escenario solo afecta a esa llamada.

| API | Propósito |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Nombres de las clases configuradas. |
| `getModel($params)` / `getForm($params)` | Instanciar la clase; `$params` son argumentos de constructor, no atributos. |
| `getEntity()` | Item común o formulario de los servicios backend/frontend. |
| `findWhere($criteria)` | `ActiveQuery` para `one()`, `all()`, etc. No existe `findByID()`. |
| `getResource($key, $params)` | Crear la clase de `RESOURCES` con argumentos de constructor; devuelve `null` si no hay correspondencia. |

Las claves son `BaseService::CREATE`, `READ`, `UPDATE` y `LIST`. Frontend solo define `LIST` y `READ`; `FORM` es una constante sin recurso predeterminado. El recurso de lectura necesita un registro existente; `$controller` es el controlador web actual y `$found` no puede ser `null`:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` devuelve `['R' => $resource]`. `render($controller, $template = null, $params = [])` renderiza `TEMPLATE` y expone `$R` en la vista. `GridViewResource($searchForm, $activeDataProvider)` contiene el modelo de búsqueda y el proveedor del listado. `json($params = [])` selecciona JSON y devuelve las propiedades del recurso combinadas con `$params`; **no** aplica la lista `JSON_FIELDS` del frontend. Construya explícitamente la respuesta pública de una API. No existe `content()`.

<a id="extension"></a>
## Extensión de controladores y escenarios

Los puntos de extensión backend son `beforeList()`, `beforeCreate()`, `beforeRead(int $id)` y `beforeUpdate(int $id)`. `SEARCH_MODEL` selecciona el modelo de búsqueda; `SEARCH` se conserva como alias de compatibilidad en controladores generados. `setTitle()` y `setBreadcrumbs()` configuran metadatos de página.

`addMutation(new MutationResource($event, $action, $callback))` personaliza la construcción del recurso backend. En `EVENT_RESOURCE_INIT`, el primer callback recibe argumentos de constructor y devuelve un recurso; los siguientes reciben ese recurso. En `EVENT_RESOURCE_CREATED`, los callbacks transforman el recurso construido. **Todos deben devolver el recurso.**

Añada el siguiente método a `backend\controllers\BookController`. Defina primero el escenario `create` y sus campos seguros en `BookForm::scenarios()`; el nombre debe proceder del código de aplicación, no de POST:

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

La creación backend pasa el escenario a `createInScenario()`. Los formularios muestran atributos escalares seguros, incluidos virtuales escalares, y omiten columnas autoincrementales y valores predeterminados expresados en SQL. La creación conserva valores predeterminados de BD para columnas sin modificar. Arrays, objetos, relaciones y cargas de archivos requieren widgets y lógica específicos.

<a id="security"></a>
## Acceso, rutas y JSON

El backend deniega el acceso por defecto: `accessRoles = []` no autoriza a nadie; los invitados siempre se rechazan. Configure roles o permisos RBAC explícitos. `['@']` autoriza deliberadamente a todos los usuarios autenticados; úselo solo si esa es la política deseada. Yii puede redirigir a un invitado al inicio de sesión; un usuario autenticado sin permiso recibe 403. El frontend es público salvo restricciones de la aplicación.

La acción predeterminada de DNK es `list`. Leer/actualizar un registro inexistente devuelve 404. El `BookController` frontend generado sobrescribe `actionList()` / `actionRead()` para HTML y filtros de búsqueda; añadir `?type=json` no activa el JSON base.

Para heredar las acciones HTML/JSON, cree `frontend/controllers/BookApiController.php`:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` devuelve `items` y `totalCount`; `index.php?r=book-api/read&id=1&type=json`, `item`. El listado base usa la consulta item y paginación, no los filtros del formulario search generado. `JSON_FIELDS` vale `['id']` por defecto; un array vacío no expone campos. También se respetan las restricciones de `fields()` de Yii. No incluya contraseñas, tokens ni campos privados.

<a id="migrations"></a>
## Migraciones

Las migraciones extienden `BaseMigration`, asignan `$entity = Entity::…` e implementan `constructTable(): array`. `safeUp()` resuelve la etiqueta antes de crear la tabla y después añade un comentario; `safeDown()` elimina la tabla. `Tables::NAMES` proporciona el nombre y `Setup` las opciones y SQL temporal. Revise antes de aplicar o revertir. Un archivo `*_create_table__<entity>.php` existente impide otra migración de creación, incluso con sobrescritura.

<a id="upgrades"></a>
## Regeneración y actualizaciones

| Operación | Archivos existentes |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Se omiten sin preguntar. |
| `run` / `list` / generadores de modelos, servicios, controladores o vistas, overwrite `0` | Se pregunta por archivo; solo el literal `yes` permite sustituir. |
| DNK overwrite `1` | Sustituye sin fusionar cambios manuales. Las migraciones de creación se siguen omitiendo. |
| Comandos Gii | Usan `--interactive` y `--overwrite` dentro de `command`; no aceptan el argumento overwrite de DNK. |

Guarde los cambios en Git o haga una copia antes de regenerar. La sustitución es atómica por archivo, pero la generación completa no es una transacción. Actualizar el paquete Composer cambia las clases de ejecución, no los archivos ya generados.

En aplicaciones existentes, compare plantillas con controladores compartidos, clases source/item/search/form, servicios, recursos y vistas. Aplique cambios selectivos, configure `accessRoles`, conserve la delegación a `create()` con escenarios y compruebe formularios y JSON público. No sobrescriba con Gii clases source mantenidas a mano. Separe controladores y vistas de DNK y Gii CRUD.

<a id="tests"></a>
## Pruebas

Desde una copia de **esta biblioteca**, no desde la aplicación generada:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` comprueba sin dependencias externas los enlaces locales, las secciones traducidas, la igualdad de los ejemplos y los comandos/rutas del generador. También se ejecuta primero en `test:all`.

La suite de regresión requiere `pdo_sqlite` y genera una aplicación temporal con las plantillas reales. `DNK_TEST_VENDOR_PATH` permite usar un directorio vendor externo. La integración Codeception requiere un ejecutor compatible instalado o `DNK_TEST_CODECEPTION` con su entrada PHP/PHAR; si falta, la comprobación falla. `test:all` ejecuta ambas suites. Consulte [tests/README.md](../../tests/README.md).

`dnk/gen-tests` crea pruebas en las suites common/backend/frontend de la aplicación destino. Son esqueletos: añada aserciones del dominio, fixtures y configuración de suites. Se admite `Codeception\Actor`, incluidos espacios de nombres `Support`. Generar pruebas no las ejecuta.

<a id="troubleshooting"></a>
## Resolución de problemas

| Síntoma | Comprobar |
| --- | --- |
| Comando `dnk` / `gii` desconocido | `controllerMap` de consola, instalación Gii y módulo de consola. |
| Propiedad `parts` o `root` desconocida | Eliminarla; usar `entityList` y alias Yii. |
| Redirección al inicio de sesión / 403 en backend | Identidad, `accessRoles` explícitos y asignaciones `authManager`. |
| 404 al leer/actualizar | ID existente, visibilidad item/form y ruta. |
| Lista vacía para `"*"` | Rellenar `entityList` y entrecomillar el asterisco. |
| `InvalidConfigException` de entidad | Constante, entrada `Tables::NAMES` y etiquetas opcionales válidas. |
| Faltan tabla, reglas o campos | Aplicar esquema, ejecutar `gii-model`, revisar reglas, escenarios y atributos seguros. |
| Error SQL de migración en otro SGBD | Adaptar la base, opciones y campos temporales MySQL. |
| Se recibe HTML en lugar de JSON | Comprobar sobrescrituras de las acciones frontend base. |
| Falta el ejecutor Codeception | Seguir la configuración en `tests/README.md`. |

Licencia del paquete: MIT, declarada en [composer.json](../../composer.json).
