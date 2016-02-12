<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\components\Model as BaseModel;
use arogachev\excel\import\DI;
use arogachev\excel\import\exceptions\RowException;
use PHPExcel_Worksheet_Row;
use yii\base\Component;

/**
 * @property StandardModel $standardModel
 */
class Model extends BaseModel
{
    const EVENT_INIT = 'init';

    /**
     * @var PHPExcel_Worksheet_Row
     */
    public $row;

    /**
     * @inheritdoc
     */
    protected static $attributeClassName = 'arogachev\excel\import\basic\Attribute';


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

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

    public function load()
    {
        $this->loadExisting();
        $this->assignMassively();
    }

    protected function loadExisting()
    {
        if ($this->isPkEmpty()) {
            return;
        }

        /* @var $modelClass \yii\db\ActiveRecord */
        $modelClass = $this->_standardModel->className;
        $model = $modelClass::findOne($this->getPkValues());
        if ($model) {
            $this->_instance = $model;
        }
    }

    /**
     * @return Attribute[]
     */
    protected function getPk()
    {
        $attributes = [];
        foreach ($this->_attributes as $attribute) {
            if (in_array($attribute->standardAttribute->name, $this->_instance->primaryKey())) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * @return array
     */
    protected function getPkValues()
    {
        $values = [];
        foreach ($this->getPk() as $attribute) {
            $values[$attribute->standardAttribute->name] = $attribute->value;
        }

        return $values;
    }

    /**
     * @return boolean
     */
    protected function isPkEmpty()
    {
        foreach ($this->getPkValues() as $value) {
            if ($value) {
                return false;
            }
        }

        return true;
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
}
