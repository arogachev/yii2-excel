<?php

namespace arogachev\excel\export\basic;

use PHPExcel;
use PHPExcel_IOFactory;
use yii\base\Object;

class Exporter extends Object
{
    /**
     * @var \yii\data\ActiveDataProvider
     */
    public $dataProvider;

    /**
     * @var \yii\db\ActiveQuery
     */
    public $query;

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
     * @var PHPExcel
     */
    protected $_phpExcel;

    /**
     * @var StandardModel[]
     */
    protected $_standardModels;

    /**
     * @var Model[]
     */
    protected $_models = [];


    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->dataProvider) {
            $this->dataProvider->pagination = false;
        }

        foreach ($this->standardModelsConfig as $config) {
            $this->_standardModels[] = new StandardModel($config);
        }

        if (!$this->sheetTitle) {
            $this->sheetTitle = function() {
                return 'Export ' . date('Y-m-d H:i:s');
            };
        }
    }

    public function run()
    {
        $this->_phpExcel = new PHPExcel;
        $sheet = $this->_phpExcel->getActiveSheet();

        $this->fillModels();
        $this->fillSheetTitle();
        $this->_standardModels[0]->exportAttributeNames($sheet);

        $row = 2;
        foreach ($this->_models as $model) {
            $model->exportAttributeValues($sheet, $row);
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

    protected function fillModels()
    {
        $models = $this->dataProvider ? $this->dataProvider->getModels() : $this->query->all();
        foreach ($models as $model) {
            $this->_models[] = new Model([
                'instance' => $model,
                'standardModel' => $this->_standardModels[0],
            ]);
        }
    }
}
