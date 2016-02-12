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
                if (!$namesMatch) {
                    continue;
                } else {
                    $isFound = true;
                }

                $isLoadedPk = DI::getCellParser()->isLoadedPk($attribute->getInitialCell());
                if ($namesMatch && $attribute->value === null && !$isLoadedPk) {
                    $this->mergeDefaultAttribute($defaultAttribute, $index);

                    break;
                }
            }

            if (!$isFound) {
                $this->mergeDefaultAttribute($defaultAttribute);
            }
        }
    }

    /**
     * @param Attribute $defaultAttribute
     * @param null|integer $index
     * @throws \arogachev\excel\import\exceptions\CellException
     */
    protected function mergeDefaultAttribute($defaultAttribute, $index = null)
    {
        $attribute = new Attribute([
            'standardAttribute' => $defaultAttribute->standardAttribute,
            'cell' => $defaultAttribute->getInitialCell(),
        ]);
        $attribute->linkRelatedModel();
        $index === null ? $this->_attributes[] = $attribute : $this->_attributes[$index] = $attribute;
    }

    public function load()
    {
        $this->replaceValues();

        parent::load();
    }

    protected function replaceValues()
    {
        foreach ($this->_attributes as $attribute) {
            if ($attribute->relatedModel) {
                $attribute->value = $attribute->relatedModel->instance->primaryKey;
            } elseif ($attribute->standardAttribute->valueReplacement) {
                $attribute->replaceValue(true);
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
