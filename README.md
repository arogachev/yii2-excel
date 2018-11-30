# Yii 2 Excel

This is fork of https://github.com/arogachev/yii2-excel

Fixed compatibility issues with PHP 7.2

ActiveRecord import and export based on PHPExcel for Yii 2 framework.

- [Installation](#installation)
- [Import Basics](docs/import-basics.md)
- [Basic import](docs/import-basic.md)
- [Advanced import](docs/import-advanced.md)
- [Running import](#running-import)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist divad942/yii2-excel
```

or add

```
"divad942/yii2-excel": "*"
```

to the require section of your `composer.json` file.

## Running import

```php
if (!$importer->run()) {
    echo $importer->error;

    if ($importer->wrongModel) {
        echo Html::errorSummary($importer->wrongModel);
    }
}
```
