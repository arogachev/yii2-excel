<?php

namespace divad942\excel\import\exceptions;

use yii\base\Exception;

class ImportException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Import Exception';
    }
}
