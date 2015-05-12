<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\import\exceptions\StandardAttributeException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * @property StandardModel $standardModel
 * @property string $column
 */
class StandardAttribute extends Object
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $label;

    /**
     * @var array|callable
     */
    public $valueReplacement;

    /**
     * @var StandardModel
     */
    protected $_standardModel;

    /**
     * @var string
     */
    protected $_column;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->validateName();

        if ($this->_standardModel->extendStandardAttributes && !$this->label) {
            $this->label = $this->_standardModel->instance->getAttributeLabel($this->name);
        }

        $this->validateLabel();
        $this->validateValueReplacement();
    }

    /**
     * @throws StandardAttributeException
     */
    protected function validateName()
    {
        $standardModel = $this->_standardModel;
        $model = $standardModel->instance;

        if (!$this->name) {
            throw new StandardAttributeException($this, 'Name is required.');
        }

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

    /**
     * @throws StandardAttributeException
     */
    protected function validateLabel()
    {
        if ($this->standardModel->useAttributeLabels && !$this->label) {
            throw new StandardAttributeException($this, 'Label not specified.');
        }
    }

    /**
     * @throws StandardAttributeException
     */
    protected function validateValueReplacement()
    {
        if (!$this->valueReplacement || !is_array($this->valueReplacement)) {
            return;
        }

        if ($this->valueReplacement != array_unique($this->valueReplacement)) {
            throw new StandardAttributeException($this, 'Value replacement list contains duplicate labels / values.');
        }
    }

    /**
     * @return StandardModel
     */
    public function getStandardModel()
    {
        return $this->_standardModel;
    }

    /**
     * @param StandardModel $value
     */
    public function setStandardModel($value)
    {
        $this->_standardModel = $value;
    }

    /**
     * @return string
     */
    public function getColumn()
    {
        return $this->_column;
    }

    /**
     * @param string $value
     */
    public function setColumn($value)
    {
        $this->_column = $value;
    }
}
