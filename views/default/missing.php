<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Dictionary;

$this->title = Yii::t('main', 'Create');
$this->params['breadcrumbs'][] = [
	'label' => Yii::t('main', 'Translate'),
	'url' => ['index'],
	'template' => "<li><i class='fa fa-language'></i> {link}</li>\n",
];
$this->params['breadcrumbs'][] = $this->title;;

?>
<div class="message-create">

	<?php $form = ActiveForm::begin(); ?>
	<?= $form->errorSummary($model, ['class'=>'callout callout-danger']); ?>

	<div class="box box-primary">
		<div class="box-body">
			<?= $model->getAttributeLabel('category') ?>:
			<?= \yii\helpers\Inflector::camelize($attributes['category']) ?><br />

			<h4><?= $model->getAttributeLabel('source') ?>:
			<strong><?= $attributes['source'] ?></strong></h4>

			<?php foreach(Yii::$app->i18n->languages  as $lang ) {
				$field = 'translation['.$lang.']';
				echo Html::beginTag('div', ['class'=>'form-group']);
				echo Html::label(Yii::t('main', 'Translate').' ['. strtoupper($lang).']', $field, ['class'=>'control-label']);
				$value = empty($models[$lang]['translation']) ? '' : $models[$lang]['translation'] ;
				echo Html::textarea($field, $value, ['class'=>'form-control', 'style'=>'height: 50px;']);
				echo Html::endTag('div');
			} ?>

		</div>
		<div class="box-footer form-group">
			<?= Html::submitButton(Yii::t('main', 'Submit'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
			<?= Html::a(Yii::t('main', 'Close'), ['index'], ['class'=>'btn btn-link', 'onClick'=>'parent.$.fn.colorbox.close();']); ?>
		</div>
	</div>
	<?php ActiveForm::end(); ?>

</div>
