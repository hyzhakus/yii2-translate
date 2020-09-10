<?php

namespace hyzhak\translate\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidArgumentException;
use yii\base\Model;
use yii\db\ActiveRecord;

use hyzhak\translate\models\Message;
use hyzhak\translate\models\MessageSource;
use hyzhak\translate\components\I18N;

class TranslateBehavior extends Behavior
{

    public $languages;
    public $fields = ['name'];

    private $_fields = [];

    public function init() {
        if(is_null($this->languages)) $this->languages = Yii::$app->i18n->languages;
        return parent::init();
    }

    public function events()
    {
        return [
//            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
//            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    // public function beforeSave($event) {
    //     foreach($this->fields as $field) {
    //         // unset if not change attribute value
    //         if ( $event->sender->getOldAttribute($field) === null || $event->sender->$field !== $event->sender->getOldAttribute($field) ) {
    //             $this->_fields[$field] = $event->sender->getOldAttribute($field);
    //         }
    //     }
    // }

    public function afterSave($event) {
        if(empty($this->fields)) return;
        $post = \Yii::$app->request->post($event->sender->formName());
        foreach($this->fields as $field) {
            // search record in MessageSorce or create new
            if( !($source = $this->getValue($event->sender->formName(), $event->sender->$field)) )
                $source = new MessageSource;
                $source->category = $event->sender->formName();
                $source->message = $event->sender->$field;
                $source->save();
            // search record in Message and create or update
            $messages = [];
            foreach($this->languages as $lang) {
                $messages[$lang] = $post[$lang][$field];
            }
            Message::updateTranslate($source, $messages);
        }
    }

    public function afterDelete($event) {
        foreach($this->fields as $field) {
            $source = MessageSource::findOne(['category'=>$event->sender->formName(), 'message'=>$event->sender->$field]);
            if($source) {
                $source->delete();
                Message::deleteAll(['id'=>$source->id]);
            }
        }
    }

    public function getValue($category, $message) {
        return MessageSource::findOne(['category'=>$category, 'message'=>$message]);
    }

    public function getTranslatedValue($model) {
        $values = [];
        foreach( $this->languages as $lang ) {
            $value[$lang] = '';
        }
        if($model) {
            foreach($model->translate as $item) {
                $value[$item->language] = $item->translation;
            }
        }
        return $value;
    }


}
