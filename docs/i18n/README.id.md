# Yii2 DNK Generator

<a id="languages"></a>
## Bahasa

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | [Português (Brasil)](../../docs/i18n/README.pt-BR.md) | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | **Bahasa Indonesia**

[Pemilihan bahasa dan kebijakan terjemahan](../../docs/i18n/README.md).

DNK menghasilkan kode aplikasi **Yii 2 Advanced** serta menyediakan kelas dasar yang dipakainya: model, formulir, model pencarian, service, controller, resource, view, migrasi, dan kerangka pengujian. Template berada di [src/templates](../../src/templates); kelas runtime di [src/source](../../src/source). Panduan ini menjelaskan branch `master` saat ini; rilis lama dapat berbeda.

<a id="contents"></a>
## Daftar isi

- [Persyaratan](#requirements)
- [Mulai cepat](#quick-start)
- [Perintah dan konfigurasi](#commands)
- [File yang dihasilkan dan arsitektur](#generated-files)
- [Service dan resource](#api)
- [Ekstensi controller dan skenario formulir](#extension)
- [Akses, rute, dan JSON](#security)
- [Migrasi](#migrations)
- [Pembuatan ulang dan pembaruan](#upgrades)
- [Pengujian](#tests)
- [Pemecahan masalah](#troubleshooting)

<a id="requirements"></a>
## Persyaratan

- PHP **8.0 atau lebih baru**, Composer, dan ekstensi PHP yang diwajibkan versi Yii terpasang.
- Aplikasi Yii 2 Advanced yang sudah diinisialisasi, dengan alias `@common`, `@backend`, `@frontend`, `@console`, koneksi database, dan aset web yang berfungsi.
- `yiisoft/yii2-gii` diaktifkan di aplikasi **konsol** untuk `gii-model` dan `gii-crud`.
- Migrasi bawaan menggunakan pengaturan khusus **MySQL**: charset, collation, InnoDB, dan `ON UPDATE CURRENT_TIMESTAMP`. Sesuaikan kelas dasar migrasi dan definisi timestamp sebelum memakai database lain. Pengujian regresi SQLite tidak membuktikan portabilitas migrasi bawaan ini.

<a id="quick-start"></a>
## Mulai cepat: entitas Book

Jalankan perintah dari direktori utama aplikasi Yii Advanced, tempat file `yii` berada. Contoh ini mengasumsikan tabel baru `book` dan database pengembangan yang sudah dikonfigurasi.

### 1. Instalasi dan konfigurasi

Untuk memasang branch yang dijelaskan langsung dari repositori:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Gabungkan konfigurasi berikut ke `console/config/main.php`, tanpa menghapus pengaturan aplikasi yang ada. `command` berisi argumen Gii, bukan perintah shell lengkap. Argumen dan nama entitas harus dikendalikan pengembang.

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

Pada konfigurasi konsol pengembangan, aktifkan Gii sebelum mengembalikan `$config` jika belum aktif:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Membuat kelas bersama dan mendaftarkan entitas

```sh
php yii dnk/setup
```

Tambahkan konstanta berikut di dalam `common\components\Entity`, pertahankan konstanta yang sudah ada:

```php
public const BOOK = 'book';
```

Tambahkan entri berikut ke `common\components\db\Tables::NAMES`:

```php
Entity::BOOK => 'book',
```

Untuk label yang mudah dibaca, tambahkan entri berikut ke `Entity::DATA`:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

Potongan tersebut ditambahkan ke isi kelas/array yang ada, bukan file PHP mandiri. Label bersifat opsional: `getLabelOne()` dan `getLabelMany()` menggunakan nama tabel terdaftar jika label tidak tersedia. Entitas tak dikenal tanpa label/tabel yang valid menimbulkan `InvalidConfigException`.

### 3. Menghasilkan kode, melengkapi skema, dan menjalankan Gii

```sh
php yii dnk/run book
```

Awalnya `BookSource` hanya kelas sementara. Sebelum menggunakan model, edit file baru `console/migrations/m…_create_table__book.php`: tambahkan entri ini ke `constructTable()` bersama `id` dan kolom timestamp. Tinjau lalu terapkan migrasi yang tertunda:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

Gii mengganti `common/models/sources/BookSource.php` dengan ActiveRecord beserta skema, aturan, dan label. Simpan logika domain di kelas item/form/service agar tidak hilang ketika source dibuat ulang. Untuk tabel yang sudah ada, daftarkan namanya dan gunakan pembuatan selektif; jangan terapkan migrasi pembuatan tabel baru terhadapnya.

Sesuaikan `backend/views/book/book-list.php` dan `frontend/views/book/book-list.php`: daftar bawaan memuat kolom contoh `parent_id`, `name`, `url`, `category_image`, bukan kolom dari skema. Ganti entri atribut pada `columns` dengan berikut, tetapi pertahankan `SerialColumn` dan `ActionColumn` agar tautan tetap bekerja:

```php
'id',
 'title',
```

Pada `backend/views/book/book-read.php` dan `frontend/views/book/book-read.php`, atur atribut DetailView:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Mengatur akses dan membuka halaman

Tambahkan properti berikut ke `backend\controllers\BookController` yang dihasilkan:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Konfigurasikan `authManager` Yii, buat role atau permission `manageBooks`, lalu berikan kepada akun yang dituju. DNK tidak membuat akun maupun penetapan RBAC. Masuk menggunakan akun tersebut.

Dengan format URL query string bawaan, buka `index.php?r=book/list` dari root web backend, lalu `book/create`, `book/read&id=1`, dan `book/update&id=1` menggunakan ID yang ada. Frontend menyediakan `book/list` dan `book/read&id=1`. Jalur sebenarnya mengikuti URL manager aplikasi.

<a id="commands"></a>
## Perintah dan konfigurasi

`entity` menerima `book`, `book,author`, atau `"*"` dalam tanda kutip untuk seluruh isi `entityList`. Gunakan nama tunggal snake_case: `book_review` menghasilkan kelas `BookReview` dan rute `book-review`. `overwrite` adalah argumen boolean posisional opsional: bawaan `0`, atau `1` untuk mengganti file. Gii memiliki opsi tersendiri.

| Perintah setelah `php yii` | Hasil |
| --- | --- |
| `dnk/setup [overwrite]` | Kelas bersama aplikasi; tanpa argumen entitas. |
| `dnk/run entity [overwrite]` | Semua file entitas pada pemetaan di bawah; tidak mencakup setup, pengujian, atau eksekusi Gii. |
| `dnk/list entity map [overwrite]` | Hanya kunci pemetaan yang dipisahkan koma. |
| `dnk/gen-models entity [overwrite]` | Model source, item, form, dan search pada bagian aplikasi terkait. |
| `dnk/gen-services entity [overwrite]` | Service common, backend, dan frontend. |
| `dnk/gen-controllers entity [overwrite]` | Controller backend dan frontend. |
| `dnk/gen-backend-views entity [overwrite]` | Formulir serta view create, update, read, dan list backend. |
| `dnk/gen-frontend-views entity [overwrite]` | View list dan read frontend. |
| `dnk/gen-tests entity [overwrite]` | Delapan kerangka unit test Codeception. |
| `dnk/gii-model entity` | Menjalankan `gii/model` terkonfigurasi untuk setiap entitas. |
| `dnk/gii-crud entity` | Menjalankan `gii/crud` terkonfigurasi untuk setiap entitas. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` opsional karena halaman DNK sudah menggunakan resource dan view sendiri. Contoh konfigurasi Gii membuat `BookCrudController` terpisah berbasis `yii\web\Controller`, dengan view di `backend/views/book-crud`. Gii menggunakan `index/view/create/update/delete`; DNK menggunakan `list/read/create/update` tanpa aksi hapus. Controller Gii memerlukan kebijakan akses sendiri. Kegagalan proses anak Gii menghentikan perulangan entitas dan mengembalikan kode keluar nonnol.

| Properti generator | Fungsi |
| --- | --- |
| `entityList` | Daftar yang dikembangkan oleh `"*"`; bawaan kosong. |
| `command` | Argumen dengan kunci `COMMAND_MODEL` dan `COMMAND_CRUD`; diperlukan untuk perintah Gii terkait. |
| `baseClasses` | Penggantian sebagian yang opsional melalui konstanta berikut; nilai lain tetap memakai bawaan. Nama kelas tidak valid ditolak sebelum pembuatan. |

| Konstanta `GenerateController` | Kelas bawaan |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Penanda template/Gii: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}`, dan `{{BaseResourceClassName}}`. Sesuaikan file hasil atau `baseClasses`; tidak ada pengaturan publik `root` maupun `parts`. Template disertakan dalam paket.

<a id="generated-files"></a>
## File yang dihasilkan dan arsitektur

Alur: **controller → service → ActiveRecord/formulir**, serta **resource → view** untuk tampilan. `BaseController` membuat kelas dalam `SERVICE` melalui container Yii. Model source bersama menyimpan hasil Gii; model item common mewarisinya, lalu item backend/frontend menambahkan perilaku masing-masing. Formulir mewarisi item terkait. Service backend/frontend membuat formulir, sedangkan service common membuat item common.

Model pencarian menyusun filter opsional dari skema tabel. `MODEL` memilih kelas item; `search()` dan `findWhere()` mempertahankan kondisi `find()` kelas tersebut melalui `andWhere`/`andFilterWhere`. Tetapkan aturan visibilitas pada query item/form terkait; generator tidak menyediakan otorisasi khusus aplikasi secara otomatis.

`dnk/setup` membuat file berikut; `@` menandakan alias Yii:

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource`, dan interface tetap berada dalam paket; setup tidak menyalinnya ke `common/components`.

Pemetaan lengkap `dnk/list`, yang juga menjelaskan keluaran `dnk/run`:

| Kunci pemetaan | Template tujuan |
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

`dnk/gen-tests` membuat:

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
## Service dan resource

Setelah menyelesaikan contoh Book, panggilan berikut memakai kelas hasil pembuatan:

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

Service common yang dihasilkan hanya mengatur `MODEL`. Untuk API formulir/resource, gunakan service backend/frontend atau atur `MODEL_FORM` dan `RESOURCES` secara eksplisit.

`create(array $params = [], bool $is_save = true)` dan `update($model, array $params = [], bool $is_save = true)` mengembalikan model, bukan boolean keberhasilan. Periksa `hasErrors()` / `getErrors()` dan tangani kegagalan penyimpanan; exception database dapat diteruskan. `false` melewati `save()` beserta validasi otomatisnya; panggil `validate()` secara eksplisit bila perlu. Contoh update mengasumsikan pembuatan berhasil dan record tersedia.

Keduanya menerima atribut datar atau data di bawah `formName()` model yang sebenarnya; hanya atribut aman pada skenario aktif yang diisi. Nilai bukan array pada kunci formulir menimbulkan `yii\base\InvalidArgumentException`. `createInScenario($params, $scenario, $is_save)` menerapkan skenario tepercaya sebelum pengisian dan mendelegasikan ke `create()`, mempertahankan override yang sesuai dengan implementasi dasar. Skenario hanya berlaku untuk panggilan itu.

| API | Fungsi |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Nama kelas model/formulir yang dikonfigurasi. |
| `getModel($params)` / `getForm($params)` | Membuat instance; `$params` adalah argumen konstruktor, bukan data atribut. |
| `getEntity()` | Item common atau formulir service backend/frontend. |
| `findWhere($criteria)` | `ActiveQuery` untuk `one()`, `all()`, dan lainnya. Tidak ada `findByID()`. |
| `getResource($key, $params)` | Membuat kelas dari `RESOURCES` memakai argumen konstruktor; `null` jika kunci tidak dipetakan. |

Kunci resource adalah `BaseService::CREATE`, `READ`, `UPDATE`, dan `LIST`. Frontend hanya memetakan `LIST` dan `READ`; konstanta `FORM` tidak memiliki resource bawaan. Resource read memerlukan item yang ada; `$controller` adalah controller web saat ini dan `$found` tidak boleh `null`:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` mengembalikan `['R' => $resource]`. `render($controller, $template = null, $params = [])` merender `TEMPLATE` dan menyediakan `$R` dalam view. `GridViewResource($searchForm, $activeDataProvider)` membawa model pencarian dan provider untuk daftar. `json($params = [])` menetapkan format JSON dan mengembalikan properti resource yang digabung dengan `$params`; metode ini **tidak** menerapkan daftar izin frontend `JSON_FIELDS`. Susun data publik API secara eksplisit. Tidak ada metode `content()`.

<a id="extension"></a>
## Ekstensi controller dan skenario formulir

Titik ekstensi backend: `beforeList()`, `beforeCreate()`, `beforeRead(int $id)`, dan `beforeUpdate(int $id)`. `SEARCH_MODEL` memilih model pencarian; `SEARCH` tetap tersedia sebagai alias kompatibilitas di controller hasil pembuatan. `setTitle()` dan `setBreadcrumbs()` mengatur metadata halaman.

`addMutation(new MutationResource($event, $action, $callback))` mengubah konstruksi resource backend. Pada `EVENT_RESOURCE_INIT`, callback pertama menerima argumen konstruktor dan mengembalikan resource; callback berikutnya menerima resource itu. Pada `EVENT_RESOURCE_CREATED`, callback mengubah resource yang sudah dibuat. **Setiap callback harus mengembalikan resource.**

Tambahkan metode berikut ke `backend\controllers\BookController`. Definisikan dahulu skenario `create` dan atribut amannya dalam `BookForm::scenarios()`; nama skenario harus berasal dari kode aplikasi, bukan POST:

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

Pembuatan backend meneruskan skenario formulir ke `createInScenario()`. Formulir hasil pembuatan menampilkan atribut skalar aman, termasuk field virtual skalar, serta melewati kolom auto-increment dan nilai bawaan berupa ekspresi SQL. Kolom yang tidak diisi mempertahankan nilai bawaan database. Array, objek, relasi, dan unggahan memerlukan widget serta logika khusus.

<a id="security"></a>
## Akses, rute, dan JSON

Backend menolak akses secara bawaan: `accessRoles = []` tidak mengizinkan siapa pun; tamu selalu ditolak. Tetapkan role atau permission RBAC secara eksplisit. `['@']` sengaja mengizinkan semua pengguna terautentikasi; gunakan hanya jika itu memang kebijakannya. Yii dapat mengarahkan tamu ke login; pengguna terautentikasi tanpa izin menerima 403. Aksi frontend bersifat publik kecuali aplikasi menambahkan pembatasan.

Aksi bawaan DNK adalah `list`. Read/update untuk record yang tidak ada mengembalikan 404. `BookController` frontend hasil pembuatan meng-override `actionList()` / `actionRead()` untuk HTML dan filter pencarian. Menambahkan `?type=json` saja tidak mengaktifkan perilaku JSON kelas dasar.

Untuk mewarisi aksi HTML/JSON, buat `frontend/controllers/BookApiController.php`:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` mengembalikan `items` dan `totalCount`; `index.php?r=book-api/read&id=1&type=json` mengembalikan `item`. Daftar dasar memakai query item dan pagination, bukan logika filter formulir search yang dihasilkan. `JSON_FIELDS` bawaan adalah `['id']`; array kosong tidak mengekspos field. Pembatasan `fields()` Yii tetap berlaku. Jangan sertakan kata sandi, token, atau field privat.

<a id="migrations"></a>
## Migrasi

Migrasi mewarisi `BaseMigration`, menetapkan `$entity = Entity::…`, dan mengimplementasikan `constructTable(): array`. `safeUp()` menentukan label sebelum membuat tabel, lalu menambahkan komentar tabel; `safeDown()` menghapus tabel. `Tables::NAMES` memberikan nama, sedangkan `Setup` memberikan opsi dan SQL timestamp. Tinjau sebelum menjalankan atau membatalkan migrasi. File `*_create_table__<entity>.php` yang cocok mencegah pembuatan migrasi create berikutnya, bahkan saat overwrite aktif.

<a id="upgrades"></a>
## Pembuatan ulang dan pembaruan

| Operasi | File yang sudah ada |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Dilewati tanpa pertanyaan. |
| `run` / `list` / generator model, service, controller, atau view, overwrite `0` | Konfirmasi per file; hanya teks persis `yes` yang mengizinkan penggantian. |
| DNK overwrite `1` | Mengganti tanpa menggabungkan perubahan manual; migrasi create yang ada tetap dilewati. |
| Perintah Gii | Memakai `--interactive` dan `--overwrite` dalam `command`, tanpa argumen overwrite DNK. |

Commit atau cadangkan perubahan sebelum membuat ulang. Penggantian bersifat atomik per file; pembuatan banyak file bukan transaksi. Pembaruan paket Composer mengubah kelas runtime, tetapi tidak memperbarui file aplikasi yang sudah dihasilkan.

Untuk aplikasi yang ada, bandingkan template baru dengan controller bersama, kelas source/item/search/form, service, resource, dan view. Terapkan perubahan secara selektif, atur `accessRoles`, pertahankan delegasi `create()` yang mendukung skenario, lalu periksa formulir dan JSON publik. Jangan menimpa kelas source yang dikelola manual melalui Gii. Pisahkan controller/view DNK dan Gii CRUD.

<a id="tests"></a>
## Pengujian

Dari checkout **pustaka ini**, bukan aplikasi hasil pembuatan:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` memeriksa tautan lokal, bagian terjemahan, kesamaan contoh kode, serta perintah/pemetaan file tanpa dependensi eksternal. Pemeriksaan ini juga dijalankan pertama dalam `test:all`.

Suite regresi memerlukan `pdo_sqlite` dan menghasilkan aplikasi sementara dari template asli. `DNK_TEST_VENDOR_PATH` dapat menunjuk direktori vendor eksternal. Integrasi Codeception memerlukan runner kompatibel yang terpasang atau `DNK_TEST_CODECEPTION` menuju entry point PHP/PHAR-nya; tanpa runner pemeriksaan gagal. `test:all` menjalankan kedua suite. Lihat [tests/README.md](../../tests/README.md).

`dnk/gen-tests` membuat pengujian dalam suite common/backend/frontend aplikasi tujuan. Ini merupakan kerangka: tambahkan assertion domain, fixture, dan konfigurasi suite. Tipe actor menerima `Codeception\Actor`, termasuk namespace `Support`. Menghasilkan tes tidak menjalankannya.

<a id="troubleshooting"></a>
## Pemecahan masalah

| Gejala | Periksa |
| --- | --- |
| Perintah `dnk` / `gii` tidak dikenal | `controllerMap` konsol, instalasi Gii, dan modul konsol. |
| Properti `parts` atau `root` tidak dikenal | Hapus properti lama; gunakan `entityList` dan alias Yii. |
| Backend mengarah ke login / 403 | Identitas, `accessRoles` eksplisit, dan penetapan `authManager`. |
| Read/update menghasilkan 404 | ID yang ada, visibilitas item/form, serta rute. |
| Daftar `"*"` kosong | Isi `entityList` dan beri tanda kutip pada asterisk. |
| `InvalidConfigException` entitas | Konstanta, entri `Tables::NAMES`, dan label opsional yang valid. |
| Tabel, aturan, atau field tidak ada | Terapkan skema, jalankan `gii-model`, periksa aturan/skenario/atribut aman. |
| SQL migrasi gagal pada DBMS lain | Sesuaikan kelas dasar, opsi, dan timestamp khusus MySQL. |
| Menerima HTML alih-alih JSON | Periksa override aksi frontend dasar. |
| Runner Codeception tidak ditemukan | Ikuti pengaturan integrasi di `tests/README.md`. |

Lisensi paket: MIT, sebagaimana dinyatakan dalam [composer.json](../../composer.json).
