# Yii 2 Excel

ActiveRecord import and export based on PHPExcel for Yii 2 framework.

[![Latest Stable Version](https://poser.pugx.org/arogachev/yii2-excel/v/stable)](https://packagist.org/packages/arogachev/yii2-excel)
[![Total Downloads](https://poser.pugx.org/arogachev/yii2-excel/downloads)](https://packagist.org/packages/arogachev/yii2-excel)
[![Latest Unstable Version](https://poser.pugx.org/arogachev/yii2-excel/v/unstable)](https://packagist.org/packages/arogachev/yii2-excel)
[![License](https://poser.pugx.org/arogachev/yii2-excel/license)](https://packagist.org/packages/arogachev/yii2-excel)

- [Installation](#installation)
- [Basic import](#basic-import)
- [Advanced import](#advanced-import)
- [Running import](#running-import)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist arogachev/yii2-excel
```

or add

```
"arogachev/yii2-excel": "*"
```

to the require section of your `composer.json` file.

## Basic import

*Features:*

- Using attribute labels from model or custom labels
- Arbitrary amount and order of columns with attributes
- Create and update mode
- Value replacement
- Detailed error messages with exact wrong filled cell mentioning
- Wrong model (where import failed) for getting all validation errors or printing error summary

*Configuration example:*

```php
use arogachev\excel\import\basic\Importer;
use frontend\models\Category;
use frontend\models\Test;
use Yii;
use yii\helpers\Html;

$importer = new Importer([
    'filePath' => Yii::getAlias('@frontend/data/test.xlsx'),
    'standardModelsConfig' => [
        [
            'className' => Test::className(),
            'standardAttributesConfig' => [
                [
                    'name' => 'type',
                    'valueReplacement' => Test::getTypesList(),
                ],
                [
                    'name' => 'description',
                    'valueReplacement' => function ($value) {
                        return $value ? Html::tag('p', $value) : '';
                    },
                ],
                [
                    'name' => 'category_id',
                    'valueReplacement' => function ($value) {
                        return Category::find()->select('id')->where(['name' => $value]);
                    },
                ],
            ],
        ],
    ],
]);
```

where `getTypesList()` method must be declared like this:

```php
<?php

namespace frontend\models;

use yii\db\ActiveRecord;

class Test extends ActiveRecord
{
    // Types

    /**
     * Selecting from the list of answers
     */
    const TYPE_CLOSED = 1;

    /**
     * Writing answer manually
     */
    const TYPE_OPENED = 2;

    ...

    /**
     * @return array
     */
    public static function getTypesList()
    {
       return [
           self::TYPE_CLOSED => 'Closed',
           self::TYPE_OPENED => 'Opened',
       ];
    }
}
```

Filling example with notes is available [here](https://docs.google.com/spreadsheets/d/1BjScMwz10s80b6gh4QzzENJygd50jXdyoc_pvQFPGrk/edit?usp=sharing).
(hover over cells with black triangle in the top right corner to see them).

## Advanced import

*Features:*

- Multiple sheets for grouping data
- Multiple models
- Remembering of attribute names for each model
- Model defaults
- Linking models through primary keys
- Saving and loading any amount of rows

*Configuration example:*

```php
use arogachev\excel\import\advanced\Importer;
use frontend\models\Answer;
use frontend\models\Category;
use frontend\models\Question;
use frontend\models\Test;
use Yii;
use yii\helpers\Html;

$importer = new Importer([
    'filePath' => Yii::getAlias('@frontend/data/test.xlsx'),
    'sheetNames' => ['PHP test', 'Courage test'],
    'standardModelsConfig' => [
        [
            'className' => Test::className(),
            'labels' => ['Test', 'Tests'],
            'standardAttributesConfig' => [
                [
                    'name' => 'type',
                    'valueReplacement' => Test::getTypesList(),
                ],
                [
                    'name' => 'description',
                    'valueReplacement' => function ($value) {
                        return $value ? Html::tag('p', $value) : '';
                    },
                ],
                [
                    'name' => 'category_id',
                    'valueReplacement' => function ($value) {
                        return Category::find()->select('id')->where(['name' => $value]);
                    },
                ],
            ],
        ],
        [
            'className' => Question::className(),
            'labels' => ['Question', 'Questions'],
            'standardAttributesConfig' => [
                [
                    'name' => 'display',
                    'valueReplacement' => Question::getDisplayList(),
                ],
            ],
        ],
        [
            'className' => Answer::className(),
            'labels' => ['Answer', 'Answers'],
            'standardAttributesConfig' => [
                [
                    'name' => 'is_supplemented_by_text',
                    'valueReplacement' => Yii::$app->formatter->booleanFormat,
                ],
            ],
        ],
    ],
]);
```

Filling example is available [here](https://docs.google.com/spreadsheets/d/1WQp1JkQNU8tAxX1nMg7rEd_G0kqkaqIVeFx1CjHWHgM/edit?usp=sharing).

## Running import

```php
if (!$importer->run()) {
    echo $importer->error;

    if ($importer->wrongModel) {
        echo Html::errorSummary($importer->wrongModel);
    }
}
```
