<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\components\Attribute as BaseAttribute;
use arogachev\excel\import\exceptions\CellException;
use PHPExcel_Cell;
use yii\base\Component;
use yii\base\InvalidParamException;
use yii\db\ActiveQuery;

/**
 * @property StandardAttribute $standardAttribute
 */
class Attribute extends BaseAttribute
{
    /**
     * @var PHPExcel_Cell
     */
    public $cell;

    /**
     * @var boolean
     */
    protected $_replaced = false;

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
     * @param boolean $throwException
     * @throws CellException
     * @throws InvalidParamException
     */
    public function replaceValue($throwException = false)
    {
        if ($this->_replaced) {
            return;
        }

        $valueReplacement = $this->_standardAttribute->valueReplacement;
        $cellValue = $this->cell->getValue();
        $value = null;

        if (is_array($valueReplacement)) {
            $flippedList = array_flip($valueReplacement);
            if (isset($flippedList[$cellValue])) {
                $value = $flippedList[$cellValue];
            } elseif ($throwException) {
                throw new CellException($this->cell, 'Failed to replace value by replacement list.');
            }
        } elseif (is_callable($valueReplacement)) {
            $result = call_user_func($valueReplacement, $cellValue);

            if ($result instanceof ActiveQuery) {
                $models = $result->all();

                if (count($models) == 1) {
                    $value = $models[0]->{$result->select[0]};
                } elseif ($throwException) {
                    throw new CellException($this->cell, 'Failed to replace value by replacement query.');
                }
            } else {
                $value = $result;
            }
        } else {
            throw new InvalidParamException('$valueReplacement must be specified as array or callable.');
        }

        if ($value !== null) {
            $this->_value = $value;
            $this->_replaced = true;
        }
    }
}
