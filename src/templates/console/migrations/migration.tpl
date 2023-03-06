<?php

use common\components\Entity;
use common\components\db\Setup;
use common\components\db\Tables;
use {{BaseMigrationClassName}} as BaseMigrationClass;

/**
 * Создание таблицы `{{snake_case}}`
 */
class {{migrateFileName}} extends BaseMigrationClass
{
    /** @var string Имя таблицы */
    public string $entity = Tables::NAMES[ Entity::{{UPPER_CASE}} ];

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

            'created_at' => Setup::COLUMN['created_at'],
            'updated_at' => Setup::COLUMN['updated_at'],
        ];
    }
}