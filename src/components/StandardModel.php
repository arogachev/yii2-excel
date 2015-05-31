<?php

namespace arogachev\excel\components;

use yii\base\InvalidParamException;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * @property StandardAttribute[] $standardAttributes
 * @property \yii\db\ActiveRecord $instance
 */
class StandardModel extends Object
{
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
     * @var array
     */
    public $standardAttributesConfig = [];

    /**
     * @var string
     */
    protected static $standardAttributeClassName = 'arogachev\excel\components\StandardAttribute';

    /**
     * @var \yii\db\ActiveRecord
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
        $this->validateStandardAttributes();
        $this->indexStandardAttributes();
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
    }

    /**
     * @param array $config
     */
    protected function initStandardAttribute($config)
    {
        $className = static::$standardAttributeClassName;
        $extendedConfig = array_merge($config, ['standardModel' => $this]);
        $standardAttribute = new $className($extendedConfig);
        $this->_standardAttributes[] = $standardAttribute;
    }

    protected function validateStandardAttributes()
    {
        $attributeNames = ArrayHelper::getColumn($this->_standardAttributes, 'name');
        if ($attributeNames != array_unique($attributeNames)) {
            throw new InvalidParamException("For standard model \"$this->className\" attribute names are not unique.");
        }

        if ($this->useAttributeLabels) {
            $attributeLabels = ArrayHelper::getColumn($this->_standardAttributes, 'label');
            if ($attributeLabels != array_unique($attributeLabels)) {
                throw new InvalidParamException("For standard model \"$this->className\" attribute labels are not unique.");
            }
        }
    }

    protected function indexStandardAttributes()
    {
        $propertyName = $this->useAttributeLabels ? 'label' : 'name';
        $standardAttributes = $this->_standardAttributes;
        $this->_standardAttributes = [];

        foreach ($standardAttributes as $standardAttribute) {
            $this->_standardAttributes[$standardAttribute->{$propertyName}] = $standardAttribute;
        }
    }

    /**
     * @return array
     */
    protected function getAllowedAttributes()
    {
        return $this->_instance->attributes();
    }

    /**
     * @return \yii\db\ActiveRecord
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

    /**
     * @param StandardAttribute[] $value
     */
    public function setStandardAttributes($value)
    {
        $this->_standardAttributes = $value;
    }
}
