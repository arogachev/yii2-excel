<?php

namespace arogachev\excel\import\exceptions;

use arogachev\excel\import\basic\StandardAttribute;

class StandardAttributeException extends ImportException
{
    /**
     * @param StandardAttribute $standardAttribute
     * {@inheritdoc}
     */
    public function __construct(StandardAttribute $standardAttribute, $message = "", $code = 0, \Exception $previous = null)
    {
        $attributeName = 'attribute';
        if ($standardAttribute->name) {
            $attributeName .= " $standardAttribute->name";
        }

        $modelClass = "{$standardAttribute->standardModel->className}";
        $message = "Invalid configuration for $attributeName in model $modelClass. $message";

        parent::__construct($message, $code, $previous);
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'Standard Attribute Exception';
    }
}
