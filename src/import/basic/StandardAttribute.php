<?php

namespace divad942\excel\import\basic;

use divad942\excel\components\StandardAttribute as BaseStandardAttribute;
use divad942\excel\exceptions\StandardAttributeException;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

/**
 * @property StandardModel $standardModel
 */
class StandardAttribute extends BaseStandardAttribute
{
    /**
     * @inheritdoc
     */
    protected function validateName()
    {
        parent::validateName();

        $standardModel = $this->_standardModel;
        $model = $standardModel->instance;

        if (!in_array($this->name, $model->attributes())) {
            throw new StandardAttributeException($this, 'Attribute not exist.');
        }

        if (in_array($this->name, $model->primaryKey())) {
            return;
        }

        if (in_array($this->name, ArrayHelper::getColumn($standardModel->standardAttributesConfig, 'name'))) {
            return;
        }

        if (!$model->isAttributeSafe($this->name)) {
            throw new StandardAttributeException($this, 'Attribute is not allowed for import.');
        }
    }
}
