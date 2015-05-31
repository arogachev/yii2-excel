<?php

namespace arogachev\excel\components;

use yii\base\Component;

/**
 * @property StandardModel $standardModel
 * @property \yii\db\ActiveRecord $instance
 */
abstract class Model extends Component
{
    /**
     * @var StandardModel
     */
    protected $_standardModel;

    /**
     * @var Attribute[]
     */
    protected $_attributes = [];

    /**
     * @var string
     */
    protected static $attributeClassName = 'arogachev\excel\components\Attribute';

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
    }

    abstract protected function initAttributes();

    /**
     * @param array $config
     */
    protected function initAttribute($config)
    {
        $className = static::$attributeClassName;
        $extendedConfig = array_merge($config, ['model' => $this]);
        $this->_attributes[] = new $className($extendedConfig);
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
