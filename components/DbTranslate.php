<?php
namespace hyzhak\translate\components;

use Yii;
//use hyzhak\translate\components\I18N;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class DbTranslate extends \yii\i18n\DbMessageSource {

    /**
     * Initializes the DbMessageSource component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * Configured [[cache]] component would also be initialized.
     * @throws InvalidConfigException if [[db]] is invalid or [[cache]] is invalid.
     */
    public function init() {
        parent::init();
    }

}
