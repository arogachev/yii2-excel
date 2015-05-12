<?php

namespace arogachev\excel\import\advanced;

use arogachev\excel\import\basic\Attribute as BasicAttribute;
use arogachev\excel\import\DI;
use arogachev\excel\import\exceptions\CellException;
use Yii;

/**
 * @property Model $relatedModel
 */
class Attribute extends BasicAttribute
{
    /**
     * @var Model
     */
    protected $_relatedModel;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!DI::getCellParser()->isLoadedPk($this->cell)) {
            parent::init();
        }
    }

    public function linkRelatedModel()
    {
        if (!DI::getCellParser()->isLoadedPk($this->cell)) {
            return;
        }

        $loadedPk = DI::getCellParser()->getLoadedPk($this->cell);
        foreach (array_reverse(DI::getImporter()->models) as $model) {
            $isRelatedByPk = $loadedPk && $model->savedPk == $loadedPk;

            $attributeModelClass = $this->_standardAttribute->standardModel->className;
            $modelClass = $model->standardModel->className;
            $isRelatedByLastPk = $loadedPk === '' && $attributeModelClass != $modelClass;

            if ($isRelatedByPk || $isRelatedByLastPk) {
                $this->_relatedModel = $model;

                break;
            }
        }

        if (!$this->_relatedModel) {
            throw new CellException($this->cell, 'Related model not found.');
        }
    }

    /**
     * @return Model
     */
    public function getRelatedModel()
    {
        return $this->_relatedModel;
    }

    /**
     * @param Model $value
     */
    public function setRelatedModel($value)
    {
        $this->_relatedModel = $value;
    }
}
