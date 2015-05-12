<?php

namespace arogachev\excel\import\advanced;

use arogachev\excel\helpers\PHPExcelHelper;
use arogachev\excel\import\BaseImporter;
use arogachev\excel\import\CellParser;
use arogachev\excel\import\exceptions\CellException;
use arogachev\excel\import\exceptions\RowException;
use PHPExcel_Cell;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * @property CellParser $cellParser
 * @property StandardModel[] $_standardModels
 * @property StandardModel $currentStandardModel
 * @property Model[] $models
 */
class Importer extends BaseImporter
{
    // Modes

    /**
     * Mode - Default Attributes
     */
    const MODE_DEFAULT_ATTRIBUTES = 'defaultAttributes';

    /**
     * Model - Import
     */
    const MODE_IMPORT = 'import';

    /**
     * @var string|array
     */
    public $sheetNames = '*';

    /**
     * @var array
     */
    public $cellParserConfig;

    /**
     * @var CellParser
     */
    protected $_cellParser;

    /**
     * @var string
     */
    protected $_currentMode;

    /**
     * @var StandardModel
     */
    protected $_currentStandardModel;

    /**
     * @var string
     */
    protected $_currentSavedRow;

    /**
     * @var array
     */
    protected $_savedRows = [];


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->validateStandardModelLabels();
        $this->_cellParser = new CellParser($this->cellParserConfig);
    }

    /**
     * @inheritdoc
     */
    protected function initStandardModel($config)
    {
        $this->_standardModels[] = new StandardModel($config);
    }

    /**
     * @throws InvalidConfigException
     */
    protected function validateStandardModelLabels()
    {
        $labels = [];
        foreach ($this->_standardModels as $standardModel) {
            foreach ($standardModel->labels as $label) {
                $labels[] = $label;
            }
        }

        if (count($labels) != count(array_unique($labels))) {
            throw new InvalidConfigException('Each standard model label must be unique.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function fillModels($rows)
    {
        foreach ($rows as $row) {
            // Skipping completely empty rows
            // Only loaded pk links can be empty

            $currentCell = $row->getCellIterator()->current();

            if (!$row->getCellIterator()->valid()) {
                continue;
            }

            if (PHPExcelHelper::isRowEmpty($row) && !$this->_cellParser->isLoadedPk($currentCell)) {
                continue;
            }

            if ($this->_cellParser->isLoadedRows($currentCell)) {
                $this->fillModels($this->_savedRows[$this->_cellParser->getLoadedRows($currentCell)]);

                continue;
            }

            if ($this->_cellParser->isSavedRows($currentCell)) {
                if ($this->_currentSavedRow) {
                    $this->_currentSavedRow = null;
                } else {
                    $this->_currentSavedRow = $this->_cellParser->getSavedRows($currentCell);
                }

                continue;
            }

            if ($this->_currentSavedRow) {
                $this->_savedRows[$this->_currentSavedRow][] = clone $row;
            }

            if ($this->_cellParser->isModelDefaultsLabel($currentCell)) {
                $this->_currentMode = self::MODE_DEFAULT_ATTRIBUTES;
                $this->fillCurrentStandardModel($currentCell);

                continue;
            }

            if ($this->_cellParser->isModelLabel($currentCell)) {
                $this->_currentMode = self::MODE_IMPORT;
                $this->fillCurrentStandardModel($currentCell);

                continue;
            }

            if (!$this->_currentStandardModel) {
                throw new RowException($row, 'Model label must be declared before attribute names or values');
            }

            if ($this->_cellParser->isAttributeName($currentCell)) {
                $this->_currentStandardModel->parseAttributeNames($row);

                continue;
            }

            if ($this->_currentMode == self::MODE_IMPORT) {
                $this->_models[] = new Model([
                    'row' => $row,
                    'standardModel' => $this->_currentStandardModel,
                ]);
            } elseif ($this->_currentMode == self::MODE_DEFAULT_ATTRIBUTES) {
                $this->_currentStandardModel->setDefaultAttributes($row);
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function safeRun()
    {
        parent::safeRun();

        $sheetNames = $this->sheetNames === '*' ? $this->_phpExcel->getSheetNames() : (array) $this->sheetNames;
        foreach ($sheetNames as $sheetName) {
            $sheet = $this->_phpExcel->getSheetByName($sheetName);
            $this->fillModels($sheet->getRowIterator());
        }

        Yii::$app->db->transaction(function() {
            foreach ($this->_models as $model) {
                $model->load();
                $model->save();
            }
        });

        $this->trigger(self::EVENT_RUN);
    }

    /**
     * @param PHPExcel_Cell $cell
     * @throws CellException
     */
    protected function fillCurrentStandardModel($cell)
    {
        if ($this->_currentMode == self::MODE_DEFAULT_ATTRIBUTES) {
            $label = $this->_cellParser->getModelDefaultsLabel($cell);
        } else {
            $label = $this->_cellParser->getModelLabel($cell);
        }

        foreach ($this->_standardModels as $standardModel) {
            if (in_array($label, $standardModel->labels)) {
                $this->_currentStandardModel = $standardModel;

                return;
            }
        }

        throw new CellException($cell, "Standard model not found by given label.");
    }

    /**
     * @return CellParser
     */
    public function getCellParser()
    {
        return $this->_cellParser;
    }
}
