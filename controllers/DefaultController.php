<?php
namespace hyzhak\translate\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
//use yii\web\Response;
use hyzhak\translate\models\Message;
use hyzhak\translate\models\MessageSource;
use hyzhak\translate\models\MessageSearch;
use hyzhak\translate\models\MessageScan;

class DefaultController extends Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'update'  => ['post'],
                    'delete'  => ['post'],
                ],
            ],
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex() {
        $searchModel = new MessageSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get());
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionCreate() {
        $model = new MessageSource;
        if ( $model->load(Yii::$app->request->post()) ) {
            $transaction = Yii::$app->db->beginTransaction();
            if($model->save()) {
                $langs = Yii::$app->request->post('translation');
                foreach($langs as $lang => $translation) {
                    $message = new Message;
                    $message->id = $model->id;
                    $message->language = $lang;
                    $message->translation = $translation;
                    if(!$message->save()) {
                        $model->addErrors($message->getErrors());
                        break;
                    }
                }
                if(!$message->hasErrors()) {
                    $transaction->commit();
                    Yii::$app->session->addFlash('success', Yii::t('main', 'Phrase successfully added!'));
                    return $this->redirect(['index']);
                }
            }
            $transaction->rollBack();
        }
        return $this->render('create', ['model'=>$model]);
    }

    public function actionMissing() {
        $this->layout = '@app/views/layouts/popup';
        $error = [];

        $attributes = [];
        $attributes['category'] = Yii::$app->request->get('category');
        $attributes['source'] = Yii::$app->request->get('source');

        $models = Message::find()->where($attributes)->indexBy('lang')->asArray()->all();
        $model = new Message;
        $model->attributes = $attributes;
        if($trans = Yii::$app->request->post('translation')) {
            foreach (Yii::$app->i18n->languages  as $lang ) {
                if(!isset($trans[$lang])) continue;
                if(isset($models[$lang]['translation'])) {
                    if($trans[$lang] == $models[$lang]['translation']) continue;
                    $model->setIsNewRecord(false);
                    $model->attributes = $models[$lang]['translation'];
                } else {
                    $model->setIsNewRecord(true);
                    $model->id = null;
                }
                $model->lang = $lang;
                $model->translation = $trans[$lang];
                if(!$model->save()) {
                    $error[$lang] = $model->getFirstErrors();
                }
            }
            if(empty($error)) {
                return $this->render('@app/views/layouts/popup_close');
            }
        }
        return $this->render('missing', ['model'=>$model, 'models'=>$models, 'attributes'=>$attributes, 'error'=>$error]);
    }

    public function actionUpdate() {
        $pk = Yii::$app->request->post('pk');
        $model = $this->setModel($pk);
        if (!$model->save()) {
            $erorText = implode('\n', $model->getFirstErrors());
            throw new BadRequestHttpException($erorText);
        }
    }

    public function actionDelete() {
        $id = (int)Yii::$app->request->post('id');
        $model = (new MessageSource)->findOne($id);
        if($model->delete()) {
            Yii::$app->session->addFlash('success', Yii::t('yii', 'Phrase deleted.'));
        } else {
            Yii::$app->session->addFlash('error', Yii::t('yii', 'Cannot delete {message}.', ['message'=>$model->message]));
        }
        return $this->redirect(['index']);
    }

    public function actionScan() {
        $model = new MessageScan;
        $result = $model->extract();
        if(empty($result)) {
            Yii::$app->session->addFlash('success', Yii::t('yii', 'No new phrases.'));
        } else {
            Yii::$app->session->addFlash('success', Yii::t('yii', 'Adden new phrases - {count}.', ['count'=>$result]));
        }
        return $this->redirect(['index']);
    }

    protected function setModel($attributes) {
        $modelSource = MessageSource::findOne((int)$attributes['id']);
        if (empty($modelSource)) {
            throw new BadRequestHttpException(Yii::t('main', 'Unknown message'));
        }
        $model = Message::find()->where([
            'id' => $modelSource->id,
            'language' => $attributes['language'],
        ])->one();
        if (empty($model)) $model = new Message;
        $model->id = $modelSource->id;
        $model->language = $attributes['language'];
        $model->translation = Yii::$app->request->post('value');
        return $model;
    }

    public function redirect($url, $statusCode = 302) {
        $url = Yii::$app->request->get('backUrl', $url);
        return Yii::$app->getResponse()->redirect(\yii\helpers\Url::to($url), $statusCode);
    }

}
