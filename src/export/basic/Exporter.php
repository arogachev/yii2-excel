<?php

namespace arogachev\excel\export\basic;

use arogachev\excel\import\basic\StandardModel;
use PHPExcel;
use PHPExcel_IOFactory;
use yii\base\Exception;
use yii\base\InvalidParamException;
use yii\base\Object;

class Exporter extends Object
{
    /**
     * @var \yii\db\ActiveQuery
     */
    public $query;

    /**
     * @var \yii\db\ActiveRecord[]
     */
    public $models;

    /**
     * @var string|callable
     */
    public $fileName;

    /**
     * @var string|callable
     */
    public $filePath;

    /**
     * @var string|callable
     */
    public $sheetTitle;

    /**
     * @var array
     */
    public $standardModelsConfig;

    /**
     * @var array
     */
    public $attributesOrder = [];

    /**
     * @var PHPExcel
     */
    protected $_phpExcel;

    /**
     * @var StandardModel[]
     */
    protected $_standardModels;


    /**
     * @inheritdoc
     */
    public function init()
    {
        foreach ($this->standardModelsConfig as $config) {
            $this->_standardModels[] = new StandardModel($config);
        }

        if (!$this->sheetTitle) {
            $this->sheetTitle = function() {
                return 'Export ' . date('Y-m-d H:i:s');
            };
        }

        $this->sortStandardAttributes();
    }

    protected function sortStandardAttributes()
    {
        if (!$this->attributesOrder) {
            return;
        }

        $standardAttributes = $this->_standardModels[0]->standardAttributes;
        $sortedStandardAttributes = array_merge(array_flip($this->attributesOrder), $standardAttributes);
        $this->_standardModels[0]->standardAttributes = $sortedStandardAttributes;
    }

    public function run()
    {
        $this->_phpExcel = new PHPExcel;
        $sheet = $this->_phpExcel->getActiveSheet();

        $this->fillSheetTitle();

        $column = 'A';
        $row = 1;

        foreach ($this->_standardModels[0]->standardAttributes as $name => $standardAttribute) {
            $standardAttribute->column = $column;
            $sheet->setCellValue($column . $row, $name);
            $column++;
        }

        $models = $this->models ?: $this->query->all();
        $row++;

        foreach ($models as $model) {
            foreach ($this->_standardModels[0]->standardAttributes as $standardAttribute) {
                $this->replaceValue($model, $standardAttribute);
                $sheet->setCellValue($standardAttribute->column . $row, $model->{$standardAttribute->name});
                $column++;
            }

            $row++;
        }

        $writer = PHPExcel_IOFactory::createWriter($this->_phpExcel, 'Excel2007');

        if ($this->filePath) {
            $filePath = is_callable($this->filePath) ? call_user_func($this->filePath) : $this->filePath;
            $writer->save($filePath);
        } else {
            $fileName = is_callable($this->fileName) ? call_user_func($this->fileName) : $this->fileName;
            header('Content-Type: application/ms-excel');
            header("Content-Disposition: attachment;filename=\"$fileName\"");
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
        }
    }

    protected function fillSheetTitle()
    {
        $title = is_callable($this->sheetTitle) ? call_user_func($this->sheetTitle) : $this->sheetTitle;
        $this->_phpExcel->getActiveSheet()->setTitle($title);
    }

    /**
     * @param \yii\db\ActiveRecord $model
     * @param \arogachev\excel\import\basic\StandardAttribute $standardAttribute
     * @throws Exception
     * @throws InvalidParamException
     */
    protected function replaceValue($model, $standardAttribute)
    {
        if (!$standardAttribute->valueReplacement) {
            return;
        }

        $value = $model->{$standardAttribute->name};

        if (is_array($standardAttribute->valueReplacement)) {
            if (!isset($standardAttribute->valueReplacement[$value])) {
                throw new Exception('Failed to replace value by replacement list.');
            }

            $value = $standardAttribute->valueReplacement[$value];
        } elseif (is_callable($standardAttribute->valueReplacement)) {
            $value = call_user_func($standardAttribute->valueReplacement, $model);
        } else {
            throw new InvalidParamException('$valueReplacement must be specified as array or callable.');
        }

        $model->{$standardAttribute->name} = $value;
    }
}
