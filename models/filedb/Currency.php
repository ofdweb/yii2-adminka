<?php

namespace common\models\filedb;

use yii\helpers\ArrayHelper;
use yii2tech\filedb\ActiveRecord;

class Currency extends ActiveRecord
{
    const RUB = 'RUB';

    /**
     * @return \yii2tech\filedb\Connection
     */
    public static function getDb()
    {
        $filedb = parent::getDb();
        $filedb->path = '@common/data/static';

        return $filedb;
    }

    public static function fileName()
    {
        return 'Currency';
    }

    public static function keyList()
    {
        return ArrayHelper::getColumn(self::find()->all(), 'id');
    }
}