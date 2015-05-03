<?php

namespace arogachev\excel\import\exceptions;

use PHPExcel_Worksheet_Row;

class RowException extends ImportException
{
    /**
     * @param PHPExcel_Worksheet_Row $row
     * {@inheritdoc}
     */
    public function __construct(PHPExcel_Worksheet_Row $row, $message = "", $code = 0, \Exception $previous = null)
    {
        $sheetTitle = $row->getCellIterator()->current()->getWorksheet()->getTitle();
        $message = "Import failed at sheet \"$sheetTitle\", row \"{$row->getRowIndex()}\". $message";

        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Row Exception';
    }
}
