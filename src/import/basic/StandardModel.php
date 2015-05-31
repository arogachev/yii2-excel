<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\components\StandardModel as BaseStandardModel;
use arogachev\excel\import\exceptions\CellException;
use arogachev\excel\import\exceptions\RowException;
use PHPExcel_Worksheet_Row;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property StandardAttribute[] $standardAttributes
 */
class StandardModel extends BaseStandardModel
{
    const SCENARIO_IMPORT = 'import';

    /**
     * @var boolean
     */
    public $setScenario = false;

    /**
     * @inheritdoc
     */
    protected static $standardAttributeClassName = 'arogachev\excel\import\basic\StandardAttribute';

    /**
     * @var array
     */
    protected static $_setScenarioEvents = [
        ActiveRecord::EVENT_INIT,
        ActiveRecord::EVENT_AFTER_FIND,
    ];


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->configureEventHandlers();
    }

    /**
     * @throws InvalidParamException
     */
    protected function initInstance()
    {
        parent::initInstance();

        if ($this->setScenario) {
            $this->_instance->scenario = self::SCENARIO_IMPORT;
        }
    }

    protected function configureEventHandlers()
    {
        if ($this->setScenario) {
            foreach (self::$_setScenarioEvents as $eventName) {
                Event::on($this->className, $eventName, function ($event) {
                    $event->sender->scenario = self::SCENARIO_IMPORT;
                });
            }
        }
    }

    /**
     * @return array
     */
    protected function getAllowedAttributes()
    {
        $model = $this->_instance;
        $attributes = [];

        foreach ($model->attributes() as $attribute) {
            if (in_array($attribute, $model->primaryKey()) || $model->isAttributeSafe($attribute)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * @param PHPExcel_Worksheet_Row $row
     * @return boolean
     * @throws CellException
     * @throws RowException
     */
    public function parseAttributeNames($row)
    {
        foreach ($this->_standardAttributes as $standardAttribute) {
            $standardAttribute->column = null;
        }

        $model = $this->_instance;
        $standardAttributeNames = array_keys($this->_standardAttributes);
        $attributeNames = [];

        foreach ($row->getCellIterator() as $cell) {
            if (!$cell->getValue()) {
                continue;
            }

            if (!in_array($cell->getValue(), $standardAttributeNames)) {
                throw new CellException($cell, "Attribute not exist.");
            }

            $this->_standardAttributes[$cell->getValue()]->column = $cell->getColumn();
            $attributeNames[] = $cell->getValue();
        }

        $pk = $model->primaryKey();
        $filledPk = array_intersect($pk, $attributeNames);

        // Primary keys either must not be specified at all (create mode),
        // or must be specified fully (update mode, in case of composite primary keys)
        if ($filledPk && count($pk) != count($filledPk)) {
            throw new RowException($row, 'All primary key attributes must be specified for updating model.');
        }

        return !empty($attributeNames);
    }
}
