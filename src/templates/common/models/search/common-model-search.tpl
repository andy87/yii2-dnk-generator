<?php

namespace common\models\search;

use common\models\sources\{{CamelCase}}Source;
use yii\data\ActiveDataProvider;
use yii\db\Exception;

/**
 * Поиск записей {{CamelCase}} без обязательных полей и проверок сохранения модели.
 */
class {{CamelCase}}Search extends {{CamelCase}}Source
{
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
     * Создаёт список с фильтрацией по переданным атрибутам.
     *
     * @param array $params Параметры запроса в формате {{CamelCase}}Search[attribute].
     * @return ActiveDataProvider Провайдер списка; при ошибке фильтра список пуст.
     * @throws Exception Если схема таблицы недоступна.
     */
    public function search(array $params = []): ActiveDataProvider
    {
        $query = {{CamelCase}}Source::find();
        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);
        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere($this->getAttributes());

        return $dataProvider;
    }
}
