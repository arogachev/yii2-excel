<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\import\exceptions\CellException;
use arogachev\excel\import\exceptions\RowException;
use PHPExcel_Worksheet_Row;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\base\Object;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property ActiveRecord $instance
 * @property StandardAttribute[] $standardAttributes
 */
class StandardModel extends Object
{
    const SCENARIO_IMPORT = 'import';

    /**
     * @var string
     */
    public $className;

    /**
     * @var boolean
     */
    public $useAttributeLabels = true;

    /**
     * @var boolean
     */
    public $extendStandardAttributes = true;

    /**
     * @var boolean
     */
    public $setScenario = false;

    /**
     * @var array
     */
    public $standardAttributesConfig = [];

    /**
     * @var array
     */
    protected static $_setScenarioEvents = [
        ActiveRecord::EVENT_INIT,
        ActiveRecord::EVENT_AFTER_FIND,
    ];

    /**
     * @var ActiveRecord
     */
    protected $_instance;

    /**
     * @var StandardAttribute[]
     */
    protected $_standardAttributes = [];


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initInstance();
        $this->initStandardAttributes();
        $this->configureEventHandlers();
    }

    /**
     * @throws InvalidParamException
     */
    protected function initInstance()
    {
        if (!$this->className) {
            throw new InvalidParamException('Class name is required for standard model.');
        }

        $this->_instance = new $this->className;

        if ($this->setScenario) {
            $this->_instance->scenario = self::SCENARIO_IMPORT;
        }
    }

    /**
     * @throws InvalidParamException
     */
    protected function initStandardAttributes()
    {
        foreach ($this->standardAttributesConfig as $config) {
            $this->initStandardAttribute($config);
        }

        if ($this->extendStandardAttributes) {
            $existingAttributes = ArrayHelper::getColumn($this->_standardAttributes, 'name');
            $missingAttributes = array_diff($this->getAllowedAttributes(), $existingAttributes);

            foreach ($missingAttributes as $attributeName) {
                $this->initStandardAttribute(['name' => $attributeName]);
            }
        }

        $attributeLabels = ArrayHelper::getColumn($this->_standardAttributes, 'name', 'label');
        if ($attributeLabels != array_unique($attributeLabels)) {
            throw new InvalidParamException("For standard model \"$this->className\" attribute labels are not unique.");
        }
    }

    /**
     * @param array $config
     */
    protected function initStandardAttribute($config)
    {
        $standardAttribute = new StandardAttribute(array_merge($config, ['standardModel' => $this]));
        $propertyName = $this->useAttributeLabels ? 'label' : 'name';
        $this->_standardAttributes[$standardAttribute->{$propertyName}] = $standardAttribute;
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

    /**
     * @return ActiveRecord
     */
    public function getInstance()
    {
        return $this->_instance;
    }

    /**
     * @return StandardAttribute[]
     */
    public function getStandardAttributes()
    {
        return $this->_standardAttributes;
    }
}
