<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use hyzhak\translate\models\Message;
use hyzhak\translate\models\MessageSource;

hyzhak\translate\assets\TranslateAsset::register($this);

$this->title = Yii::t('main', 'Translations');
$this->params['breadcrumbs'][] = [
    'label' => $this->title,
];

$this->params['breadcrumbsRight'] = [
    [
        'label' => '<i class="fa fa-plus"></i> ' . Yii::t('main', 'Add Phrase'),
        'url' => ['/translate/default/create', 'backUrl'=>Yii::$app->request->url],
        'class' => 'btn btn-sm btn-primary rounded mr-2',
        'encode' => false,
    ],
    [
        'label' => '<i class="fas fa-sync"></i> ' . Yii::t('main', 'Rescan'),
        'url' => ['/translate/default/scan'],
        'class' => 'btn btn-sm btn-success rounded',
        'encode' => false,
    ],
];

?>

<div class="message-index">

<div class="card shadow rounded-lg">
    <div class="card-body">
        <div class="mb-3 btn-group justify-content-end d-flex">
		<?php
            $filter_get = Yii::$app->request->get('filter', Message::FILTER_ALL);
            foreach ( [
                Message::FILTER_ALL             => Yii::t('main', 'All'),
                Message::FILTER_TRANSLATED      => Yii::t('main', 'Translated'),
                Message::FILTER_NOT_TRANSLATED  => Yii::t('main', 'Not Translated'),
            ] as $filter => $name ) {
                echo Html::a($name, ['/translate/default/index', 'filter'=>$filter], [
                    'class' => 'btn btn-secondary' . (($filter_get == $filter)?' active':''),
                ]);
		    }
        ?>
        </div>
    <?php echo GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'condensed' => true,
        'responsive' => true,
        'summaryOptions' => [
            'class' => 'summary text-right'
        ],
        'pager' => [
            'options' => [
                'class'=>'pagination float-right',
            ],
        ],
        'columns' => [
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' => [
                    'max-width'=>'50px',
                    'class'=>'text-center'
                ],
                'template' => '{delete}',
                'buttons' => [
                    'delete' =>  function ($url, $model, $key) {
                        return \yii\helpers\Html::a(
                            '<i class="fas fa-trash fa-lg" aria-hidden="true"></i>',
                            ['/translate/default/delete', 'backUrl'=>Yii::$app->request->url],
                            [
                                'title' => Yii::t('yii', 'Delete'),
                                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                'data-method' => 'post',
                                'data-params' => [
                                    'id'=>$model->id,
                                ],
                            ]
                        );
                    },
                ],
            ],
            [
                'attribute' => 'category',
                'value' => function ($model, $index, $dataColumn) {
                    return \yii\helpers\Inflector::camelize($model->category);
                },
                'filter' => MessageSource::getCategories(),
            ],
            'message',
            [
                'attribute' => 'translation',
                'header' => Yii::t('main', 'Translations'),
                'contentOptions' => [
                    'class' => 'translate-message',
                ],
                'value' => function ($model, $index, $dataColumn) {
                    $models = [];
                    foreach($model->translate as $item) {
                        //if($model->category != $item->category) continue;
                        $models[$item->language]= $item;
                    }
                    return $this->render('_message-show', [
                        'model'     => $model,
                        'models'    => $models,
                        'index'     => $index,
                        'column'    => $dataColumn,
                    ]);
                },
                'format' => 'raw',
            ],
        ]
    ]);
?>
    </div>
</div>
</div>
<?php
$this->registerJs('
    $("a.button-scan").on("click", function(){
        $(this).find("i").addClass("fa-spin");
    });
');
