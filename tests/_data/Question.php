<?php

namespace data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $test_id
 * @property string $content
 * @property integer $sort
 */
class Question extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'questions';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['test_id', 'content', 'sort'], 'required'],
            ['test_id', 'exist', 'targetClass' => Test::className(), 'targetAttribute' => 'id'],
            ['content', 'string'],
            ['sort', 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'test_id' => 'Test',
        ];
    }
}
