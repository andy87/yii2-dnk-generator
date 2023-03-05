<?php

use common\components\db\Config;
use common\components\Part;
use andy87\dnk\common\components\core\BaseMigration as BaseMigrationClass;

/**
 * Создание таблицы `{{snake_case}}`
 */
class {{migrateFileName}} extends BaseMigrationClass
{
    /** @var string Имя таблицы */
    public string $part = Part::{{UPPER_CASE}};

    /**
     * @return array
     */
    public function constructTable(): array
    {
        return [
            'id' => $this->primaryKey(),

            // new column ... examples:
                #'status_id' => $this->integer(3)->defaultValue(1),
                #'string' => $this->string(255)->notNull()->unique(),
                #'item_id' => $this->integer(11),

            'created_at' => Config::COLUMN['created_at'],
            'updated_at' => Config::COLUMN['updated_at'],
        ];
    }
}