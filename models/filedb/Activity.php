<?php
namespace common\models\filedb;

use yii\helpers\ArrayHelper;
use yii2tech\filedb\ActiveRecord;

/**
 * @author Ilia
 *
 * @property integer $id ID записи
 * @property string $name Название
 */
class Activity extends ActiveRecord
{
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
        return 'Activity';
    }

    public static function keyList()
    {
        return ArrayHelper::getColumn(self::itemList(), 'id');
    }

    public static function itemList()
    {
        return self::find()->all();
    }
}