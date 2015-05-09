<?php

namespace arogachev\excel\helpers;

class PHPExcelHelper
{
    /**
     * @param \PHPExcel_Worksheet_Row $row
     * @return boolean
     */
    public static function isRowEmpty($row)
    {
        foreach ($row->getCellIterator() as $cell) {
            if ($cell->getValue()) {
                return false;
            }
        }

        return true;
    }
}
