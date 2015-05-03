<?php

namespace arogachev\excel\import\basic;

use arogachev\excel\import\BaseImporter;
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
            if ($c == 1) {
                $this->_standardModels[0]->parseAttributeNames($row);
            } else {
                $this->_models = new Model([
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
            $model->validate();
        }

        Yii::$app->db->transaction(function () {
            foreach ($this->_models as $model) {
                $model->save(false);
            }
        });

        $this->trigger(self::EVENT_RUN);
    }
}
