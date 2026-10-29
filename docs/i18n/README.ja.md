# Yii2 DNK Generator

<a id="languages"></a>
## 言語

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | **日本語** | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[言語の選定理由と翻訳の更新方針](../../docs/i18n/README.md)。

DNK は **Yii 2 Advanced** のアプリケーションコードを生成し、そのコードが使う基底クラスを提供します。対象はモデル、フォーム、検索モデル、サービス、コントローラー、リソース、ビュー、マイグレーション、テストのひな形です。テンプレートは [src/templates](../../src/templates)、実行時クラスは [src/source](../../src/source) にあります。このガイドは現在の `master` ブランチを対象としており、過去のリリースとは異なる場合があります。

<a id="contents"></a>
## 目次

- [動作要件](#requirements)
- [クイックスタート](#quick-start)
- [コマンドと設定](#commands)
- [生成ファイルと構成](#generated-files)
- [サービスとリソース](#api)
- [コントローラーの拡張とシナリオ](#extension)
- [アクセス制御、ルート、JSON](#security)
- [マイグレーション](#migrations)
- [再生成と更新](#upgrades)
- [テスト](#tests)
- [トラブルシューティング](#troubleshooting)

<a id="requirements"></a>
## 動作要件

- PHP **8.0 以降**、Composer、インストールした Yii が必要とする PHP 拡張。
- 初期化済みの Yii 2 Advanced アプリケーション。`@common`、`@backend`、`@frontend`、`@console` のエイリアス、データベース接続、Web アセットが設定されていること。
- `gii-model` と `gii-crud` には、**コンソールアプリケーション**で有効化した `yiisoft/yii2-gii` が必要です。
- 標準マイグレーションは **MySQL** 向けで、文字セット、照合順序、InnoDB、`ON UPDATE CURRENT_TIMESTAMP` を使用します。他の DB では基底マイグレーションとタイムスタンプ定義を変更してください。SQLite の回帰テストは、この標準設定の移植性を保証しません。

<a id="quick-start"></a>
## クイックスタート：Book エンティティ

`yii` がある Yii Advanced アプリケーションのルートでコマンドを実行します。新規テーブル `book` と、設定済みの開発用 DB を前提とします。

### 1. インストールと設定

このガイドが対象とするブランチをリポジトリから直接インストールします。

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

既存の設定を残して、以下を `console/config/main.php` に統合します。`command` は Gii の引数であり、完全なシェルコマンドではありません。引数とエンティティ名は開発者が管理してください。

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

Gii がまだ有効でなければ、開発環境のコンソール設定で `$config` を返す前に追加します。

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. 共通クラスの生成とエンティティ登録

```sh
php yii dnk/setup
```

既存の定数を残し、`common\components\Entity` クラス内に追加します。

```php
public const BOOK = 'book';
```

`common\components\db\Tables::NAMES` に追加します。

```php
Entity::BOOK => 'book',
```

表示用ラベルとして `Entity::DATA` に追加します。

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

これらは既存のクラスや配列へ追加する断片であり、独立した PHP ファイルではありません。ラベルは任意です。`getLabelOne()` と `getLabelMany()` はラベルがなければ登録済みのテーブル名を使います。有効なラベルまたはテーブル設定のない未知のエンティティは `InvalidConfigException` を発生させます。

### 3. 生成、スキーマの追加、Gii の実行

```sh
php yii dnk/run book
```

初期状態の `BookSource` は仮のクラスです。モデルを使用する前に、新しい `console/migrations/m…_create_table__book.php` を編集し、`constructTable()` の `id` とタイムスタンプ列に加えて次の項目を追加します。その後、未実行のマイグレーションを確認して適用します。

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii は `common/models/sources/BookSource.php` を、スキーマ、検証ルール、ラベルを持つ ActiveRecord に置き換えます。独自の業務ロジックは item/form/service クラスに記述し、source の再生成で消えないようにします。既存テーブルでは名前を登録して選択生成を使い、新しいテーブル作成マイグレーションは適用しないでください。

`backend/views/book/book-list.php` と `frontend/views/book/book-list.php` を調整します。標準一覧にはスキーマ由来ではないサンプル列 `parent_id`、`name`、`url`、`category_image` があります。`columns` の属性項目を以下に置き換え、リンクを維持するため既存の `SerialColumn` と `ActionColumn` は残してください。

```php
'id',
 'title',
```

`backend/views/book/book-read.php` と `frontend/views/book/book-read.php` の DetailView 属性を設定します。

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. アクセス設定とページの確認

生成した `backend\controllers\BookController` に追加します。

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Yii の `authManager` を設定し、`manageBooks` ロールまたは権限を作成して対象アカウントに割り当てます。DNK はアカウントや RBAC 割り当てを作成しません。そのアカウントでログインしてください。

標準のクエリ文字列 URL なら、backend の Web ルートから `index.php?r=book/list` を開き、続いて `book/create`、`book/read&id=1`、`book/update&id=1` を利用します。ID は存在する値に置き換えてください。Frontend は `book/list` と `book/read&id=1` を提供します。実際のパスはアプリケーションの URL マネージャーに依存します。

<a id="commands"></a>
## コマンドと設定

`entity` は `book`、`book,author`、または引用符付きの `"*"` を受け取ります。`"*"` は `entityList` 全体に展開されます。単数の snake_case を使ってください。`book_review` は `BookReview` クラスと `book-review` ルートになります。省略可能な位置引数 `overwrite` は真偽値で、既定値 `0`、ファイル置換は `1` です。Gii は独自のオプションを使います。

| `php yii` の後に続くコマンド | 結果 |
| --- | --- |
| `dnk/setup [overwrite]` | 共通クラスを生成。エンティティ引数は不要。 |
| `dnk/run entity [overwrite]` | 下表の全エンティティファイルを生成。setup、テスト、Gii 実行は含まない。 |
| `dnk/list entity map [overwrite]` | カンマ区切りのマッピングキーだけを生成。 |
| `dnk/gen-models entity [overwrite]` | 各アプリケーション部分の source、item、form、search モデル。 |
| `dnk/gen-services entity [overwrite]` | common、backend、frontend のサービス。 |
| `dnk/gen-controllers entity [overwrite]` | backend、frontend のコントローラー。 |
| `dnk/gen-backend-views entity [overwrite]` | backend のフォーム、作成、更新、詳細、一覧ビュー。 |
| `dnk/gen-frontend-views entity [overwrite]` | frontend の一覧、詳細ビュー。 |
| `dnk/gen-tests entity [overwrite]` | 8 個の Codeception 単体テストのひな形。 |
| `dnk/gii-model entity` | 各エンティティで設定済みの `gii/model` を実行。 |
| `dnk/gii-crud entity` | 各エンティティで設定済みの `gii/crud` を実行。 |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

DNK は独自のリソースとビューを持つため、`dnk/gii-crud` は任意です。Gii の設定例は `yii\web\Controller` を継承する別の `BookCrudController` を作成し、ビューを `backend/views/book-crud` に配置します。Gii は `index/view/create/update/delete`、DNK は `list/read/create/update` を使い、DNK に削除アクションはありません。Gii コントローラーには独自のアクセス制御が必要です。Gii 子プロセスが失敗するとエンティティ処理を停止し、その非ゼロ終了コードを返します。

| ジェネレーターのプロパティ | 用途 |
| --- | --- |
| `entityList` | `"*"` の展開先。既定では空。 |
| `command` | `COMMAND_MODEL` と `COMMAND_CRUD` をキーとする引数文字列。対応する Gii コマンドに必須。 |
| `baseClasses` | 下記定数による任意の部分上書き。未指定の値は既定値を維持。不正なクラス名は生成前に拒否。 |

| `GenerateController` の定数 | 既定のクラス |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

テンプレート/Gii のマーカー：`{{snake_case}}`、`{{CamelCase}}`、`{{UPPER_CASE}}`、`{{kebab-case}}`、`{{migrateFileName}}`、`{{BaseMigrationClassName}}`、`{{BaseModelClassName}}`、`{{BaseControllerClassName}}`、`{{BaseServiceClassName}}`、`{{BaseResourceClassName}}`。生成ファイルや `baseClasses` でカスタマイズします。公開設定 `root`、`parts` は存在せず、テンプレートはパッケージに含まれます。

<a id="generated-files"></a>
## 生成ファイルと構成

処理は**コントローラー → サービス → ActiveRecord/フォーム**、表示は**リソース → ビュー**です。`BaseController` は Yii コンテナで `SERVICE` のクラスを生成します。共通 source モデルは Gii 出力を保持し、common item モデルが継承、backend/frontend item モデルが各側の振る舞いを追加します。フォームは対応する item を継承します。Backend/frontend サービスはフォーム、common サービスは共通 item を作成します。

検索モデルはテーブル構造から任意フィルターを作成します。`MODEL` が item クラスを指定し、`search()` と `findWhere()` は `andWhere`/`andFilterWhere` によりその `find()` の条件を維持します。可視性ルールは関連する item/form のクエリに定義してください。アプリケーション固有の認可は自動生成されません。

`dnk/setup` の生成ファイルです。`@` は Yii エイリアスを示します。

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

`BaseService`、`BaseResource`、`BaseController`、`BaseMigration`、`GridViewResource`、`MutationResource` とインターフェースはパッケージ内に残り、setup で `common/components` へコピーされません。

`dnk/list` の全マッピングです。`dnk/run` の出力にも対応します。

| マッピングキー | 出力先テンプレート |
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

`dnk/gen-tests` の出力：

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
## サービスとリソース

Book のクイックスタート後、生成クラスで次の呼び出しができます。

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

生成される common サービスは `MODEL` のみを設定します。フォーム/リソース API には backend/frontend サービスを使うか、`MODEL_FORM` と `RESOURCES` を明示的に設定してください。

`create(array $params = [], bool $is_save = true)` と `update($model, array $params = [], bool $is_save = true)` は成功フラグではなくモデルを返します。`hasErrors()` / `getErrors()` を確認し、保存失敗を処理してください。DB 例外が伝播する場合があります。`false` は `save()` とその自動検証を省略するため、必要なら `validate()` を明示的に呼びます。更新例は作成成功と既存レコードを前提にしています。

両メソッドは平坦な属性配列、または実際の `formName()` をキーとするデータを受け取り、現在のシナリオの安全な属性だけを代入します。そのキーの値が配列でなければ `yii\base\InvalidArgumentException` になります。`createInScenario($params, $scenario, $is_save)` は代入前に信頼できるシナリオを設定し、`create()` に委譲することで基底実装と互換性のあるオーバーライドを維持します。シナリオはその呼び出しだけに有効です。

| API | 用途 |
| --- | --- |
| `getClassModel()` / `getClassForm()` | 設定されたモデル/フォームのクラス名。 |
| `getModel($params)` / `getForm($params)` | クラスを生成。`$params` はコンストラクター引数であり、属性データではない。 |
| `getEntity()` | 共通 item、または backend/frontend サービスのフォーム。 |
| `findWhere($criteria)` | `one()`、`all()` などを呼べる `ActiveQuery`。`findByID()` は存在しない。 |
| `getResource($key, $params)` | コンストラクター引数を使って `RESOURCES` のクラスを生成。未登録キーは `null`。 |

リソースキーは `BaseService::CREATE`、`READ`、`UPDATE`、`LIST` です。Frontend は `LIST` と `READ` のみを登録し、`FORM` 定数に標準リソースはありません。詳細リソースには既存レコードが必要です。`$controller` は現在の Web コントローラー、`$found` は `null` でない必要があります。

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` は `['R' => $resource]` を返します。`render($controller, $template = null, $params = [])` は `TEMPLATE` を描画し、ビューに `$R` を渡します。`GridViewResource($searchForm, $activeDataProvider)` は一覧用の検索モデルとデータプロバイダーを保持します。`json($params = [])` は JSON 形式を設定し、リソースのプロパティと `$params` を結合して返しますが、frontend の `JSON_FIELDS` 許可リストは**適用しません**。API の公開データは明示的に構成してください。`content()` は存在しません。

<a id="extension"></a>
## コントローラーの拡張とシナリオ

Backend の拡張点は `beforeList()`、`beforeCreate()`、`beforeRead(int $id)`、`beforeUpdate(int $id)` です。`SEARCH_MODEL` は検索モデルを指定し、生成コントローラーの `SEARCH` は互換エイリアスです。`setTitle()` と `setBreadcrumbs()` はページ情報を設定します。

`addMutation(new MutationResource($event, $action, $callback))` は backend リソースの生成を変更します。`EVENT_RESOURCE_INIT` の最初のコールバックはコンストラクター引数を受け取り、リソースを返します。以降はそのリソースを受け取ります。`EVENT_RESOURCE_CREATED` は生成済みリソースを変換します。**すべてのコールバックでリソースを返してください。**

次のメソッドを `backend\controllers\BookController` に追加します。先に `BookForm::scenarios()` で `create` シナリオと安全なフィールドを定義してください。シナリオ名は POST ではなくアプリケーションコードで決めます。

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

Backend の作成処理はフォームのシナリオを `createInScenario()` に渡します。生成フォームは安全なスカラー属性とスカラーの仮想フィールドを表示し、自動増分列や SQL 式の既定値は除外します。作成時に未指定の列は DB の既定値を保持します。配列、オブジェクト、関連、アップロードには専用ウィジェットと業務ロジックが必要です。

<a id="security"></a>
## アクセス制御、ルート、JSON

Backend は既定でアクセスを拒否します。`accessRoles = []` は誰にも許可せず、ゲストは常に拒否します。RBAC ロール/権限を明示的に設定してください。`['@']` はすべての認証済みユーザーを意図的に許可するため、その方針の場合にのみ使用します。Yii はゲストをログインへ転送する場合があり、認証済みで権限のないユーザーには 403 を返します。Frontend はアプリケーションが制限しない限り公開です。

DNK の既定アクションは `list` です。存在しないレコードの詳細/更新は 404 になります。生成された frontend `BookController` は `actionList()` / `actionRead()` を HTML と検索フィルター用に上書きしています。`?type=json` を追加するだけでは基底クラスの JSON 処理は有効になりません。

継承した HTML/JSON アクションを利用するには、`frontend/controllers/BookApiController.php` を作成します。

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` は `items` と `totalCount`、`index.php?r=book-api/read&id=1&type=json` は `item` を返します。基底一覧は item クエリとページ分割を使い、生成 search フォームのフィルターは使いません。`JSON_FIELDS` の既定値は `['id']`、空配列ならフィールドを公開しません。Yii の `fields()` 制限も適用されます。パスワード、トークン、非公開フィールドを許可リストに含めないでください。

<a id="migrations"></a>
## マイグレーション

生成マイグレーションは `BaseMigration` を継承し、`$entity = Entity::…` を設定、`constructTable(): array` を実装します。`safeUp()` は作成前にラベルを解決し、テーブル作成後にコメントを追加します。`safeDown()` はテーブルを削除します。`Tables::NAMES` がテーブル名、`Setup` がオプションと時刻の SQL を提供します。実行とロールバック前に確認してください。一致する `*_create_table__<entity>.php` が存在すると、上書き有効でも作成マイグレーションは再生成されません。

<a id="upgrades"></a>
## 再生成と更新

| 操作 | 既存ファイルの扱い |
| --- | --- |
| `setup` / `gen-tests`、overwrite `0` | 質問せずスキップ。 |
| `run` / `list` / モデル・サービス・コントローラー・ビュー生成、overwrite `0` | ファイルごとに確認し、正確な `yes` のみ置換を許可。 |
| DNK overwrite `1` | 手動変更をマージせず置換。既存の作成マイグレーションは引き続きスキップ。 |
| Gii コマンド | `command` 内の `--interactive`、`--overwrite` を使用。DNK の overwrite 引数は受け取らない。 |

再生成前に変更をコミットまたはバックアップしてください。置換は単一ファイル単位でアトミックですが、複数ファイルの生成はトランザクションではありません。Composer の更新はパッケージ内の実行時クラスを更新するだけで、生成済みファイルは更新しません。

既存アプリケーションでは、新テンプレートを共通コントローラー、source/item/search/form、サービス、リソース、ビューと比較します。必要な差分のみ適用し、`accessRoles` を設定、シナリオ対応の `create()` 委譲を維持し、フォームと公開 JSON を確認します。手動管理の source クラスを Gii で上書きしないでください。DNK と Gii CRUD のコントローラー/ビューは分離します。

<a id="tests"></a>
## テスト

生成アプリケーションではなく、**このライブラリのチェックアウト**で実行します。

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` は外部依存なしでローカルリンク、翻訳のセクション、コード例の一致、生成コマンドとファイルの対応を確認します。`test:all` でも最初に実行されます。

回帰テストは `pdo_sqlite` を必要とし、実際のテンプレートから一時アプリケーションを生成します。`DNK_TEST_VENDOR_PATH` で外部 vendor ディレクトリを指定できます。Codeception 統合には互換ランナーのインストール、または PHP エントリーポイント/PHAR を指す `DNK_TEST_CODECEPTION` が必要で、未指定・未インストールなら失敗します。`test:all` は両スイートを実行します。詳細は [tests/README.md](../../tests/README.md) を参照してください。

一方、`dnk/gen-tests` は対象アプリケーションの common/backend/frontend スイートにテストを生成します。ひな形なので業務アサーション、フィクスチャー、スイート設定を追加してください。Actor 型は `Codeception\Actor` を使い、`Support` 名前空間にも対応します。生成だけではテストは実行されません。

<a id="troubleshooting"></a>
## トラブルシューティング

| 症状 | 確認事項 |
| --- | --- |
| `dnk` / `gii` コマンドが不明 | コンソール `controllerMap`、Gii インストール、コンソールモジュール設定。 |
| `parts` / `root` プロパティが不明 | 古い設定を削除し、`entityList` と Yii エイリアスを使用。 |
| Backend のログイン転送 / 403 | 認証情報、明示的な `accessRoles`、`authManager` の割り当て。 |
| 詳細/更新が 404 | ID の存在、item/form の可視性、ルート。 |
| `"*"` が空リスト | `entityList` を設定し、アスタリスクを引用符で囲む。 |
| エンティティの `InvalidConfigException` | 定数、`Tables::NAMES`、任意ラベルの妥当性。 |
| テーブル・ルール・フォーム項目がない | スキーマ適用、`gii-model` 実行、ルール/シナリオ/安全属性の確認。 |
| 他の DB でマイグレーション SQL エラー | MySQL 固有の基底クラス、オプション、タイムスタンプを変更。 |
| JSON 要求に HTML が返る | frontend 基底アクションのオーバーライドを確認。 |
| Codeception ランナーがない | `tests/README.md` の統合設定に従う。 |

パッケージのライセンスは MIT です。[composer.json](../../composer.json) に記載されています。
