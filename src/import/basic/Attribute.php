<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\import\exceptions\CellException;
use PHPExcel_Cell;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveQuery;

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
        if (!$this->_standardAttribute->valueReplacement) {
            $this->_value = $this->cell->getValue();
        } else {
            $this->replaceValue();
        }
    }

    /**
     * @throws CellException
     * @throws InvalidParamException
     */
    protected function replaceValue()
    {
        $valueReplacement = $this->_standardAttribute->valueReplacement;
        $value = $this->cell->getValue();

        if (is_array($valueReplacement)) {
            $flippedList = array_flip($valueReplacement);
            if (!isset($flippedList[$value])) {
                throw new CellException($this->cell, 'Failed to replace value by replacement list.');
            }

            $value = $flippedList[$value];
        } elseif (is_callable($valueReplacement)) {
            $result = call_user_func($valueReplacement, $value);

            if ($result instanceof ActiveQuery) {
                $models = $result->all();

                if (count($models) != 1) {
                    throw new CellException($this->cell, 'Failed to replace value by replacement query.');
                }

                $value = $models[0]->{$result->select[0]};
            } else {
                $value = $result;
            }
        } else {
            throw new InvalidParamException('$valueReplacement must be specified as array or callable.');
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

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }
}
