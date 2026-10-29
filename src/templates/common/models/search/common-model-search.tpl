<?php

namespace common\models\search;

use common\models\sources\{{CamelCase}}Source;
use common\models\items\{{CamelCase}};
use yii\data\ActiveDataProvider;
use yii\db\Exception;

/**
 * Поиск записей {{CamelCase}} без обязательных полей и проверок сохранения модели.
 */
class {{CamelCase}}Search extends {{CamelCase}}Source
{
    /** @var string Item-модель, определяющая видимость записей в этом окружении. */
    public const MODEL = {{CamelCase}}::class;

    /**
     * Проверяет необязательные фильтры по типам колонок таблицы.
     *
     * @return array Правила валидации фильтров.
     * @throws Exception Если схема таблицы недоступна.
     */
    public function rules(): array
    {
        $types = ['integer' => 'integer', 'double' => 'number', 'boolean' => 'boolean'];
        $attributes = [];
        foreach (static::getTableSchema()->columns as $column) {
            $validator = $types[$column->phpType] ?? 'string';
            $attributes[$validator][] = $column->name;
        }

        $rules = [];
        foreach ($attributes as $validator => $names) {
            $rules[] = [$names, $validator];
        }

        return $rules;
    }

    /**
     * Добавляет фильтры к запросу item-модели, сохраняя её ограничения видимости.
     *
     * @param array $params Параметры запроса в формате {{CamelCase}}Search[attribute].
     * @return ActiveDataProvider Провайдер списка; при ошибке фильтра список пуст.
     * @throws Exception Если схема таблицы недоступна.
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $modelClass = static::MODEL;
        $query = $modelClass::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);
        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere($this->getAttributes());

        return $dataProvider;
    }
}
