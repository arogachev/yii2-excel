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

**Important note:** because of limitations of Github Flavored Markdown, it's impossible to use underlined text or color
filling, so pay attention to notes near the examples. Also please refer to Excel file at the end of this document for
better understanding.

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

The saved model link (cell `E3`) must be located right after the last filled attribute column and have **blue filling**
(you can override that). You need to specify label of saved link, in this case it matches the `name` attribute of the
model. The label can have any name that your want and only used in Excel file for linking purpose.

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

Also, when you switch to filling model of different type, the link to last filled model of previous used type is
remembered automatically, so to use it you need just mark the cell as loaded model link and don't write the label:

|       | A            | B                                | C                             | D           |
| ----- | ------------ | -------------------------------- | ----------------------------- | ----------- |
| **1** | **Test**     |                                  |                               |             |
| **2** | *Name*       | *Type*                           | *Description*                 | *Category*  |
| **3** | PHP test     | Closed                           | How good are your PHP skills? | Programming |
| **4** |              |                                  |                               |             |
| **5** | **Question** |                                  |                               |             |
| **6** | *Test*       | *Content*                        | *Answers display*             |             |
| **7** |              | What PHP frameworks do you know? | Line-by-line                  |             |

The cell `A7` must have **yellow filling** (you can override that), otherwise it will be treated as value, not link.

## Model defaults

You can specify default values for attributes of each standard model right in Excel sheet, no additional configuration
needed. Filling defaults is similar to filling models, but you need to mark standard model label as the beginning of the
defaults section. It must be written in **bold** text combined with underline (**___**).

First, it's useful for frequently used values or for a set of identical values:

|       | A        | B           |
| ----- | -------- | ----------- |
| **1** | **Test** |             |
| **2** | *Type*   | *Category*  |
| **3** | Opened   | Programming |

The text cell `A1` must also be underlined (**___**, you can override that), otherwise it will be treated as the
beginning of filling regular values, not defaults.

When you mark cell as standard model defaults label, the filling mode is switched to filling defaults, so make sure to
write standard model label again below to fill regular values.

So if we want to fill a set of programming tests, we can completely skip filling `Category` column. And if majority of
tests have `opened` type, we can skip the filling of these values and fill `Type` column cells only for tests which type
is different (for example `closed` type):

|       | A             | B      | C             |
| ----- | ------------- | ------ | ------------- |
| **4** |               |        |               |
| **5** | **Tests**     |        |               |
| **6** | *Name*        | *Type* | *Description* |
| **7** | Git test      |        |               |
| **8** | Database test |        |               |
| **9** | PHP test      | Closed |               |

You can specify defaults at any moment that you want and redefine as many times as you want. If you want to add one more
default value, you don't need to copy paste previous default values (they are remembered already), just write needed
column with value:

|        | A                                                 |
| ------ | ------------------------------------------------- |
| **9**  |                                                   |
| **10** | **Test**                                          |
| **11** | *Description*                                     |
| **12** | This test was created by professional programmer. |

To clean the default value just leave the cell empty in the next defaults section:

|        | A          |
| ------ | ---------- |
| **13** |            |
| **14** | **Test**   |
| **15** | *Category* |
| **16** |            |

But the biggest advantage of that is you can define the relationships of the models in defaults, and combining with the
right order or models, we can completely eliminate filling relationships every time, which is of course more
user-friendly.

For example, test consists of questions, and each question can contain set of answers, so we can write the following
defaults:

|       | A            |
| ----- | ------------ |
| **1** | **Question** |
| **2** | *Test*       |
| **3** |              |
| **4** |              |
| **5** | **Answer**   |
| **6** | *Question*   |
| **7** |              |

Cells `A3` and `A6` is marked as loaded model links (yellow filling is used by default, you can override that).

So if we design our filling to fill tests with its content one by one (one test per file or one per sheet or one after
another), and order of models to be like so: test - question - followed by its answers - next question - followed by
its answers, etc. which is more natural for content manager, we can completely forget about setting relationships now
and just fill the data.

It's recommended to move defaults to separate sheet and place it before the others and provide this template to content
managers.

|        | A                                | B                         | C                                  | D           |
| ------ | -------------------------------- | ------------------------- | ---------------------------------- | ----------- |
| **1**  | **Test**                         |                           |                                    |             |
| **2**  | *Name*                           | *Type*                    | *Description*	                     | *Category*  |
| **3**  | PHP test                         | Closed	                | This test show good are you at PHP | Programming |
| **4**  |                                  |                           |                                    |             |
| **5**  | **Question**                     |                           |                                    |             |
| **6**  | *Content*	                    | *Display*                 |                                    |             |
| **7**  | What PHP frameworks do you know? | Line-by-line              |                                    |             |
| **8**  |                                  |                           |                                    |             |
| **9**  | **Answers**                      |                           |                                    |             |
| **10** | *Content*                        | *Is supplemented by text* |                                    |             |
| **11** | Yii2                             |                           |                                    |             |
| **12** | Laravel 5                        |                           |                                    |             |
| **13** | Symfony 2                        |                           |                                    |             |
| **14** | Other (specify)                  | Yes                       |                                    |             |
| **15** |                                  |                           |                                    |             |
| **16** | **Question**                     |                           |                                    |             |
| **17** | Do you use VCS in your work?     |                           |                                    |             |
| **18** |                                  |                           |                                    |             |
| **19** | **Answers**	                    |                           |                                    |             |
| **20** | Yes	                            |                           |                                    |             |
| **21** | No	                            |                           |                                    |             |

Full filling example is available [here](https://docs.google.com/spreadsheets/d/1WQp1JkQNU8tAxX1nMg7rEd_G0kqkaqIVeFx1CjHWHgM/edit?usp=sharing).
