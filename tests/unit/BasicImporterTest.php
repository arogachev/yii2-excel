<?php

use arogachev\excel\import\basic\Importer;
use data\Author;
use data\Test;
use yii\codeception\TestCase;

class BasicImporterTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public $appConfig = '@tests/unit/_config.php';


    public function testCommon()
    {
        $url = 'https://spreadsheets.google.com/feeds/download/spreadsheets/Export?key=18EybqxPRLadRcXn9_xN1aTGzQkJb6B3fvgCDTYW__gU&exportFormat=xlsx';
        $path = Yii::getAlias('@tests/_output/BasicImporter.xslx');
        file_put_contents($path, file_get_contents($url));

        $importer = new Importer([
            'filePath' => $path,
            'standardModelsConfig' => [
                [
                    'className' => Test::className(),
                    'standardAttributesConfig' => [
                        [
                            'name' => 'type',
                            'valueReplacementList' => Test::getTypesList(),
                        ],
                        [
                            'name' => 'author_id',
                            'valueReplacementQuery' => function ($value) {
                                return Author::find()->select('id')->where(['name' => $value]);
                            },
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals($importer->run(), true);
        $this->assertEquals(Test::find()->count(), 3);
        $this->assertEquals(Test::findOne(1)->attributes, [
            'id' => 1,
            'name' => 'Basic test',
            'type' => 2,
            'author_id' => 2,
        ]);
        $this->assertEquals(Test::findOne(2)->attributes, [
            'id' => 2,
            'name' => 'Common test',
            'type' => 1,
            'author_id' => 1,
        ]);
        $this->assertEquals(Test::findOne(3)->attributes, [
            'id' => 3,
            'name' => 'Programming test',
            'type' => 2,
            'author_id' => 2,
        ]);
    }
}
