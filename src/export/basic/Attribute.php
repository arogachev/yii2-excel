<?php

namespace arogachev\excel\export\basic;

use arogachev\excel\components\Attribute as BaseAttribute;
use arogachev\excel\export\exceptions\ExportException;
use yii\base\InvalidParamException;

/**
 * @var StandardAttribute $standardAttribute
 */
class Attribute extends BaseAttribute
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->_standardAttribute->valueReplacement) {
            $this->_value = $this->_model->instance->{$this->_standardAttribute->name};
        } else {
            $this->replaceValue();
        }
    }

    /**
     * @throws ExportException
     * @throws InvalidParamException
     */
    protected function replaceValue()
    {
        $standardAttribute = $this->_standardAttribute;
        $valueReplacement = $standardAttribute->valueReplacement;
        if (!$valueReplacement) {
            return;
        }

        $value = $this->_value;

        if (is_array($standardAttribute->valueReplacement)) {
            if (!isset($standardAttribute->valueReplacement[$value])) {
                throw new ExportException('Failed to replace value by replacement list.');
            }

            $value = $standardAttribute->valueReplacement[$value];
        } elseif (is_callable($standardAttribute->valueReplacement)) {
            $value = call_user_func($standardAttribute->valueReplacement, $this->_model->instance);
        } else {
            throw new InvalidParamException('$valueReplacement must be specified as array or callable.');
        }

        $this->_value = $value;
    }
}
