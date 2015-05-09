<?php

namespace data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 */
class Author extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'authors';
    }
}
