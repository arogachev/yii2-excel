<?php

namespace data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property integer $author_id
 */
class Test extends ActiveRecord
{
    // Types

    /**
     * Type - Closed
     */
    const TYPE_CLOSED = 1;

    /**
     * Type - Opened
     */
    const TYPE_OPENED = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tests';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type', 'author_id'], 'required'],
            [['name', 'description'], 'string'],
            ['type', 'in', 'range' => array_keys(static::getTypesList())],
            ['author_id', 'exist', 'targetClass' => Author::className(), 'targetAttribute' => 'id'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_id' => 'Author',
        ];
    }

    /**
     * @return array
     */
    public static function getTypesList()
    {
        return [
            self::TYPE_CLOSED => 'Closed',
            self::TYPE_OPENED => 'Opened',
        ];
    }
}
