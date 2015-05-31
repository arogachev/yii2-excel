<?php

namespace arogachev\excel\components;

use arogachev\excel\exceptions\StandardAttributeException;
use yii\base\Object;

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
        if (!$this->name) {
            throw new StandardAttributeException($this, 'Name is required.');
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
