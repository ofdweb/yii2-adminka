<?php

namespace common\models\gii;

use common\models\Account;
use common\models\AccountProfile;
use common\models\AccountSubject;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%account}}".
 *
 * @property integer $id
 * @property string $status
 * @property string $role
 * @property string $purpose
 * @property string $type
 * @property string $phone
 * @property string $email
 * @property string $fname
 * @property string $mname
 * @property string $lname
 * @property integer $regionCode
 * @property integer $hasPledge
 * @property string $investmentCondition
 * @property string $authKey
 * @property string $passwordHash
 * @property string $accessToken
 * @property string $confirmationToken
 * @property integer $pndAt
 * @property integer $offerAt
 * @property integer $phoneAt
 * @property integer $emailAt
 * @property integer $createdAt
 * @property integer $updatedAt
 *
 * @property GeoRegion $regionCode0
 * @property AccountProfile $accountProfile
 * @property AccountSubject[] $accountSubjects
 * @property AccountSubject[] $accountSubjects0
 */
class User extends ActiveRecord implements IdentityInterface
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status', 'purpose', 'type', 'investmentCondition'], 'string'],
            [['role', 'authKey', 'passwordHash', 'accessToken', 'confirmationToken', 'createdAt', 'updatedAt'], 'required'],
            [['regionCode', 'hasPledge', 'pndAt', 'offerAt', 'phoneAt', 'emailAt', 'createdAt', 'updatedAt'], 'integer'],
            [['role'], 'string', 'max' => 31],
            [['phone'], 'string', 'max' => 16],
            [['email', 'passwordHash', 'accessToken', 'confirmationToken'], 'string', 'max' => 255],
            [['fname', 'mname', 'lname'], 'string', 'max' => 64],
            [['authKey'], 'string', 'max' => 32],
            [['regionCode'], 'exist', 'skipOnError' => true, 'targetClass' => GeoRegion::className(), 'targetAttribute' => ['regionCode' => 'code']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'role' => 'Role',
            'purpose' => 'Purpose',
            'type' => 'Type',
            'phone' => 'Phone',
            'email' => 'Email',
            'fname' => 'Fname',
            'mname' => 'Mname',
            'lname' => 'Lname',
            'regionCode' => 'Region Code',
            'hasPledge' => 'Has Pledge',
            'investmentCondition' => 'Investment Condition',
            'authKey' => 'Auth Key',
            'passwordHash' => 'Password Hash',
            'accessToken' => 'Access Token',
            'confirmationToken' => 'Confirmation Token',
            'pndAt' => 'Pnd At',
            'offerAt' => 'Offer At',
            'phoneAt' => 'Phone At',
            'emailAt' => 'Email At',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getRegionCode0()
    {
        return $this->hasOne(GeoRegion::className(), ['code' => 'regionCode']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountProfile()
    {
        return $this->hasOne(AccountProfile::className(), ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountSubjects()
    {
        return $this->hasMany(AccountSubject::className(), ['accountId' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountSubjects0()
    {
        return $this->hasMany(AccountSubject::className(), ['subjectAccountId' => 'id']);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public static function findIdentity($id)
    {
        return static::find()->byId($id)->isActive()->one();
    }

    /**
     * @param $username
     * @param $password
     * @return Account|null
     */
    public static function findIdentityByBaseAuth($username, $password)
    {
        $account = static::findByEmail($username);
        if ($account === null) {
            return null;
        }

        return $account->validatePassword($password) ? $account : null;
    }

    /**
     * @param string $email
     * @return static
     */
    public static function findByEmail($email)
    {
        return static::find()->byEmail($email)->one();
    }

    /**
     * Validates password
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public static function findIdentityByAccessToken($token, $type = NULL)
    {
        return static::find()->where(['accessToken' => $token])->isActive()->one();
    }

    /**
     * @param string $phone
     * @return static
     */
    public static function findByPhone($phone)
    {
        return static::find()->byPhone($phone)->one();
    }

    /**
     * @param string $token
     * @return static
     */
    public static function findByConfirmationToken($token)
    {
        return static::find()->andWhere(['confirmationToken' => $token])->one();
    }
}