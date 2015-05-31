<?php

namespace arogachev\excel\export\basic;

use arogachev\excel\components\StandardModel as BaseStandardModel;
use arogachev\excel\export\exceptions\ExportException;

/**
 * @property StandardAttribute[] $standardAttributes
 */
class StandardModel extends BaseStandardModel
{
    /**
     * @var array
     */
    public $attributesOrder = [];

    /**
     * @inheritdoc
     */
    protected static $standardAttributeClassName = 'arogachev\excel\export\basic\StandardAttribute';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->sortStandardAttributes();
        $this->fillStandardAttributesColumns();
    }

    protected function sortStandardAttributes()
    {
        if (!$this->attributesOrder) {
            return;
        }

        $standardAttributes = $this->_standardAttributes;
        $this->_standardAttributes = [];

        foreach ($this->attributesOrder as $attributeName) {
            if (!isset($standardAttributes[$attributeName])) {
                $message = "Attribute with name \"$attributeName\" mentioned in \$attributesOrder"
                    . " for standard model \"$this->className\" not found in standard attributes list";
                throw new ExportException($message);
            }

            $this->_standardAttributes[$attributeName] = $standardAttributes[$attributeName];
        }
    }

    protected function fillStandardAttributesColumns()
    {
        $column = 'A';

        foreach ($this->_standardAttributes as $name => $standardAttribute) {
            $standardAttribute->column = $column;

            $column++;
        }
    }

    /**
     * @param \PHPExcel_Worksheet $sheet
     */
    public function exportAttributeNames($sheet)
    {
        $row = 1;

        foreach ($this->_standardAttributes as $name => $standardAttribute) {
            $sheet->setCellValue($standardAttribute->column . $row, $name);
        }
    }
}
