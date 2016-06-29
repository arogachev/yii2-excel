# Basic import

*Features:*

- Using attribute labels from model or custom labels
- Arbitrary amount and order of columns with attributes
- Create and update mode
- Value replacement
- Detailed error messages with exact wrong filled cell mentioning
- Getting wrong model (where import failed) for getting all validation errors or printing error summary

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

*Filling example:*

|       | A   | B                | C      | D                        | E           |
| ----- | --- | ---------------- | ------ | ------------------------ | ----------- |
| **1** | ID  | Name             | Type   | Description              | Category    |
| **2** | 1   | Temperament test | Closed | This is temperament test | Psychology  |
| **3** |     | PHP test         | Closed |                          | Programming |
| **4** |     | Git test         | Opened |                          | Programming |

- Attribute names / labels must be placed in first filled row.
- When primary key is specified, then it's update mode, otherwise - create mode.
- For composite primary keys - you always need to specify them fully.
- Completely empty rows are skipped.
