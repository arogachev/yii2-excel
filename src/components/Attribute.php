<?php

namespace arogachev\excel\components;

use yii\base\Component;

/**
 * @property StandardAttribute $standardAttribute
 * @property mixed $value
 */
class Attribute extends Component
{
    /**
     * @var Model
     */
    protected $_model;

    /**
     * @var StandardAttribute
     */
    protected $_standardAttribute;

    /**
     * @var mixed
     */
    protected $_value;


    /**
     * @param Model $value
     */
    public function setModel($value)
    {
        $this->_model = $value;
    }

    /**
     * @return StandardAttribute
     */
    public function getStandardAttribute()
    {
        return $this->_standardAttribute;
    }

    /**
     * @param StandardAttribute $value
     */
    public function setStandardAttribute($value)
    {
        $this->_standardAttribute = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }
}
