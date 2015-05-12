<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\import\DI;
use arogachev\excel\import\exceptions\RowException;
use PHPExcel_Worksheet_Row;
use yii\base\Component;

/**
 * @property StandardModel $standardModel
 * @property \yii\db\ActiveRecord $instance
 */
class Model extends Component
{
    const EVENT_INIT = 'init';

    /**
     * @var PHPExcel_Worksheet_Row
     */
    public $row;

    /**
     * @var StandardModel
     */
    protected $_standardModel;

    /**
     * @var Attribute[]
     */
    protected $_attributes = [];

    /**
     * @var \yii\db\ActiveRecord
     */
    protected $_instance;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initAttributes();
        $this->trigger(self::EVENT_INIT);
    }

    protected function initAttributes()
    {
        $sheet = $this->row->getCellIterator()->current()->getWorksheet();

        foreach ($this->_standardModel->standardAttributes as $standardAttribute) {
            if ($standardAttribute->column) {
                $this->initAttribute([
                    'standardAttribute' => $standardAttribute,
                    'cell' => $sheet->getCell($standardAttribute->column . $this->row->getRowIndex()),
                ]);
            }
        }
    }

    /**
     * @param array $config
     */
    protected function initAttribute($config)
    {
        $this->_attributes[] = new Attribute($config);
    }

    public function load()
    {
        $this->loadExisting();
        $this->assignMassively();
    }

    protected function loadExisting()
    {
        $pk = [];
        foreach ($this->_attributes as $attribute) {
            if (in_array($attribute->standardAttribute->name, $this->_instance->primaryKey())) {
                $pk[$attribute->standardAttribute->name] = $attribute->value;
            }
        }

        if (!$pk) {
            return;
        }

        if (count($pk) == 1 && !reset($pk)) {
            return;
        }

        foreach ($pk as $value) {
            if (!$value) {
                throw new RowException($this->row, 'For updated model all primary key attributes must be specified.');
            }
        }

        /* @var $modelClass \yii\db\ActiveRecord */
        $modelClass = $this->_standardModel->className;
        $model = $modelClass::findOne($pk);
        if (!$model) {
            throw new RowException($this->row, 'Model for update not found.');
        }

        $this->_instance = $model;
    }

    protected function assignMassively()
    {
        foreach ($this->_attributes as $attribute) {
            $this->_instance->{$attribute->standardAttribute->name} = $attribute->value;
        }
    }

    public function validate()
    {
        if (!$this->_instance->validate()) {
            DI::getImporter()->wrongModel = $this->_instance;
            throw new RowException($this->row, 'Model data is not valid.');
        }
    }

    /**
     * @param boolean $runValidation
     * @throws RowException
     */
    public function save($runValidation = true)
    {
        if ($runValidation) {
            $this->validate();
        }

        $this->_instance->save(false);
    }

    /**
     * @return StandardModel
     */
    public function getStandardModel()
    {
        return $this->_standardModel;
    }

    /**
     * @param StandardModel $value
     */
    public function setStandardModel($value)
    {
        $this->_standardModel = $value;
    }

    /**
     * @return \yii\db\ActiveRecord
     */
    public function getInstance()
    {
        return $this->_instance;
    }

    /**
     * @param \yii\db\ActiveRecord $value
     */
    public function setInstance($value)
    {
        $this->_instance = $value;
    }
}
