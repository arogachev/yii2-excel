<?php

namespace arogachev\excel\import;

use arogachev\excel\import\exceptions\CellException;
use PHPExcel_Cell;
use yii\base\Object;

class CellParser extends Object
{
    // Hex color codes

    /**
     * Color - Blue
     */
    const COLOR_BLUE = '00B0F0';

    /**
     * Color - Yellow
     */
    const COLOR_YELLOW = 'FFFF00';

    /**
     * Color - Green
     */
    const COLOR_GREEN = '00FF00';

    /**
     * Color - Orange
     */
    const COLOR_ORANGE = 'F1C232';

    /**
     * @var callable
     */
    public $modelLabelDetection;

    /**
     * @var callable
     */
    public $modelLabelGetting;

    /**
     * @var callable
     */
    public $modelDefaultsLabelDetection;

    /**
     * @var callable
     */
    public $modelDefaultsLabelGetting;

    /**
     * @var callable
     */
    public $attributeNameDetection;

    /**
     * @var callable
     */
    public $attributeNameGetting;

    /**
     * @var callable
     */
    public $savedPkDetection;

    /**
     * @var callable
     */
    public $savedPkGetting;

    /**
     * @var callable
     */
    public $loadedPkDetection;

    /**
     * @var callable
     */
    public $loadedPkGetting;

    /**
     * @var callable
     */
    public $savedRowsDetection;

    /**
     * @var callable
     */
    public $savedRowsGetting;

    /**
     * @var callable
     */
    public $loadedRowsDetection;

    /**
     * @var callable
     */
    public $loadedRowsGetting;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initDefaults();
    }

    protected function initDefaults()
    {
        if (!$this->modelLabelDetection) {
            $this->modelLabelDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getStyle()->getFont()->getBold();
            };
        }

        if (!$this->modelLabelGetting) {
            $this->modelLabelGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }

        if (!$this->modelDefaultsLabelDetection) {
            $this->modelDefaultsLabelDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                $isBold = $cell->getStyle()->getFont()->getBold();
                $underline = $cell->getStyle()->getFont()->getUnderline();

                return $isBold && $underline != 'none';
            };
        }

        if (!$this->modelDefaultsLabelGetting) {
            $this->modelDefaultsLabelGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }

        if (!$this->attributeNameDetection) {
            $this->attributeNameDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getStyle()->getFont()->getItalic();
            };
        }

        if (!$this->attributeNameGetting) {
            $this->attributeNameGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }

        if (!$this->savedPkDetection) {
            $this->savedPkDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                $startColor = $cell->getStyle()->getFill()->getStartColor()->getRGB();
                $endColor = $cell->getStyle()->getFill()->getEndColor()->getRGB();

                return $startColor == self::COLOR_BLUE && $endColor == self::COLOR_BLUE;
            };
        }

        if (!$this->savedPkGetting) {
            $this->savedPkGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }

        if (!$this->loadedPkDetection) {
            $this->loadedPkDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                $startColor = $cell->getStyle()->getFill()->getStartColor()->getRGB();
                $endColor = $cell->getStyle()->getFill()->getEndColor()->getRGB();

                return $startColor == self::COLOR_YELLOW && $endColor == self::COLOR_YELLOW;
            };
        }

        if (!$this->loadedPkGetting) {
            $this->loadedPkGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }

        if (!$this->savedRowsDetection) {
            $this->savedRowsDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                $startColor = $cell->getStyle()->getFill()->getStartColor()->getRGB();
                $endColor = $cell->getStyle()->getFill()->getEndColor()->getRGB();

                return $startColor == self::COLOR_GREEN && $endColor == self::COLOR_GREEN;
            };
        }

        if (!$this->savedRowsGetting) {
            $this->savedRowsGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }

        if (!$this->loadedRowsDetection) {
            $this->loadedRowsDetection = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                $startColor = $cell->getStyle()->getFill()->getStartColor()->getRGB();
                $endColor = $cell->getStyle()->getFill()->getEndColor()->getRGB();

                return $startColor == self::COLOR_ORANGE && $endColor == self::COLOR_ORANGE;
            };
        }

        if (!$this->loadedRowsGetting) {
            $this->loadedRowsGetting = function ($cell) {
                /* @var $cell PHPExcel_Cell */
                return $cell->getValue();
            };
        }
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isModelLabel($cell)
    {
        return call_user_func($this->modelLabelDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return string
     * @throws CellException
     */
    public function getModelLabel($cell)
    {
        $value = call_user_func($this->modelLabelGetting, $cell);
        if (!$value) {
            throw new CellException($cell, 'The model label not specified.');
        }

        return (string) $value;
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isModelDefaultsLabel($cell)
    {
        return call_user_func($this->modelDefaultsLabelDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return mixed
     * @throws CellException
     */
    public function getModelDefaultsLabel($cell)
    {
        $value = call_user_func($this->modelDefaultsLabelGetting, $cell);
        if (!$value) {
            throw new CellException($cell, 'The model defaults label not specified.');
        }

        return (string) $value;
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isAttributeName($cell)
    {
        return call_user_func($this->attributeNameDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return string
     */
    public function getAttributeName($cell)
    {
        return (string) call_user_func($this->attributeNameGetting, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isSavedPk($cell)
    {
        return call_user_func($this->savedPkDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return string
     * @throws CellException
     */
    public function getSavedPk($cell)
    {
        $value = call_user_func($this->savedPkGetting, $cell);
        if (!$value) {
            throw new CellException($cell, 'The saved primary key not specified.');
        }

        return (string) $value;
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isLoadedPk($cell)
    {
        return call_user_func($this->loadedPkDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return string
     * @throws CellException
     */
    public function getLoadedPk($cell)
    {
        return (string) call_user_func($this->loadedPkGetting, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isSavedRows($cell)
    {
        return call_user_func($this->savedRowsDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return string
     * @throws CellException
     */
    public function getSavedRows($cell)
    {
        $value = call_user_func($this->savedRowsGetting, $cell);
        if (!$value) {
            throw new CellException($cell, 'Name for saved rows not specified.');
        }

        return (string) $value;
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return boolean
     */
    public function isLoadedRows($cell)
    {
        return call_user_func($this->loadedRowsDetection, $cell);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @return string
     * @throws CellException
     */
    public function getLoadedRows($cell)
    {
        $value = call_user_func($this->loadedRowsGetting, $cell);
        if (!$value) {
            throw new CellException($cell, 'Name for loaded row not specified.');
        }

        return (string) $value;
    }
}
