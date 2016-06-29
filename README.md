# Yii 2 Excel

ActiveRecord import and export based on PHPExcel for Yii 2 framework.

This library is mainly designed to import data, export is in the raw condition (even it's working in basic form),
under development and not documented yet.

The important notes:

- It uses ActiveRecord models and PHPExcel library, so operating big data requires pretty good hardware, especially RAM.
In case of memory shortage I can advise splitting data into smaller chunks.
- This is not just a wrapper on some PHPExcel methods, it's a tool helping import data from Excel in human readable
form with minimal configuration.
- This is designed for periodical import.
- The library is more effective when working with multiple related models and complex data structures.

[![Latest Stable Version](https://poser.pugx.org/arogachev/yii2-excel/v/stable)](https://packagist.org/packages/arogachev/yii2-excel)
[![Total Downloads](https://poser.pugx.org/arogachev/yii2-excel/downloads)](https://packagist.org/packages/arogachev/yii2-excel)
[![Latest Unstable Version](https://poser.pugx.org/arogachev/yii2-excel/v/unstable)](https://packagist.org/packages/arogachev/yii2-excel)
[![License](https://poser.pugx.org/arogachev/yii2-excel/license)](https://packagist.org/packages/arogachev/yii2-excel)

- [Installation](#installation)
- [Import Basics](docs/import-basics.md)
- [Basic import](docs/import-basic.md)
- [Advanced import](docs/import-advanced.md)
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

## Running import

```php
if (!$importer->run()) {
    echo $importer->error;

    if ($importer->wrongModel) {
        echo Html::errorSummary($importer->wrongModel);
    }
}
```
