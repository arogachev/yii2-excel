<?php

namespace data;

use yii\db\ActiveRecord;

/**
 * @property integer $id
 * @property integer $question_id
 * @property string $content
 * @property integer $sort
 */
class Answer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'answers';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['question_id', 'content', 'sort'], 'required'],
            ['question_id', 'exist', 'targetClass' => Question::className(), 'targetAttribute' => 'id'],
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
            'question_id' => 'Question',
        ];
    }
}
