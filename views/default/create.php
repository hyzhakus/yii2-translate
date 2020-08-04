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
			<?= $form->field($model, 'category')->dropDownList(
				$model::getCategories(),
				['prompt'=> Yii::t('main', 'Please select')]
			);
			?>
			<?= $form->field($model, 'message')->textArea() ?>

			<? foreach(Yii::$app->i18n->languages as $lang ) {
				$field = 'translation['.$lang.']';
				echo Html::beginTag('div', ['class'=>'form-group']);
				echo Html::label(Yii::t('main', 'Translate').' ['. strtoupper($lang).']', $field, ['class'=>'control-label']);
				echo Html::textarea($field, '', ['class'=>'form-control']);
				echo Html::endTag('div');
			} ?>

		</div>
	</div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('main', 'Create') : Yii::t('main', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('main', 'Back'), ['index'], ['class'=>'btn btn-link']); ?>
    </div>

    <?php ActiveForm::end(); ?>


</div>
