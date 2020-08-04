<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
use hyzhak\translate\models\Message;
use hyzhak\translate\models\MessageSource;
use hyzhak\translate\assets\TranslateAsset;

TranslateAsset::register($this);

$this->title = Yii::t('main', 'Translations');
$this->params['breadcrumbs'][] = [
	'label' => $this->title,
	'template' => "<li><i class='fa fa-language'></i> {link}</li>\n",
];
?>

<div class="message-index">

<div class="box box-primary">
	<div class="box-header with-border">
		<span class="pull-left">
			<div class="btn-group">
			<?php
				$filter_get = Yii::$app->request->get('filter', Message::FILTER_ALL);
				foreach ( [
						Message::FILTER_ALL             => Yii::t('main', 'All'),
						Message::FILTER_TRANSLATED      => Yii::t('main', 'Translated'),
						Message::FILTER_NOT_TRANSLATED  => Yii::t('main', 'Not Translated'),
					] as $filter => $name ) :
					echo Html::a($name, ['/translate/default/index', 'filter'=>$filter], [
						'class' => 'btn btn-default' . (($filter_get == $filter)?' active':''),
					]);
			?>
			<?php endforeach; ?>
			</div>
		</span>

		<span class="pull-right">
			<div class="btn-group">
				<a class="btn btn-primary" href="<?php
					echo Url::to(['/translate/default/create', 'backUrl'=>Yii::$app->request->url]); ?>"><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;<?php
					echo Yii::t('main', 'Add Phrase'); ?></a>
				<a class="btn btn-success button-scan" href="<?php
					echo Url::to(['/translate/default/scan']); ?>"><i class="fa fa-refresh"></i>&nbsp;&nbsp;&nbsp;<?php
					echo Yii::t('main', 'Rescan'); ?></a>
			</div>
		</span>
	</div>
	<div class="box-body pad table-responsive">
	<?php
	echo \yii\grid\GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'options' => [
			'class'=>'table-responsive table-condensed'
		],
		'summaryOptions' => [
			'class' => 'summary text-right'
		],
		'pager' => [
			'options' => [
				'class'=>'pagination pull-right',
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
							'<span class="fa fa-trash fa-lg"></span>',
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
