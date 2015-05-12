<?php

use arogachev\excel\import\advanced\Importer;
use data\Author;
use data\Question;
use data\Test;
use yii\codeception\TestCase;
use yii\helpers\Html;

class AdvancedImporterTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public $appConfig = '@tests/unit/_config.php';


    public function testCommon()
    {
        $url = 'https://spreadsheets.google.com/feeds/download/spreadsheets/Export?key=1K5pS0Rz6KrM0n-ju_CBm0DZxFkKyoyJd3Orhhk3MYz4&exportFormat=xlsx';
        $path = Yii::getAlias('@tests/_output/AdvancedImporter.xlsx');
        file_put_contents($path, file_get_contents($url));

        $importer = new Importer([
            'filePath' => $path,
            'sheetNames' => ['Data'],
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
                            'name' => 'author_id',
                            'valueReplacement' => function ($value) {
                                return Author::find()->select('id')->where(['name' => $value]);
                            },
                        ],
                    ],
                ],
                [
                    'className' => Question::className(),
                    'labels' => ['Question', 'Questions'],
                ],
            ],
        ]);

        $result = $importer->run();
        $this->assertEquals($importer->error, null);
        $this->assertEquals($result, true);

        $this->assertEquals(Test::find()->count(), 5);
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
        $this->assertEquals(Test::findOne(4)->attributes, [
            'id' => 4,
            'name' => 'Language test',
            'type' => 1,
            'description' => '',
            'author_id' => 1,
        ]);
        $this->assertEquals(Test::findOne(5)->attributes, [
            'id' => 5,
            'name' => 'Science test',
            'type' => 1,
            'description' => '',
            'author_id' => 1,
        ]);

        $this->assertEquals(Question::find()->count(), 4);
        $this->assertEquals(Question::findOne(1)->attributes, [
            'id' => 1,
            'test_id' => 1,
            'content' => "What's your name?",
            'sort' => 1,
        ]);
        $this->assertEquals(Question::findOne(2)->attributes, [
            'id' => 2,
            'test_id' => 1,
            'content' => 'How old are you?',
            'sort' => 2,
        ]);
        $this->assertEquals(Question::findOne(3)->attributes, [
            'id' => 3,
            'test_id' => 1,
            'content' => "What's your name?",
            'sort' => 1,
        ]);
        $this->assertEquals(Question::findOne(4)->attributes, [
            'id' => 4,
            'test_id' => 1,
            'content' => 'How old are you?',
            'sort' => 2,
        ]);
    }
}
