<?php

namespace arogachev\excel\behaviors;

use arogachev\excel\import\advanced\Attribute;
use arogachev\excel\import\DI;
use yii\base\Behavior;

class CellBehavior extends Behavior
{
    /**
     * @var string
     */
    protected $_sheetCodeName;

    /**
     * @var string
     */
    protected $_cellCoordinate;


    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Attribute::EVENT_INIT => 'customInit',
        ];
    }

    public function customInit()
    {
        /* @var $model Attribute */
        $model = $this->owner;
        $this->_sheetCodeName = $model->cell->getWorksheet()->getCodeName();
        $this->_cellCoordinate = $model->cell->getCoordinate();
    }

    /**
     * @return \PHPExcel_Cell
     */
    public function getInitialCell()
    {
        return DI::getPHPExcel()->getSheetByCodeName($this->_sheetCodeName)->getCell($this->_cellCoordinate);
    }
}
