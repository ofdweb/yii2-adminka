<?php

namespace common\models\filedb;

use yii\helpers\ArrayHelper;
use yii2tech\filedb\ActiveRecord;

/**
 * Class Status
 * @property string $name
 */
class Status extends ActiveRecord
{
    const WAITING = 'WAITING';

    const ACCEPTED = 'ACCEPTED';

    const REJECTED = 'REJECTED';

    const REVOKED = 'REVOKED';

    const DELETED = 'DELETED';

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
        return 'Status';
    }

    public static function keyList()
    {
        return ArrayHelper::getColumn(self::find()->all(), 'id');
    }
}