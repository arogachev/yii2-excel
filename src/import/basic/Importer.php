<?php

namespace divad942\excel\import\basic;

use divad942\excel\helpers\PHPExcelHelper;
use divad942\excel\import\BaseImporter;
use divad942\excel\import\exceptions\RowException;
use Yii;

class Importer extends BaseImporter
{
    /**
     * @inheritdoc
     */
    protected function fillModels($rows)
    {
        $c = 1;
        foreach ($rows as $row) {
            if (PHPExcelHelper::isRowEmpty($row)) {
                break;
            }

            if ($c == 1) {
                if (!$this->_standardModels[0]->parseAttributeNames($row)) {
                    throw new RowException($row, 'Attribute names must be placed in first filled row.');
                }
            } else {
                $this->_models[] = new Model([
                    'row' => $row,
                    'standardModel' => $this->_standardModels[0],
                ]);
            }

            $c++;
        }
    }

    /**
     * @inheritdoc
     */
    protected function safeRun()
    {
        parent::safeRun();

        $this->fillModels($this->_phpExcel->getActiveSheet()->getRowIterator());

        foreach ($this->_models as $model) {
            $model->load();
            $model->validate();
        }

        Yii::$app->db->transaction(function () {
            foreach ($this->_models as $model) {
                $model->save();
            }
        });

        $this->trigger(self::EVENT_RUN);
    }
}
