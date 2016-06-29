# Basics

This library works with ActiveRecord models.

There are 4 main types of objects, first two of them are configurable.

## Standard Model

Base model to use. It contains relations to the actual ActiveRecord model class and to list of used standard attributes.

Common properties:

- `className` - related ActiveRecord model class name. Required. It's recommended to use `className()` static method of
`yii\db\ActiveRecord` to get it. For example: `Post::className()`.
- `useAttributeLabels` - either to use user-friendly attribute labels or raw names (according database column names).
Defaults to `true`. Using this you can avoid writing `author_id` and write `Author` instead and use translations for
your language.
- `extendStandardAttributes` - extend user defined standard attributes with missing attributes of this model. Defaults
to `true`.
- `standardAttributesConfig` - list of related standard attributes with their configuration.

Import properties:

- `setScenario` - set separate scenario for saving models. Defaults to `false`. When using that, scenario is set to
`import`, so you can configure separate validation rules for import.
- `labels` - list of labels used to determine this standard model in Excel data. For advanced import only. It's
recommended to use singular and multiple word forms: `['Post', 'Posts']` so you can write appropriate form depending on
context.

## Standard Attribute

Base attribute belonging to standard model.

Properties:

- `name` - related attribute name. Required.
- `label` - user-friendly label. Optional, by default it's taken from ActiveRecord `attributeLabels()` list.
- `valueReplacement` - value mapping to avoid hard coding ids, constants or convert / format value to desired state.
You can specify a list, for example: `Test::getTypesList()`, where `getTypesList` method can be declared like this:

```
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

or use a callable.

One way is to use it to get the id of related model from human readable form:

```php
function ($value) {
    return Category::find()->select('id')->where(['name' => $value]);
},
```

In this case you must return `ActiveQuery`, not ActiveRecord or set of ActiveRecords, so do not add `->one()` or
`->all()` to the query chain. This is needed to make sure there is exactly one matching value exists.
Specifying attribute in `select` is also important, otherwise first attribute will be selected.

Another way is just return the new formatted value:

```php
function ($value) {
    return $value ? Html::tag('p', $value) : '';
},
```

In this case we are using this as decorator to wrap text in paragraph tag to prevent writing HTML in Excel. Obviously
it can be more complicated that that.

## Model

Model instance containing data taken from Excel sheet row (and converted, if needed). Belongs to standard model and has
some attributes.

## Attribute

The value of attribute.
