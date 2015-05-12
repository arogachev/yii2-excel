<?php

namespace arogachev\excel\import\advanced;

use arogachev\excel\import\basic\Model as BasicModel;
use arogachev\excel\import\DI;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property StandardModel $_standardModel
 * @property StandardModel $standardModel
 * @property Attribute[] $_attributes
 * @property string $savedPk
 */
class Model extends BasicModel
{
    /**
     * @var string
     */
    protected $_savedPk;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->initAttributes();
        $this->mergeDefaultAttributes();
        $this->initSavedPk();
        $this->trigger(self::EVENT_INIT);
    }

    /**
     * @param array $config
     */
    protected function initAttribute($config)
    {
        $attribute = new Attribute($config);
        $attribute->linkRelatedModel();
        $this->_attributes[] = $attribute;
    }

    protected function initSavedPk()
    {
        $sheet = $this->row->getCellIterator()->current()->getWorksheet();
        $columns = ArrayHelper::getColumn($this->_standardModel->standardAttributes, 'column');
        sort($columns);
        $lastColumn = end($columns);
        $cell = $sheet->getCell(++$lastColumn . $this->row->getRowIndex());

        if (DI::getCellParser()->isSavedPk($cell)) {
            $this->_savedPk = DI::getCellParser()->getSavedPk($cell);
        }
    }

    protected function mergeDefaultAttributes()
    {
        foreach ($this->_standardModel->defaultAttributes as $defaultAttribute) {
            $isFound = false;
            foreach ($this->_attributes as $index => $attribute) {
                $namesMatch = $defaultAttribute->standardAttribute->name == $attribute->standardAttribute->name;
                if ($namesMatch && $attribute->value = null) {
                    $defaultAttribute->linkRelatedModel();
                    $this->_attributes[$index] = $defaultAttribute;
                    $isFound = true;

                    break;
                }
            }

            if (!$isFound) {
                $defaultAttribute->linkRelatedModel();
                $this->_attributes[] = $defaultAttribute;
            }
        }
    }

    public function load()
    {
        $this->loadExisting();
        $this->replaceSavedPkLinks();
        $this->assignMassively();
    }

    protected function replaceSavedPkLinks()
    {
        foreach ($this->_attributes as $attribute) {
            if ($attribute->relatedModel) {
                $attribute->value = $attribute->relatedModel->_instance->primaryKey;
            }
        }
    }

    /**
     * @return string
     */
    public function getSavedPk()
    {
        return $this->_savedPk;
    }
}
