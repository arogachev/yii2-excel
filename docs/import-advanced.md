# Advanced import

Advanced import has all basic import features plus some additional features:

- Multiple sheets for grouping data
- Multiple models
- Remembering attribute names for each model and redefining them on the fly
- Model defaults
- Linking models through primary keys
- Saving and loading any amount of rows to prevent copy pasting and reduce amount of filling data

## Cell types

There are few types of cells in advanced import:

- Standard model name. By default it's written in **bold** font.
- Standard attribute name. By default it's written in *italic* font.
- Attribute value. It's written in regular font.
- Defaults section for standard model - standard model name written in **bold** text combined with underline (**___**).
- Saved model link. By default this cell has yellow color filling (HEX code - `#FFFF00`).
- Loaded model link. By default this cell has blue color filling (HEX code - `#00B0F0`).
- Saved rows block. By default this cell has green color filling (HEX code - `#00FF00`).
- Loaded rows block. By default this cell has orange color filling (HEX code - ``#F1C232`).

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
                    'name' => 'answers_display',
                    'valueReplacement' => Question::getAnswersDisplayList(),
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

## Basic filling example

In advanced import filling of the models of desired type starts with specifying standard model label. Then you need to
specify standard attribute labels horizontally in one row. After that you can fill models with attribute values under
according attribute labels.

|       | A                | B      | C                        | D           |
| ----- | ---------------- | ------ | ------------------------ | ----------- |
| **1** | **Tests**        |        |                          |             |
| **2** | *Name*           | *Type* | *Description*            | *Category*  |
| **3** | Temperament test | Closed | This is temperament test | Psychology  |
| **4** | PHP test         | Closed |                          | Programming |
| **5** | Git test         | Opened |                          | Programming |

To switch to filling models of different type just specify other standard model with its attribute labels:

|       | A            | B                                | C                 |
| ----- |------------- | -------------------------------- | ----------------- |
| **6** |              |                                  |                   |
| **7** | **Question** |                                  |                   |
| **8** | *Test*       | *Content*                        | *Answers display* |
| **9** | 1            | What PHP frameworks do you know? | Line-by-line      |

When you decide to back to filling models of type that you already used, you can omit attribute labels row, because they
are now remembered and linked to according Excel columns, so you can just write:

|        | A             | B        | C   | D            |
| ------ | ------------- | -------- | --- | ------------ |
| **10** |               |          |     |              |
| **11** | **Test**      |          |     |              |
| **13** | Database test | Opened   |     | Programming  |

But you can redefine the order and amount of used columns for each standard model at any time on the fly:

|        | A            | B           |
| ------ | ------------ | ----------- |
| **14** |              |             |
| **15** | **Test**     |             |
| **16** | *Name*       | *Category*  |
| **17** | Courage test | Psychology  |
| **18** | PHP test     | Programming |
| **19** | Git test     | Programming |

## Working with relational data

We can remember any model like this:

|       | A        | B      | C                             | D           | E        |
| ----- | -------- | ------ | ----------------------------- | ----------- | -------- |
| **1** | **Test** |        |                               |             |          |
| **2** | *Name*   | *Type* | *Description*                 | *Category*  |          |
| **3** | PHP test | Closed | How good are your PHP skills? | Programming | PHP test |

The cell must be located right after the last filled attribute column and have **blue filling** (you can override
that). You need to specify label of saved link, in this case it matches the `name` attribute of the model. The label
can have any name that your want and only used in Excel file for linking purpose.

Later you can retrieve that link and use it like that:

|       | A            | B                                | C                 |
| ----- |------------- | -------------------------------- | ----------------- |
| **4** |              |                                  |                   |
| **5** | **Question** |                                  |                   |
| **6** | *Test*       | *Content*                        | *Answers display* |
| **7** | PHP test     | What PHP frameworks do you know? | Line-by-line      |

The cell `A9` must have **yellow filling** (you can override that), otherwise it will be treated as value, not link.

As a result, after saving linked model primary key value will be fetched and assigned to this attribute.

Obviously before marking cell as linked to other model primary key, you need to mark according model for saving above
(in case of the same sheet) or in previous sheets.

Filling example is available [here](https://docs.google.com/spreadsheets/d/1WQp1JkQNU8tAxX1nMg7rEd_G0kqkaqIVeFx1CjHWHgM/edit?usp=sharing).
