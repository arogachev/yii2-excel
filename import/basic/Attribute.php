<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\import\exceptions\CellException;
use PHPExcel_Cell;
use yii\base\Object;

/**
 * @property StandardAttribute $standardAttribute
 * @property mixed $value
 */
class Attribute extends Object
{
    /**
     * @var PHPExcel_Cell
     */
    public $cell;

    /**
     * @var StandardAttribute
     */
    protected $_standardAttribute;

    /**
     * @var mixed
     */
    protected $_value;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $value = $this->cell->getValue();

        if ($this->_standardAttribute->valueReplacementQuery) {
            $query = call_user_func($this->_standardAttribute->valueReplacementQuery, $value);
            $models = $query->all();

            if (count($models) != 1) {
                throw new CellException($this->cell, 'Failed to replace value by replacement query.');
            }

            $value = $models[0]->{$query->select};
        } elseif ($this->_standardAttribute->valueReplacementList) {
            if (!isset($this->_standardAttribute->valueReplacementList[$value])) {
                throw new CellException($this->cell, 'Failed to replace value by replacement list.');
            }

            $value = $this->_standardAttribute->valueReplacementList[$value];
        }

        $this->_value = $value;
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
}
