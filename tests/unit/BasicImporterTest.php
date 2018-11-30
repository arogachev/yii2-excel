<?php

use divad942\excel\import\basic\Importer;
use data\Author;
use data\Test;
use yii\codeception\TestCase;
use yii\helpers\Html;

class BasicImporterTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public $appConfig = '@tests/unit/_config.php';


    public function testCommon()
    {
        $url = 'https://spreadsheets.google.com/feeds/download/spreadsheets/Export?key=18EybqxPRLadRcXn9_xN1aTGzQkJb6B3fvgCDTYW__gU&exportFormat=xlsx';
        $path = Yii::getAlias('@tests/_output/BasicImporter.xlsx');
        file_put_contents($path, file_get_contents($url));

        $importer = new Importer([
            'filePath' => $path,
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
                            'name' => 'author_id',
                            'valueReplacement' => function ($value) {
                                return Author::find()->select('id')->where(['name' => $value]);
                            },
                        ],
                    ],
                ],
            ],
        ]);

        $result = $importer->run();
        $this->assertEquals($importer->error, null);
        $this->assertEquals($result, true);

        $this->assertEquals(Test::find()->count(), 3);
        $this->assertEquals(Test::findOne(1)->attributes, [
            'id' => 1,
            'name' => 'Basic test',
            'type' => 2,
            'description' => '<p>This is the basic test</p>',
            'author_id' => 2,
        ]);
        $this->assertEquals(Test::findOne(2)->attributes, [
            'id' => 2,
            'name' => 'Common test',
            'type' => 1,
            'description' => '',
            'author_id' => 1,
        ]);
        $this->assertEquals(Test::findOne(3)->attributes, [
            'id' => 3,
            'name' => 'Programming test',
            'type' => 2,
            'description' => '',
            'author_id' => 2,
        ]);
    }
}
