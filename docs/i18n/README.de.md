# Yii2 DNK Generator

<a id="languages"></a>
## Sprachen

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | **Deutsch** | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[Sprachauswahl und Pflege der Übersetzungen](../../docs/i18n/README.md).

DNK erzeugt Anwendungscode für **Yii 2 Advanced** und stellt dessen Basisklassen bereit: Modelle, Formulare, Suchmodelle, Services, Controller, Ressourcen, Views, Migrationen und Testgerüste. Vorlagen liegen unter [src/templates](../../src/templates), Laufzeitklassen unter [src/source](../../src/source). Diese Anleitung beschreibt den aktuellen Branch `master`; ältere Versionen können abweichen.

<a id="contents"></a>
## Inhalt

- [Voraussetzungen](#requirements)
- [Schnellstart](#quick-start)
- [Befehle und Konfiguration](#commands)
- [Erzeugte Dateien und Architektur](#generated-files)
- [Services und Ressourcen](#api)
- [Controller-Erweiterung und Formularszenarien](#extension)
- [Zugriff, Routen und JSON](#security)
- [Migrationen](#migrations)
- [Erneute Generierung und Updates](#upgrades)
- [Tests](#tests)
- [Fehlerbehebung](#troubleshooting)

<a id="requirements"></a>
## Voraussetzungen

- PHP **8.0 oder neuer**, Composer und die von der installierten Yii-Version benötigten PHP-Erweiterungen.
- Eine initialisierte Yii-2-Advanced-Anwendung mit funktionierenden Aliasen `@common`, `@backend`, `@frontend`, `@console`, Datenbankkonfiguration und Web-Assets.
- `yiisoft/yii2-gii`, aktiviert in der **Konsolenanwendung**, für `gii-model` und `gii-crud`.
- Die Standardmigrationen verwenden **MySQL**-spezifische Einstellungen: Zeichensatz, Kollation, InnoDB und `ON UPDATE CURRENT_TIMESTAMP`. Für andere Datenbanken müssen Migrationsbasis und Zeitstempeldefinitionen angepasst werden. SQLite-Regressionstests belegen keine Portabilität dieser Vorgaben.

<a id="quick-start"></a>
## Schnellstart: eine Book-Entität

Befehle im Stammverzeichnis der Yii-Advanced-Anwendung ausführen, wo `yii` liegt. Das Beispiel setzt eine neue Tabelle `book` und eine konfigurierte Entwicklungsdatenbank voraus.

### 1. Installieren und konfigurieren

Den beschriebenen Branch direkt aus dem Repository installieren:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Diese Konfiguration mit `console/config/main.php` zusammenführen und vorhandene Einstellungen beibehalten. `command` enthält Gii-Argumente, keine vollständigen Shell-Befehle. Argumente und Entitätsnamen müssen unter Kontrolle des Entwicklers bleiben.

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

Falls Gii noch nicht aktiviert ist, in der Konsolenkonfiguration der Entwicklungsumgebung vor der Rückgabe von `$config` ergänzen:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Gemeinsame Klassen erzeugen und Entität registrieren

```sh
php yii dnk/setup
```

Diese Konstante in `common\components\Entity` ergänzen; vorhandene Konstanten erhalten:

```php
public const BOOK = 'book';
```

Diesen Eintrag in `common\components\db\Tables::NAMES` ergänzen:

```php
Entity::BOOK => 'book',
```

Für lesbare Bezeichnungen diesen Eintrag in `Entity::DATA` ergänzen:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

Dies sind Ergänzungen vorhandener Klassen- bzw. Array-Inhalte, keine eigenständigen PHP-Dateien. Bezeichnungen sind optional: `getLabelOne()` und `getLabelMany()` verwenden ersatzweise den registrierten Tabellennamen. Eine unbekannte Entität ohne gültige Bezeichnung/Tabelle löst `InvalidConfigException` aus.

### 3. Generieren, Schema erweitern und Gii ausführen

```sh
php yii dnk/run book
```

`BookSource` ist zunächst ein Platzhalter. Vor Verwendung der Modelle die neue Datei `console/migrations/m…_create_table__book.php` bearbeiten: diesen Eintrag neben `id` und den Zeitstempeln in `constructTable()` ergänzen. Anschließend ausstehende Migrationen prüfen und anwenden:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii ersetzt `common/models/sources/BookSource.php` durch ActiveRecord-Code mit Schema, Regeln und Bezeichnungen. Eigene Fachlogik gehört in item/form/service-Klassen, damit sie bei erneuter Source-Generierung erhalten bleibt. Bei vorhandenen Tabellen deren Namen registrieren und selektiv generieren; keine weitere Erstellungsmigration darauf anwenden.

`backend/views/book/book-list.php` und `frontend/views/book/book-list.php` anpassen: Die Listen enthalten Beispielspalten `parent_id`, `name`, `url`, `category_image`, keine aus dem Schema abgeleiteten Spalten. Die Attributeinträge in `columns` durch folgende ersetzen; vorhandene `SerialColumn`- und `ActionColumn`-Definitionen für funktionierende Links beibehalten:

```php
'id',
 'title',
```

In `backend/views/book/book-read.php` und `frontend/views/book/book-read.php` die DetailView-Attribute setzen:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Zugriff konfigurieren und Seiten öffnen

Diese Eigenschaft im erzeugten `backend\controllers\BookController` ergänzen:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Yii `authManager` konfigurieren, Rolle oder Berechtigung `manageBooks` erstellen und dem vorgesehenen Konto zuweisen. DNK erstellt weder Konten noch RBAC-Zuweisungen. Mit diesem Konto anmelden.

Beim Standard-URL-Format unter dem Backend-Webroot `index.php?r=book/list` öffnen, danach `book/create`, `book/read&id=1` und `book/update&id=1` mit einer vorhandenen ID. Das Frontend bietet `book/list` und `book/read&id=1`. Die konkreten Pfade hängen vom URL-Manager ab.

<a id="commands"></a>
## Befehle und Konfiguration

`entity` akzeptiert `book`, `book,author` oder `"*"` in Anführungszeichen für alle Einträge aus `entityList`. Namen im Singular und in snake_case verwenden: `book_review` ergibt die Klasse `BookReview` und die Route `book-review`. Das optionale positionelle Boolean-Argument `overwrite` ist standardmäßig `0`; `1` ersetzt Dateien. Gii hat eigene Optionen.

| Befehl nach `php yii` | Ergebnis |
| --- | --- |
| `dnk/setup [overwrite]` | Gemeinsame Klassen; kein Entitätsargument. |
| `dnk/run entity [overwrite]` | Alle Entitätsdateien der folgenden Zuordnung, ohne Setup-Klassen, Tests oder Gii-Ausführung. |
| `dnk/list entity map [overwrite]` | Nur die durch Kommas getrennten Zuordnungsschlüssel. |
| `dnk/gen-models entity [overwrite]` | Source-, Item-, Form- und Search-Modelle der jeweiligen Anwendungsteile. |
| `dnk/gen-services entity [overwrite]` | Common-, Backend- und Frontend-Services. |
| `dnk/gen-controllers entity [overwrite]` | Backend- und Frontend-Controller. |
| `dnk/gen-backend-views entity [overwrite]` | Backend-Formular sowie Create-, Update-, Read- und List-Views. |
| `dnk/gen-frontend-views entity [overwrite]` | Frontend-List- und Read-Views. |
| `dnk/gen-tests entity [overwrite]` | Acht Codeception-Unit-Testgerüste. |
| `dnk/gii-model entity` | Konfiguriertes `gii/model` je Entität ausführen. |
| `dnk/gii-crud entity` | Konfiguriertes `gii/crud` je Entität ausführen. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` ist optional: DNK verwendet bereits eigene Ressourcen und Views. Die Beispielkonfiguration erzeugt einen separaten `BookCrudController` auf Basis von `yii\web\Controller`, mit Views in `backend/views/book-crud`. Gii verwendet `index/view/create/update/delete`, DNK dagegen `list/read/create/update` ohne Löschaktion. Der Gii-Controller braucht eigene Zugriffsregeln. Schlägt ein Gii-Unterprozess fehl, endet die Entitätsschleife mit dessen Exitcode ungleich null.

| Generatoreigenschaft | Zweck |
| --- | --- |
| `entityList` | Liste für `"*"`; standardmäßig leer. |
| `command` | Argumentzeichenfolgen unter `COMMAND_MODEL` und `COMMAND_CRUD`; für den jeweiligen Gii-Befehl erforderlich. |
| `baseClasses` | Optionale teilweise Überschreibungen über die Konstanten unten; andere Werte behalten ihre Vorgaben. Ungültige Klassennamen werden vor der Generierung abgewiesen. |

| Konstante von `GenerateController` | Standardklasse |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Vorlagen-/Gii-Marker: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}`, `{{BaseResourceClassName}}`. Erzeugte Dateien oder `baseClasses` anpassen; öffentliche Einstellungen `root` oder `parts` existieren nicht. Die Vorlagen werden mit dem Paket geliefert.

<a id="generated-files"></a>
## Erzeugte Dateien und Architektur

Ablauf: **Controller → Service → ActiveRecord/Formular**, Darstellung über **Ressource → View**. `BaseController` erzeugt die in `SERVICE` angegebene Klasse über den Yii-Container. Gemeinsame Source-Modelle enthalten Gii-Ausgaben; Common-Item-Modelle erweitern diese, Backend-/Frontend-Item-Modelle ergänzen spezifisches Verhalten. Formulare erweitern das jeweilige Item-Modell. Backend-/Frontend-Services erstellen Formulare, der Common-Service ein Common-Item-Modell.

Suchmodelle bilden optionale Filter aus dem Tabellenschema. `MODEL` bestimmt die Item-Klasse; `search()` und `findWhere()` erhalten Bedingungen aus deren `find()` durch `andWhere`/`andFilterWhere`. Sichtbarkeitsregeln in den jeweiligen Item-/Form-Abfragen definieren; der Generator liefert keine anwendungsspezifische Autorisierung.

`dnk/setup` erzeugt folgende Dateien; `@` kennzeichnet Yii-Aliase:

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` und Interfaces verbleiben im Paket; Setup kopiert sie nicht nach `common/components`.

Vollständige Zuordnung für `dnk/list`, zugleich Ausgabe von `dnk/run`:

| Zuordnungsschlüssel | Zielvorlage |
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

`dnk/gen-tests` erzeugt:

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
## Services und Ressourcen

Nach dem Book-Schnellstart verwenden diese Aufrufe die generierten Klassen:

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

Generierte Common-Services konfigurieren nur `MODEL`. Für Formular-/Ressourcen-APIs einen Backend-/Frontend-Service verwenden oder `MODEL_FORM` und `RESOURCES` explizit konfigurieren.

`create(array $params = [], bool $is_save = true)` und `update($model, array $params = [], bool $is_save = true)` liefern das Modell, keinen Erfolgswert. `hasErrors()` / `getErrors()` prüfen und Speicherfehler behandeln; Datenbankausnahmen können weitergegeben werden. `false` überspringt `save()` samt automatischer Validierung; bei Bedarf `validate()` aufrufen. Das Update-Beispiel setzt erfolgreiche Erstellung und einen vorhandenen Datensatz voraus.

Beide Methoden akzeptieren flache Attribute oder Daten unter dem tatsächlichen `formName()` des Modells; zugewiesen werden nur sichere Attribute des aktiven Szenarios. Ein anderer Datentyp als Array unter diesem Schlüssel löst `yii\base\InvalidArgumentException` aus. `createInScenario($params, $scenario, $is_save)` setzt vor der Zuweisung ein vertrauenswürdiges Szenario und delegiert an `create()`, wobei mit der Basisimplementierung kompatible Überschreibungen erhalten bleiben. Das Szenario gilt nur für diesen Aufruf.

| API | Zweck |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Konfigurierte Modell-/Formklassennamen. |
| `getModel($params)` / `getForm($params)` | Instanz erzeugen; `$params` sind Konstruktorargumente, keine Attributdaten. |
| `getEntity()` | Common-Item-Modell bzw. Formular der Backend-/Frontend-Services. |
| `findWhere($criteria)` | `ActiveQuery` für `one()`, `all()` usw.; `findByID()` existiert nicht. |
| `getResource($key, $params)` | Klasse aus `RESOURCES` mit Konstruktorargumenten erzeugen; ohne Zuordnung `null`. |

Ressourcenschlüssel sind `BaseService::CREATE`, `READ`, `UPDATE`, `LIST`. Frontend-Services ordnen nur `LIST` und `READ` zu; die Konstante `FORM` hat keine Standardressource. Eine Read-Ressource benötigt einen vorhandenen Datensatz; `$controller` ist der aktuelle Web-Controller, `$found` darf nicht `null` sein:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` liefert `['R' => $resource]`. `render($controller, $template = null, $params = [])` rendert `TEMPLATE` und stellt `$R` in der View bereit. `GridViewResource($searchForm, $activeDataProvider)` enthält Suchmodell und Datenprovider für Listen. `json($params = [])` setzt das JSON-Antwortformat und liefert Ressourceneigenschaften zusammen mit `$params`; die Frontend-Freigabeliste `JSON_FIELDS` wird **nicht** angewendet. Für APIs öffentliche Daten explizit zusammenstellen. `content()` existiert nicht.

<a id="extension"></a>
## Controller-Erweiterung und Formularszenarien

Backend-Erweiterungspunkte: `beforeList()`, `beforeCreate()`, `beforeRead(int $id)`, `beforeUpdate(int $id)`. `SEARCH_MODEL` wählt das Suchmodell; `SEARCH` bleibt in generierten Controllern als Kompatibilitätsalias erhalten. `setTitle()` und `setBreadcrumbs()` setzen Seitenmetadaten.

`addMutation(new MutationResource($event, $action, $callback))` passt die Backend-Ressourcenerstellung an. Bei `EVENT_RESOURCE_INIT` erhält der erste Callback Konstruktorargumente und liefert eine Ressource; weitere Callbacks erhalten diese Ressource. `EVENT_RESOURCE_CREATED` transformiert die bereits erstellte Ressource. **Jeder Callback muss die Ressource zurückgeben.**

Diese Methode in `backend\controllers\BookController` ergänzen. Zuvor das Szenario `create` und seine sicheren Felder in `BookForm::scenarios()` definieren; der Name muss aus Anwendungscode stammen, nicht aus POST-Daten:

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

Die Backend-Erstellung übergibt das Formularszenario an `createInScenario()`. Generierte Formulare zeigen sichere skalare Attribute einschließlich skalarer virtueller Felder, überspringen aber Auto-Increment-Spalten und SQL-Ausdrücke als Standardwerte. Unveränderte Spalten behalten beim Erstellen ihre Datenbankstandardwerte. Arrays, Objekte, Beziehungen und Uploads benötigen eigene Widgets und Anwendungslogik.

<a id="security"></a>
## Zugriff, Routen und JSON

Backend-Zugriff ist standardmäßig gesperrt: `accessRoles = []` erlaubt niemandem Zugriff, Gäste werden immer abgewiesen. Explizite RBAC-Rollen oder -Berechtigungen konfigurieren. `['@']` erlaubt bewusst allen angemeldeten Benutzern Zugriff und eignet sich nur für genau diese Richtlinie. Yii kann Gäste zur Anmeldung umleiten; angemeldete Benutzer ohne Berechtigung erhalten 403. Frontend-Aktionen sind öffentlich, sofern die Anwendung keine Einschränkungen ergänzt.

DNKs Standardaktion ist `list`. Lesen/Aktualisieren fehlender Datensätze ergibt 404. Der generierte Frontend-`BookController` überschreibt `actionList()` / `actionRead()` für HTML und Suchfilter. `?type=json` allein aktiviert dort nicht das JSON-Verhalten der Basisklasse.

Für geerbte HTML-/JSON-Aktionen `frontend/controllers/BookApiController.php` erstellen:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` liefert `items` und `totalCount`; `index.php?r=book-api/read&id=1&type=json` liefert `item`. Die Basisliste nutzt Item-Abfrage und Paginierung, nicht die Filterlogik des generierten Suchformulars. `JSON_FIELDS` ist standardmäßig `['id']`; ein leeres Array veröffentlicht keine Felder. Yii-`fields()`-Einschränkungen gelten weiterhin. Passwörter, Tokens und private Felder nicht freigeben.

<a id="migrations"></a>
## Migrationen

Migrationen erweitern `BaseMigration`, setzen `$entity = Entity::…` und implementieren `constructTable(): array`. `safeUp()` ermittelt vor der Tabellenerstellung die Bezeichnung und ergänzt danach einen Tabellenkommentar; `safeDown()` löscht die Tabelle. `Tables::NAMES` liefert den Namen, `Setup` Tabellenoptionen und Zeitstempel-SQL. Vor Ausführung und Rollback prüfen. Eine vorhandene `*_create_table__<entity>.php` verhindert eine weitere Erstellungsmigration, auch bei aktivierter Überschreibung.

<a id="upgrades"></a>
## Erneute Generierung und Updates

| Vorgang | Vorhandene Dateien |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Ohne Rückfrage überspringen. |
| `run` / `list` / Modell-, Service-, Controller- oder View-Generatoren, overwrite `0` | Pro Datei nachfragen; nur der genaue Text `yes` erlaubt Ersetzen. |
| DNK overwrite `1` | Ersetzen ohne Zusammenführen manueller Änderungen; vorhandene Erstellungsmigrationen bleiben ausgenommen. |
| Gii-Befehle | Verwenden `--interactive` und `--overwrite` in `command`, kein DNK-overwrite-Argument. |

Anpassungen vorher committen oder sichern. Das Ersetzen ist pro Datei atomar; die gesamte Generierung ist keine Transaktion. Ein Composer-Update ändert Paketklassen, nicht bereits erzeugte Anwendungsdateien.

Bei vorhandenen Anwendungen neue Vorlagen mit gemeinsamen Controllern, source/item/search/form-Klassen, Services, Ressourcen und Views vergleichen. Änderungen gezielt übernehmen, `accessRoles` konfigurieren, szenariobewusste Delegation an `create()` erhalten und Formulare sowie öffentliches JSON prüfen. Manuell gepflegte Source-Klassen nicht mit Gii überschreiben. DNK- und Gii-CRUD-Controller/Views getrennt halten.

<a id="tests"></a>
## Tests

Aus einem Checkout **dieser Bibliothek**, nicht aus der generierten Anwendung:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` prüft ohne externe Abhängigkeiten lokale Links, übersetzte Abschnitte, identische Codebeispiele sowie Befehle und Dateizuordnungen. Die Prüfung läuft auch zuerst in `test:all`.

Regressionstests benötigen `pdo_sqlite` und erzeugen aus echten Vorlagen eine temporäre Anwendung. `DNK_TEST_VENDOR_PATH` erlaubt ein externes vendor-Verzeichnis. Die Codeception-Integration benötigt einen kompatiblen installierten Runner oder `DNK_TEST_CODECEPTION` mit dessen PHP-Einstiegspunkt/PHAR; ohne Runner schlägt die Prüfung fehl. `test:all` führt beide Suites aus. Details: [tests/README.md](../../tests/README.md).

`dnk/gen-tests` erzeugt Tests in den common/backend/frontend-Suites der Zielanwendung. Dies sind Gerüste: Fachassertionen, Fixtures und Suite-Konfiguration ergänzen. Der Actor-Typ unterstützt `Codeception\Actor`, auch mit `Support`-Namespace. Generieren führt die Tests nicht aus.

<a id="troubleshooting"></a>
## Fehlerbehebung

| Symptom | Prüfen |
| --- | --- |
| Unbekannter Befehl `dnk` / `gii` | Konsolen-`controllerMap`, Gii-Installation und Konsolenmodul. |
| Unbekannte Eigenschaft `parts` oder `root` | Entfernen; `entityList` und Yii-Aliase verwenden. |
| Backend-Anmeldeumleitung / 403 | Identität, explizite `accessRoles`, `authManager`-Zuweisungen. |
| 404 beim Lesen/Aktualisieren | Vorhandene ID, Item-/Form-Sichtbarkeit und Route. |
| Leere Liste bei `"*"` | `entityList` füllen, Stern in Anführungszeichen setzen. |
| Entitätsbezogene `InvalidConfigException` | Konstante, `Tables::NAMES` und gültige optionale Bezeichnungen. |
| Tabelle, Regeln oder Formularfelder fehlen | Schema anwenden, `gii-model` ausführen, Regeln/Szenarien/sichere Attribute prüfen. |
| Migrations-SQL-Fehler mit anderer Datenbank | MySQL-spezifische Basis, Optionen und Zeitstempel anpassen. |
| HTML statt JSON | Überschreibungen der Frontend-Basisaktionen prüfen. |
| Codeception-Runner fehlt | Integrationsanleitung in `tests/README.md` befolgen. |

Paketlizenz: MIT laut [composer.json](../../composer.json).
