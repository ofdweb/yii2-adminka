<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\validators\PhoneFilterValidator;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;
    public $url;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'email'],
            [['url', 'email'], 'required'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Телефон'
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     * @throws \yii\base\InvalidConfigException
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::find()->byEmail($this->email)->one();

        if (!$user) {
            return false;
        }
        
        if (!$user->token) {
            $user->generateToken();
            if (!$user->save()) {
                return false;
            }
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user, 'url' => $this->url]
            )
            ->setTo($user->email)
            ->setSubject('Восстановление пароля')
            ->send();
    }
}
