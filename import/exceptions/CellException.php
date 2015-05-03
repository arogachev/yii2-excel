<?php

namespace arogachev\excel\import\exceptions;

use PHPExcel_Cell;

class CellException extends ImportException
{
    /**
     * @param PHPExcel_Cell $cell
     * {@inheritdoc}
     */
    public function __construct(PHPExcel_Cell $cell, $message = "", $code = 0, \Exception $previous = null)
    {
        $sheetTitle = $cell->getWorksheet()->getTitle();
        $cellCoordinate = $cell->getCoordinate();
        $message = "Error when preparing data for import: sheet \"$sheetTitle\", cell \"$cellCoordinate\". $message";

        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Cell Exception';
    }
}
