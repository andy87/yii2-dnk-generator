# Yii2 DNK Generator

<a id="languages"></a>
## Idiomas

[English](../../README.md) | [Русский](../../docs/i18n/README.ru.md) | [Қазақша](../../docs/i18n/README.kk.md) | [简体中文](../../docs/i18n/README.zh-CN.md) | [Español](../../docs/i18n/README.es.md) | **Português (Brasil)** | [Deutsch](../../docs/i18n/README.de.md) | [Français](../../docs/i18n/README.fr.md) | [日本語](../../docs/i18n/README.ja.md) | [Bahasa Indonesia](../../docs/i18n/README.id.md)

[Seleção de idiomas e política de tradução](../../docs/i18n/README.md).

O DNK gera código para **Yii 2 Advanced** e fornece as classes base utilizadas: modelos, formulários, modelos de busca, serviços, controllers, recursos, views, migrações e esqueletos de testes. As templates ficam em [src/templates](../../src/templates), e as classes de execução em [src/source](../../src/source). Este guia descreve a branch `master` atual; versões antigas podem ser diferentes.

<a id="contents"></a>
## Conteúdo

- [Requisitos](#requirements)
- [Início rápido](#quick-start)
- [Comandos e configuração](#commands)
- [Arquivos gerados e arquitetura](#generated-files)
- [Serviços e recursos](#api)
- [Extensão de controllers e cenários](#extension)
- [Acesso, rotas e JSON](#security)
- [Migrações](#migrations)
- [Regeneração e atualizações](#upgrades)
- [Testes](#tests)
- [Solução de problemas](#troubleshooting)

<a id="requirements"></a>
## Requisitos

- PHP **8.0 ou superior**, Composer e extensões PHP exigidas pela versão instalada do Yii.
- Aplicação Yii 2 Advanced inicializada, com aliases `@common`, `@backend`, `@frontend`, `@console`, banco de dados e assets web configurados.
- `yiisoft/yii2-gii` habilitado na aplicação de **console** para `gii-model` e `gii-crud`.
- As migrações padrão usam opções de **MySQL**: charset, collation, InnoDB e `ON UPDATE CURRENT_TIMESTAMP`. Adapte a classe base e os campos de data/hora antes de usar outro banco. Testes de regressão com SQLite não comprovam a portabilidade dessas migrações.

<a id="quick-start"></a>
## Início rápido: uma entidade Book

Execute os comandos na raiz da aplicação Yii Advanced, onde fica `yii`. O exemplo pressupõe uma nova tabela `book` e um banco de desenvolvimento configurado.

### 1. Instalar e configurar

Para instalar a branch descrita diretamente do repositório:

```sh
composer config repositories.dnk vcs https://github.com/andy87/yii2-dnk-generator
composer require "andy87/yii2-dnk-generator:dev-master"
composer require --dev yiisoft/yii2-gii
```

Incorpore esta configuração a `console/config/main.php`, mantendo as configurações existentes. `command` contém argumentos do Gii, não comandos completos de shell. Os argumentos e nomes de entidades devem permanecer sob controle do desenvolvedor.

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

Na configuração de console de desenvolvimento, habilite Gii antes de retornar `$config`, caso ainda não esteja habilitado:

```php
if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => yii\gii\Module::class];
}
```

### 2. Criar classes compartilhadas e registrar a entidade

```sh
php yii dnk/setup
```

Adicione este membro a `common\components\Entity`, preservando as constantes existentes:

```php
public const BOOK = 'book';
```

Adicione esta entrada a `common\components\db\Tables::NAMES`:

```php
Entity::BOOK => 'book',
```

Para rótulos legíveis, adicione esta entrada a `Entity::DATA`:

```php
self::BOOK => [
    self::TABLE_NAME => Tables::NAMES[self::BOOK],
    self::LABELS => [self::NAME_MANY => 'Books', self::NAME_ONE => 'Book'],
],
```

São acréscimos aos corpos de classes e arrays existentes, não arquivos PHP independentes. Os rótulos são opcionais: `getLabelOne()` e `getLabelMany()` usam o nome da tabela registrada quando não há rótulos. Uma entidade desconhecida sem rótulo ou tabela válida gera `InvalidConfigException`.

### 3. Gerar, ampliar o esquema e executar Gii

```sh
php yii dnk/run book
```

Inicialmente, `BookSource` é um esqueleto. Antes de usar os modelos, edite `console/migrations/m…_create_table__book.php`: adicione esta entrada a `constructTable()`, junto a `id` e aos campos de data/hora. Revise e aplique as migrações pendentes:

```php
'title' => $this->string(200)->notNull(),
```

```sh
php yii migrate
php yii dnk/gii-model book
```

O Gii substitui `common/models/sources/BookSource.php` por um ActiveRecord com esquema, regras e rótulos. Coloque a lógica de domínio nas classes item/form/service para preservá-la na regeneração. Para uma tabela existente, registre o nome e use geração seletiva; não aplique outra migração de criação a ela.

Adapte `backend/views/book/book-list.php` e `frontend/views/book/book-list.php`: as listas trazem colunas de exemplo `parent_id`, `name`, `url` e `category_image`, não colunas derivadas do esquema. Substitua seus atributos em `columns` pelos seguintes, mantendo `SerialColumn` e `ActionColumn` para preservar os links:

```php
'id',
 'title',
```

Em `backend/views/book/book-read.php` e `frontend/views/book/book-read.php`, configure os atributos de DetailView:

```php
'attributes' => ['id', 'title', 'created_at:datetime'],
```

### 4. Configurar o acesso e abrir as páginas

Adicione esta propriedade ao `backend\controllers\BookController` gerado:

```php
/** @var string[] RBAC roles or permissions allowed to manage books. */
public array $accessRoles = ['manageBooks'];
```

Configure o `authManager` do Yii, crie o papel ou permissão `manageBooks` e atribua à conta correta. DNK não cria contas nem atribuições RBAC. Entre com essa conta.

Com o formato padrão de URL, abra `index.php?r=book/list` na raiz web do backend; depois `book/create`, `book/read&id=1` e `book/update&id=1`, usando um ID existente. O frontend oferece `book/list` e `book/read&id=1`. Os caminhos dependem do gerenciador de URLs da aplicação.

<a id="commands"></a>
## Comandos e configuração

`entity` aceita `book`, `book,author` ou `"*"` entre aspas para todos os itens de `entityList`. Use nomes singulares em snake_case: `book_review` gera `BookReview` e a rota `book-review`. `overwrite` é um booleano posicional opcional: `0` por padrão, `1` para substituir arquivos. Gii tem opções próprias.

| Comando após `php yii` | Resultado |
| --- | --- |
| `dnk/setup [overwrite]` | Classes compartilhadas; não recebe entidade. |
| `dnk/run entity [overwrite]` | Todos os arquivos do mapa abaixo; exclui setup, testes e execução do Gii. |
| `dnk/list entity map [overwrite]` | Somente as chaves do mapa separadas por vírgulas. |
| `dnk/gen-models entity [overwrite]` | Modelos source, item, form e search das partes correspondentes. |
| `dnk/gen-services entity [overwrite]` | Serviços common, backend e frontend. |
| `dnk/gen-controllers entity [overwrite]` | Controllers backend e frontend. |
| `dnk/gen-backend-views entity [overwrite]` | Formulário e views de criação, atualização, leitura e listagem do backend. |
| `dnk/gen-frontend-views entity [overwrite]` | Views de listagem e leitura do frontend. |
| `dnk/gen-tests entity [overwrite]` | Oito esqueletos de testes unitários Codeception. |
| `dnk/gii-model entity` | Executa `gii/model` configurado para cada entidade. |
| `dnk/gii-crud entity` | Executa `gii/crud` configurado para cada entidade. |

```sh
php yii dnk/run book,author
php yii dnk/run "*"
php yii dnk/list book common-service,common-model-source,frontend-service
php yii dnk/gen-services book 1
php yii dnk/gen-tests book
php yii dnk/gii-crud book
```

`dnk/gii-crud` é opcional: DNK já usa recursos e views próprios. A configuração de exemplo cria um `BookCrudController` separado, baseado em `yii\web\Controller`, com views em `backend/views/book-crud`. Gii usa `index/view/create/update/delete`; DNK usa `list/read/create/update` e não fornece exclusão. O controller Gii precisa de política de acesso própria. Uma falha no processo Gii interrompe o laço de entidades e retorna seu código de saída diferente de zero.

| Propriedade do gerador | Finalidade |
| --- | --- |
| `entityList` | Lista expandida por `"*"`; vazia por padrão. |
| `command` | Argumentos nas chaves `COMMAND_MODEL` e `COMMAND_CRUD`; necessários para o comando Gii correspondente. |
| `baseClasses` | Substituições parciais opcionais pelas constantes abaixo; os demais valores são mantidos. Nomes inválidos são rejeitados antes da geração. |

| Constante de `GenerateController` | Classe padrão |
| --- | --- |
| `BASE_MIGRATE_CLASS` | `andy87\dnk\source\base\BaseMigration` |
| `BASE_CONTROLLER_CLASS` | `andy87\dnk\source\base\BaseController` |
| `BASE_MODEL_CLASS` | `common\components\core\BaseModel` |
| `BASE_SERVICE_CLASS` | `andy87\dnk\source\base\BaseService` |
| `BASE_RESOURCE_CLASS` | `andy87\dnk\source\base\BaseResource` |

Marcadores das templates/Gii: `{{snake_case}}`, `{{CamelCase}}`, `{{UPPER_CASE}}`, `{{kebab-case}}`, `{{migrateFileName}}`, `{{BaseMigrationClassName}}`, `{{BaseModelClassName}}`, `{{BaseControllerClassName}}`, `{{BaseServiceClassName}}` e `{{BaseResourceClassName}}`. Personalize os arquivos gerados ou `baseClasses`; não existem configurações públicas `root` e `parts`. As templates são distribuídas no pacote.

<a id="generated-files"></a>
## Arquivos gerados e arquitetura

Fluxo: **controller → serviço → ActiveRecord/formulário**, com **recurso → view** para apresentação. `BaseController` instancia a classe de `SERVICE` pelo contêiner do Yii. Modelos source comuns contêm a saída do Gii; modelos item comuns os estendem, e os item backend/frontend acrescentam comportamento específico. Formulários estendem o item correspondente. Serviços backend/frontend criam formulários; o serviço common cria o item comum.

Modelos de busca constroem filtros opcionais a partir do esquema. `MODEL` seleciona a classe item; `search()` e `findWhere()` preservam suas condições de `find()` com `andWhere`/`andFilterWhere`. Defina regras de visibilidade nas consultas item/form pertinentes; o gerador não fornece autorização específica da aplicação.

`dnk/setup` cria estes arquivos; `@` indica um alias Yii:

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

`BaseService`, `BaseResource`, `BaseController`, `BaseMigration`, `GridViewResource`, `MutationResource` e interfaces permanecem no pacote; setup não os copia para `common/components`.

Mapa completo de `dnk/list`, também correspondente à saída de `dnk/run`:

| Chave do mapa | Template de destino |
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

`dnk/gen-tests` cria:

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
## Serviços e recursos

Após o início rápido de Book, estas chamadas usam as classes geradas:

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

Os serviços common gerados configuram apenas `MODEL`. Para as APIs de formulários/recursos, use um serviço backend/frontend ou configure explicitamente `MODEL_FORM` e `RESOURCES`.

`create(array $params = [], bool $is_save = true)` e `update($model, array $params = [], bool $is_save = true)` retornam o modelo, não um booleano de sucesso. Verifique `hasErrors()` / `getErrors()` e trate falhas de persistência; exceções do banco podem ser propagadas. `false` pula `save()` e sua validação automática; chame `validate()` quando necessário. O exemplo de atualização pressupõe criação bem-sucedida e registro existente.

Ambos aceitam atributos simples ou dados sob o `formName()` real do modelo; somente atributos seguros do cenário ativo são atribuídos. Um valor que não seja array nessa chave gera `yii\base\InvalidArgumentException`. `createInScenario($params, $scenario, $is_save)` aplica um cenário confiável antes da atribuição e delega a `create()`, preservando sobrescritas compatíveis com a implementação base. O cenário vale apenas para essa chamada.

| API | Finalidade |
| --- | --- |
| `getClassModel()` / `getClassForm()` | Nomes das classes configuradas. |
| `getModel($params)` / `getForm($params)` | Instanciar a classe; `$params` são argumentos do construtor, não atributos. |
| `getEntity()` | Item comum ou formulário dos serviços backend/frontend. |
| `findWhere($criteria)` | `ActiveQuery` para `one()`, `all()` etc. Não existe `findByID()`. |
| `getResource($key, $params)` | Criar a classe de `RESOURCES` com argumentos do construtor; retorna `null` se a chave não estiver mapeada. |

As chaves são `BaseService::CREATE`, `READ`, `UPDATE` e `LIST`. Frontend mapeia apenas `LIST` e `READ`; `FORM` é uma constante sem recurso padrão. O recurso de leitura exige um item existente; `$controller` é o controller web atual e `$found` não pode ser `null`:

```php
$resource = $service->getResource(
    andy87\dnk\source\base\BaseService::READ,
    ['item' => $found]
);
$html = $resource->render($controller);
```

`release()` retorna `['R' => $resource]`. `render($controller, $template = null, $params = [])` renderiza `TEMPLATE`, disponibilizando `$R` na view. `GridViewResource($searchForm, $activeDataProvider)` contém o modelo de busca e o provedor do recurso de listagem. `json($params = [])` seleciona o formato JSON e retorna as propriedades do recurso mescladas com `$params`; **não** aplica a lista `JSON_FIELDS` do frontend. Defina explicitamente os dados públicos de uma API. Não existe `content()`.

<a id="extension"></a>
## Extensão de controllers e cenários

Os pontos de extensão backend são `beforeList()`, `beforeCreate()`, `beforeRead(int $id)` e `beforeUpdate(int $id)`. `SEARCH_MODEL` seleciona o modelo de busca; `SEARCH` permanece como alias de compatibilidade nos controllers gerados. `setTitle()` e `setBreadcrumbs()` definem metadados da página.

`addMutation(new MutationResource($event, $action, $callback))` personaliza a construção de recursos backend. Em `EVENT_RESOURCE_INIT`, o primeiro callback recebe argumentos do construtor e retorna um recurso; os demais recebem esse recurso. Em `EVENT_RESOURCE_CREATED`, os callbacks transformam o recurso construído. **Todos devem retornar o recurso.**

Adicione este método a `backend\controllers\BookController`. Defina antes o cenário `create` e seus campos seguros em `BookForm::scenarios()`; o nome deve vir do código da aplicação, não do POST:

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

A criação backend passa o cenário para `createInScenario()`. Formulários gerados exibem atributos escalares seguros, incluindo virtuais escalares, e ignoram colunas autoincrementais e valores padrão definidos por expressões SQL. A criação preserva padrões do banco para colunas não alteradas. Arrays, objetos, relações e uploads exigem widgets e lógica próprios.

<a id="security"></a>
## Acesso, rotas e JSON

O backend nega acesso por padrão: `accessRoles = []` não autoriza ninguém; visitantes sempre são rejeitados. Configure papéis ou permissões RBAC explícitos. `['@']` autoriza deliberadamente todos os usuários autenticados; use somente se essa for a política desejada. Yii pode redirecionar visitantes para login; usuários autenticados sem permissão recebem 403. O frontend é público, salvo restrições da aplicação.

A ação padrão do DNK é `list`. Leitura/atualização de registro inexistente retorna 404. O `BookController` frontend gerado sobrescreve `actionList()` / `actionRead()` para HTML e filtros de busca; adicionar `?type=json` não ativa o JSON da classe base.

Para herdar as ações HTML/JSON, crie `frontend/controllers/BookApiController.php`:

```php
namespace frontend\controllers;

/** Read-only public book endpoints using the base HTML/JSON actions. */
class BookApiController extends \frontend\components\controllers\FrontendController
{
    public const SERVICE = \frontend\services\BookService::class;
    public const JSON_FIELDS = ['id', 'title'];
}
```

`index.php?r=book-api/list&type=json` retorna `items` e `totalCount`; `index.php?r=book-api/read&id=1&type=json` retorna `item`. A listagem base usa consulta item e paginação, sem os filtros do formulário search gerado. `JSON_FIELDS` é `['id']` por padrão; um array vazio não expõe campos. Restrições de `fields()` do Yii também são respeitadas. Não inclua senhas, tokens ou campos privados.

<a id="migrations"></a>
## Migrações

As migrações estendem `BaseMigration`, definem `$entity = Entity::…` e implementam `constructTable(): array`. `safeUp()` resolve o rótulo antes de criar a tabela e depois adiciona um comentário; `safeDown()` exclui a tabela. `Tables::NAMES` fornece o nome; `Setup`, opções e SQL temporal. Revise antes de executar ou reverter. Um arquivo `*_create_table__<entity>.php` existente impede outra migração de criação, mesmo com sobrescrita.

<a id="upgrades"></a>
## Regeneração e atualizações

| Operação | Arquivos existentes |
| --- | --- |
| `setup` / `gen-tests`, overwrite `0` | Ignorados sem pergunta. |
| `run` / `list` / geradores de modelos, serviços, controllers ou views, overwrite `0` | Pergunta por arquivo; somente o literal `yes` permite substituição. |
| DNK overwrite `1` | Substitui sem mesclar alterações manuais. Migrações de criação continuam sendo ignoradas. |
| Comandos Gii | Usam `--interactive` e `--overwrite` em `command`; não recebem o argumento overwrite do DNK. |

Faça commit ou backup das alterações antes de regenerar. A substituição é atômica por arquivo; a geração completa não é uma transação. Atualizar o pacote Composer altera classes de execução, mas não atualiza arquivos já gerados.

Em aplicações existentes, compare templates novas com controllers compartilhados, classes source/item/search/form, serviços, recursos e views. Aplique mudanças seletivamente, configure `accessRoles`, preserve a delegação a `create()` com cenários e confira formulários e JSON público. Não sobrescreva com Gii classes source mantidas manualmente. Separe controllers e views de DNK e Gii CRUD.

<a id="tests"></a>
## Testes

Em uma cópia **desta biblioteca**, não na aplicação gerada:

```sh
composer install
composer test:docs
composer test
php tests/run.php entity
composer test:codeception
composer test:all
```

`test:docs` verifica sem dependências externas os links locais, as seções traduzidas, a igualdade dos exemplos e os comandos/caminhos do gerador. Também é a primeira etapa de `test:all`.

A suite de regressão requer `pdo_sqlite` e gera uma aplicação temporária com templates reais. `DNK_TEST_VENDOR_PATH` permite um diretório vendor externo. A integração Codeception requer um executor compatível instalado ou `DNK_TEST_CODECEPTION` apontando para sua entrada PHP/PHAR; a ausência falha a verificação. `test:all` executa ambas as suites. Veja [tests/README.md](../../tests/README.md).

`dnk/gen-tests` cria testes nas suites common/backend/frontend da aplicação destino. São esqueletos: adicione asserções do domínio, fixtures e configuração das suites. A tipagem aceita `Codeception\Actor`, incluindo namespaces `Support`. Gerar testes não os executa.

<a id="troubleshooting"></a>
## Solução de problemas

| Sintoma | Verificar |
| --- | --- |
| Comando `dnk` / `gii` desconhecido | `controllerMap` de console, instalação Gii e módulo de console. |
| Propriedade `parts` ou `root` desconhecida | Remover; usar `entityList` e aliases Yii. |
| Redirecionamento para login / 403 no backend | Identidade, `accessRoles` explícitos e atribuições `authManager`. |
| 404 na leitura/atualização | ID existente, visibilidade item/form e rota. |
| Lista vazia para `"*"` | Preencher `entityList` e colocar o asterisco entre aspas. |
| `InvalidConfigException` de entidade | Constante, entrada `Tables::NAMES` e rótulos opcionais válidos. |
| Faltam tabela, regras ou campos | Aplicar esquema, executar `gii-model`, conferir regras, cenários e atributos seguros. |
| Erro SQL de migração em outro SGBD | Adaptar base, opções e campos temporais MySQL. |
| HTML no lugar de JSON | Verificar sobrescritas das ações frontend base. |
| Executor Codeception ausente | Seguir a configuração em `tests/README.md`. |

Licença do pacote: MIT, declarada em [composer.json](../../composer.json).
