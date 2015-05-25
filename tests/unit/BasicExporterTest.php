<?php

use arogachev\excel\export\basic\Exporter;
use data\Test;
use yii\codeception\TestCase;
use yii\helpers\HtmlPurifier;

class BasicExporterTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public $appConfig = '@tests/unit/_config.php';


    public function testCommon()
    {
        $exporter = new Exporter([
            'query' => Test::find()->where(['id' => 1]),
            'filePath' => Yii::getAlias('@tests/_output/BasicExporter.xlsx'),
            'sheetTitle' => 'Tests',
            'standardModelsConfig' => [
                [
                    'className' => Test::className(),
                    'standardAttributesConfig' => [
                        [
                            'name' => 'type',
                            'valueReplacement' => function ($model) {
                                /* @var $model Test */
                                return $model->getTypeLabel();
                            },
                        ],
                        [
                            'name' => 'description',
                            'valueReplacement' => function ($model) {
                                /* @var $model Test */
                                return HtmlPurifier::process($model->description, [
                                    'HTML.ForbiddenElements' => ['p'],
                                ]);
                            },
                        ],
                        [
                            'name' => 'author_id',
                            'valueReplacement' => function ($model) {
                                /* @var $model Test */
                                return $model->author->name;
                            },
                        ],
                    ],
                ],
            ],
            'attributesOrder' => ['ID', 'Name', 'Type', 'Description', 'Author'],
        ]);

        $exporter->run();

        $phpExcel = PHPExcel_IOFactory::load($exporter->filePath);

        $this->assertEquals($phpExcel->getActiveSheet()->getTitle(), 'Tests');

        $attributeNames = [];
        $attributeValues = [];
        $c = 1;
        foreach ($phpExcel->getActiveSheet()->getRowIterator() as $row) {
            if ($c > 2) {
                break;
            }

            foreach ($row->getCellIterator() as $cell) {
                $column = $cell->getColumn();
                $value = $cell->getValue();
                $c == 1 ? $attributeNames[$column] = $value : $attributeValues[$column] = $value;
            }

            $c++;
        }

        $attributeValues['A'] = (int) $attributeValues['A'];

        $this->assertEquals($attributeNames, [
            'A' => 'ID',
            'B' => 'Name',
            'C' => 'Type',
            'D' => 'Description',
            'E' => 'Author'
        ]);
        $this->assertEquals($attributeValues, [
            'A' => 1,
            'B' => 'Common test',
            'C' => 'Closed',
            'D' => 'This is the common test',
            'E' => 'Ivan Ivanov'
        ]);
    }
}
