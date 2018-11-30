<?php

namespace divad942\excel\import\advanced;

use divad942\excel\import\basic\StandardModel as BasicStandardModel;

/**
 * @property Attribute[] $defaultAttributes
 */
class StandardModel extends BasicStandardModel
{
    /**
     * @var array
     */
    public $labels = [];

    /**
     * @var Attribute[]
     */
    protected $_defaultAttributes = [];


    /**
     * @return Attribute[]
     */
    public function getDefaultAttributes()
    {
        return $this->_defaultAttributes;
    }

    /**
     * @param \PHPExcel_Worksheet_Row $row
     */
    public function setDefaultAttributes($row)
    {
        $this->_defaultAttributes = [];
        $sheet = $row->getCellIterator()->current()->getWorksheet();
        foreach ($this->_standardAttributes as $standardAttribute) {
            if ($standardAttribute->column) {
                $this->_defaultAttributes[] = new Attribute([
                    'cell' => $sheet->getCell($standardAttribute->column . $row->getRowIndex()),
                    'standardAttribute' => $standardAttribute,
                ]);
            }
        }
    }
}
