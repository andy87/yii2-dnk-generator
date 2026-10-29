# Yii2 DNK Generator

<a id="languages"></a>
## Langues

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | **Français** | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[Choix des langues et politique de traduction](../../docs/i18n/README.md).

DNK génère du code pour **Yii 2 Advanced** et fournit les classes de base utilisées : modèles, formulaires, modèles de recherche, services, contrôleurs, ressources, vues, migrations et squelettes de tests. Les modèles de génération sont dans [src/templates](../../src/templates), les classes d'exécution dans [src/source](../../src/source). Ce guide décrit la branche `master` actuelle ; les anciennes versions peuvent différer.

<a id="contents"></a>
## Sommaire

- [Prérequis](#requirements)
- [Démarrage rapide](#quick-start)
- [Commandes et configuration](#commands)
- [Fichiers générés et architecture](#generated-files)
- [Services et ressources](#api)
- [Extension des contrôleurs et scénarios](#extension)
- [Accès, routes et JSON](#security)
- [Migrations](#migrations)
- [Régénération et mises à jour](#upgrades)
- [Tests](#tests)
- [Dépannage](#troubleshooting)

<a id="requirements"></a>
## Prérequis

- PHP **8.0 ou ultérieur**, Composer et les extensions PHP exigées par la version de Yii installée.
- Une application Yii 2 Advanced initialisée, avec les alias `@common`, `@backend`, `@frontend`, `@console`, la base de données et les ressources web configurés.
- `yiisoft/yii2-gii` activé dans l'application **console** pour `gii-model` et `gii-crud`.
- Les migrations par défaut utilisent des paramètres propres à **MySQL** : jeu de caractères, collation, InnoDB et `ON UPDATE CURRENT_TIMESTAMP`. Adapter la classe de migration et les horodatages avant d'utiliser une autre base. Les tests SQLite ne prouvent pas la portabilité de ces migrations.

<a id="quick-start"></a>
## Démarrage rapide : une entité Book

Exécuter les commandes à la racine de l'application Yii Advanced, où se trouve `yii`. L'exemple suppose une nouvelle table `book` et une base de développement configurée.

### 1. Installer et configurer

Pour installer directement depuis le dépôt la branche décrite :

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Fusionner cette configuration avec `console/config/main.php`, en conservant les paramètres existants. `command` contient des arguments Gii, pas des commandes shell complètes. Les arguments et noms d'entités doivent rester sous le contrôle du développeur.

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

Dans la configuration console de développement, activer Gii avant de retourner `$config` si ce n'est pas déjà fait :

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Créer les classes communes et enregistrer l'entité

```sh
php yii dnk/setup
```

Ajouter cette constante dans `common\components\Entity`, sans supprimer les constantes existantes :

```php
public const BOOK = 'book';
```

Ajouter cette entrée dans `common\components\db\Tables::NAMES` :

```php
Entity::BOOK => 'book',
```

Pour des libellés lisibles, ajouter cette entrée dans `Entity::DATA` :

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

Ces fragments complètent des corps de classes ou tableaux existants ; ce ne sont pas des fichiers PHP autonomes. Les libellés sont facultatifs : `getLabelOne()` et `getLabelMany()` utilisent à défaut le nom de table enregistré. Une entité inconnue sans libellé/table valide déclenche `InvalidConfigException`.

### 3. Générer, compléter le schéma et lancer Gii

```sh
php yii dnk/run book
```

`BookSource` est initialement une classe provisoire. Avant d'utiliser les modèles, modifier `console/migrations/m…_create_table__book.php` : ajouter cette entrée à `constructTable()`, à côté de `id` et des horodatages. Vérifier puis appliquer les migrations en attente :

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii remplace `common/models/sources/BookSource.php` par un ActiveRecord avec schéma, règles et libellés. Placer la logique métier dans les classes item/form/service afin de la conserver lors d'une régénération. Pour une table existante, enregistrer son nom et générer sélectivement ; ne pas lui appliquer une nouvelle migration de création.

Adapter `backend/views/book/book-list.php` et `frontend/views/book/book-list.php` : les listes contiennent les colonnes exemples `parent_id`, `name`, `url`, `category_image`, non déduites du schéma. Remplacer leurs attributs dans `columns` par les suivants, en conservant `SerialColumn` et `ActionColumn` pour préserver les liens :

```php
'id',
 'title',
```

Dans `backend/views/book/book-read.php` et `frontend/views/book/book-read.php`, définir les attributs DetailView :

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Configurer les accès et ouvrir les pages

Ajouter cette propriété au `backend\controllers\BookController` généré :

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Configurer `authManager` de Yii, créer le rôle ou la permission `manageBooks` et l'attribuer au compte prévu. DNK ne crée ni comptes ni attributions RBAC. Se connecter avec ce compte.

Avec les URL par défaut, ouvrir `index.php?r=book/list` depuis la racine web du backend, puis `book/create`, `book/read&id=1` et `book/update&id=1` avec un ID existant. Le frontend fournit `book/list` et `book/read&id=1`. Les chemins dépendent du gestionnaire d'URL de l'application.

<a id="commands"></a>
## Commandes et configuration

`entity` accepte `book`, `book,author` ou `"*"` entre guillemets pour tous les éléments de `entityList`. Utiliser le singulier en snake_case : `book_review` produit `BookReview` et la route `book-review`. `overwrite` est un booléen positionnel facultatif : `0` par défaut, `1` pour remplacer les fichiers. Gii possède ses propres options.

| Commande après `php yii` | Résultat |
| --- | --- |
| `dnk/setup [overwrite]` | Classes communes ; aucun argument d'entité. |
| `dnk/run entity [overwrite]` | Tous les fichiers de la table ci-dessous, sans classes setup, tests ni exécution Gii. |
| `dnk/list entity map [overwrite]` | Uniquement les clés de génération séparées par des virgules. |
| `dnk/gen-models entity [overwrite]` | Modèles source, item, form et search des parties concernées. |
| `dnk/gen-services entity [overwrite]` | Services common, backend et frontend. |
| `dnk/gen-controllers entity [overwrite]` | Contrôleurs backend et frontend. |
| `dnk/gen-backend-views entity [overwrite]` | Formulaire et vues de création, modification, lecture et liste backend. |
| `dnk/gen-frontend-views entity [overwrite]` | Vues de liste et lecture frontend. |
| `dnk/gen-tests entity [overwrite]` | Huit squelettes de tests unitaires Codeception. |
| `dnk/gii-model entity` | Exécute `gii/model` configuré pour chaque entité. |
| `dnk/gii-crud entity` | Exécute `gii/crud` configuré pour chaque entité. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` est facultatif : DNK possède déjà ses ressources et vues. La configuration d'exemple crée un `BookCrudController` distinct, basé sur `yii\web\Controller`, avec des vues dans `backend/views/book-crud`. Gii utilise `index/view/create/update/delete` ; DNK utilise `list/read/create/update` et ne fournit pas de suppression. Le contrôleur Gii nécessite sa propre politique d'accès. L'échec d'un processus Gii interrompt la boucle des entités et renvoie son code de sortie non nul.

| Propriété du générateur | Rôle |
| --- | --- |
| `entityList` | Liste développée par `"*"`, vide par défaut. |
| `command` | Arguments indexés par `COMMAND_MODEL` et `COMMAND_CRUD`, requis pour la commande Gii correspondante. |
| `baseClasses` | Remplacements partiels facultatifs avec les constantes ci-dessous ; les autres valeurs restent inchangées. Les noms invalides sont rejetés avant génération. |

| Constante de `GenerateController` | Classe par défaut |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Marqueurs des modèles/Gii : `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}`, `{{BaseResourceClassName}}`. Personnaliser les fichiers générés ou `baseClasses` ; les paramètres publics `root` et `parts` n'existent pas. Les modèles sont livrés dans le paquet.

<a id="generated-files"></a>
## Fichiers générés et architecture

Flux : **contrôleur → service → ActiveRecord/formulaire**, avec **ressource → vue** pour l'affichage. `BaseController` instancie la classe de `SERVICE` via le conteneur Yii. Les modèles source communs contiennent la sortie Gii ; les item communs les étendent, puis les item backend/frontend ajoutent les comportements spécifiques. Les formulaires étendent l'item correspondant. Les services backend/frontend créent des formulaires ; le service common crée l'item commun.

Les modèles de recherche construisent des filtres facultatifs à partir du schéma. `MODEL` choisit la classe item ; `search()` et `findWhere()` préservent ses conditions `find()` avec `andWhere`/`andFilterWhere`. Définir la visibilité dans les requêtes item/form concernées ; le générateur ne fournit pas l'autorisation propre à l'application.

`dnk/setup` crée ces fichiers ; `@` désigne un alias Yii :

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` et les interfaces restent dans le paquet ; setup ne les copie pas dans `common/components`.

Correspondances complètes de `dnk/list`, également produites par `dnk/run` :

| Clé de génération | Modèle de destination |
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

`dnk/gen-tests` crée :

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
## Services et ressources

Après le démarrage rapide Book, ces appels utilisent les classes générées :

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

Les services common générés configurent uniquement `MODEL`. Pour les API de formulaires/ressources, utiliser un service backend/frontend ou configurer explicitement `MODEL_FORM` et `RESOURCES`.

`create(array $params = [], bool $is_save = true)` et `update($model, array $params = [], bool $is_save = true)` renvoient le modèle, pas un booléen de réussite. Vérifier `hasErrors()` / `getErrors()` et traiter les échecs de persistance ; les exceptions de base de données peuvent remonter. `false` ignore `save()` et sa validation automatique ; appeler explicitement `validate()` si nécessaire. L'exemple de modification suppose une création réussie et un enregistrement existant.

Les deux méthodes acceptent des attributs simples ou des données sous le véritable `formName()` du modèle ; seuls les attributs sûrs du scénario actif sont affectés. Une valeur autre qu'un tableau sous cette clé provoque `yii\base\InvalidArgumentException`. `createInScenario($params, $scenario, $is_save)` applique un scénario fiable avant l'affectation et délègue à `create()`, en conservant les redéfinitions compatibles avec l'implémentation de base. Le scénario ne vaut que pour cet appel.

| API | Rôle |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Noms des classes de modèle/formulaire configurées. |
| `getModel($params)` / `getForm($params)` | Instanciation ; `$params` contient les arguments du constructeur, pas les attributs. |
| `getEntity()` | Item commun ou formulaire des services backend/frontend. |
| `findWhere($criteria)` | `ActiveQuery` pour `one()`, `all()`, etc. `findByID()` n'existe pas. |
| `getResource($key, $params)` | Instancie une classe de `RESOURCES` avec ses arguments ; renvoie `null` sans correspondance. |

Les clés sont `BaseService::CREATE`, `READ`, `UPDATE`, `LIST`. Le frontend définit uniquement `LIST` et `READ` ; `FORM` est une constante sans ressource par défaut. Une ressource de lecture exige un élément existant ; `$controller` est le contrôleur web courant et `$found` ne doit pas être `null` :

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` renvoie `['R' => $resource]`. `render($controller, $template = null, $params = [])` affiche `TEMPLATE` et expose `$R` dans la vue. `GridViewResource($searchForm, $activeDataProvider)` contient le modèle de recherche et le fournisseur de données de liste. `json($params = [])` sélectionne JSON et renvoie les propriétés de la ressource fusionnées avec `$params` ; la liste frontend `JSON_FIELDS` **n'est pas appliquée**. Construire explicitement les données publiques d'une API. `content()` n'existe pas.

<a id="extension"></a>
## Extension des contrôleurs et scénarios

Les points d'extension backend sont `beforeList()`, `beforeCreate()`, `beforeRead(int $id)`, `beforeUpdate(int $id)`. `SEARCH_MODEL` sélectionne le modèle de recherche ; `SEARCH` reste un alias de compatibilité dans les contrôleurs générés. `setTitle()` et `setBreadcrumbs()` définissent les métadonnées de page.

`addMutation(new MutationResource($event, $action, $callback))` personnalise la construction des ressources backend. À `EVENT_RESOURCE_INIT`, le premier callback reçoit les arguments du constructeur et renvoie une ressource ; les suivants reçoivent cette ressource. À `EVENT_RESOURCE_CREATED`, les callbacks transforment la ressource construite. **Chaque callback doit renvoyer la ressource.**

Ajouter cette méthode dans `backend\controllers\BookController`. Définir d'abord le scénario `create` et ses champs sûrs dans `BookForm::scenarios()` ; son nom doit provenir du code applicatif, pas de POST :

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

La création backend transmet le scénario à `createInScenario()`. Les formulaires générés affichent les attributs scalaires sûrs, y compris les champs virtuels scalaires, et ignorent les colonnes auto-incrémentées et valeurs par défaut exprimées en SQL. Les colonnes non modifiées conservent leurs valeurs par défaut en base. Tableaux, objets, relations et téléversements nécessitent des widgets et une logique spécifiques.

<a id="security"></a>
## Accès, routes et JSON

Le backend refuse l'accès par défaut : `accessRoles = []` n'autorise personne ; les visiteurs sont toujours refusés. Configurer explicitement rôles ou permissions RBAC. `['@']` autorise volontairement tous les utilisateurs authentifiés ; ne l'utiliser que si telle est la politique. Yii peut rediriger un visiteur vers la connexion ; un utilisateur authentifié sans permission reçoit 403. Les actions frontend sont publiques sauf restrictions ajoutées par l'application.

L'action DNK par défaut est `list`. Lire/modifier un enregistrement absent renvoie 404. Le `BookController` frontend généré redéfinit `actionList()` / `actionRead()` pour HTML et les filtres de recherche. Ajouter `?type=json` ne suffit pas à activer le comportement JSON de base.

Pour hériter des actions HTML/JSON, créer `frontend/controllers/BookApiController.php` :

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` renvoie `items` et `totalCount` ; `index.php?r=book-api/read&id=1&type=json` renvoie `item`. La liste de base utilise la requête item et la pagination, sans les filtres du formulaire search généré. `JSON_FIELDS` vaut `['id']` par défaut ; un tableau vide n'expose aucun champ. Les restrictions Yii `fields()` restent applicables. Exclure mots de passe, jetons et champs privés.

<a id="migrations"></a>
## Migrations

Les migrations étendent `BaseMigration`, définissent `$entity = Entity::…` et implémentent `constructTable(): array`. `safeUp()` résout le libellé avant de créer la table puis ajoute un commentaire ; `safeDown()` supprime la table. `Tables::NAMES` fournit le nom, `Setup` les options et le SQL des horodatages. Vérifier avant exécution ou annulation. Un fichier `*_create_table__<entity>.php` existant empêche une nouvelle migration de création, même avec écrasement activé.

<a id="upgrades"></a>
## Régénération et mises à jour

| Opération | Fichiers existants |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Ignorés sans demander. |
| `run` / `list` / générateurs de modèles, services, contrôleurs ou vues, overwrite `0` | Confirmation par fichier ; seul le texte exact `yes` autorise le remplacement. |
| DNK overwrite `1` | Remplace sans fusionner les modifications manuelles ; les migrations de création restent ignorées. |
| Commandes Gii | Utilisent `--interactive` et `--overwrite` dans `command`, sans argument overwrite DNK. |

Créer un commit ou une sauvegarde avant de régénérer. Le remplacement est atomique par fichier ; la génération complète n'est pas une transaction. Mettre à jour le paquet Composer modifie les classes d'exécution, pas les fichiers déjà générés.

Pour une application existante, comparer les nouvelles templates aux contrôleurs communs, classes source/item/search/form, services, ressources et vues. Appliquer sélectivement, configurer `accessRoles`, préserver la délégation à `create()` avec scénarios et vérifier formulaires et JSON public. Ne pas écraser avec Gii les classes source maintenues manuellement. Séparer contrôleurs et vues DNK et Gii CRUD.

<a id="tests"></a>
## Tests

Depuis une copie de **cette bibliothèque**, pas depuis l'application générée :

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` vérifie sans dépendances externes les liens locaux, les sections traduites, les exemples identiques et les commandes/chemins du générateur. Il est aussi exécuté en premier par `test:all`.

La suite de régression nécessite `pdo_sqlite` et génère une application temporaire à partir des vraies templates. `DNK_TEST_VENDOR_PATH` permet un répertoire vendor externe. L'intégration Codeception nécessite un exécuteur compatible installé ou `DNK_TEST_CODECEPTION` pointant sur son entrée PHP/PHAR ; son absence fait échouer le contrôle. `test:all` exécute les deux suites. Voir [tests/README.md](../../tests/README.md).

`dnk/gen-tests` crée des tests dans les suites common/backend/frontend de l'application cible. Ce sont des squelettes : ajouter assertions métier, fixtures et configuration des suites. Le typage accepte `Codeception\Actor`, y compris les espaces de noms `Support`. Générer les tests ne les exécute pas.

<a id="troubleshooting"></a>
## Dépannage

| Symptôme | Vérifier |
| --- | --- |
| Commande `dnk` / `gii` inconnue | `controllerMap` console, installation Gii et module console. |
| Propriété `parts` ou `root` inconnue | La supprimer ; utiliser `entityList` et les alias Yii. |
| Redirection de connexion / 403 backend | Identité, `accessRoles` explicites et attributions `authManager`. |
| 404 en lecture/modification | ID existant, visibilité item/form et route. |
| Liste vide pour `"*"` | Remplir `entityList`, entourer l'astérisque de guillemets. |
| `InvalidConfigException` d'entité | Constante, entrée `Tables::NAMES`, libellés facultatifs valides. |
| Table, règles ou champs absents | Appliquer le schéma, lancer `gii-model`, vérifier règles/scénarios/attributs sûrs. |
| Erreur SQL de migration sur un autre SGBD | Adapter base, options et horodatages MySQL. |
| HTML reçu au lieu de JSON | Vérifier les redéfinitions des actions frontend de base. |
| Exécuteur Codeception introuvable | Suivre la configuration dans `tests/README.md`. |

Licence du paquet : MIT, déclarée dans [composer.json](../../composer.json).
