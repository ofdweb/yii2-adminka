<?php

namespace backend\models;

use common\models\Account;
use common\Rbac;
use Yii;
use yii\base\Model;

/**
 * Форма входа администратора
 */
class LoginForm extends Model
{

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    /**
     * @var bool
     */
    public $rememberMe = true;

    /**
     * @var Account
     */
    private $_account;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'password'], 'required'],
            [['email', 'password'], 'string'],
            ['email', 'email'],
            ['password', 'validatePassword'],
            ['email', 'validateEmail'],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'password' => 'Пароль',
            'rememberMe' => 'Запомнить меня',
        ];
    }

    /**
     * Validates the email.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateEmail($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $account = $this->getAccount();

            if ($account !== null) {
                if ($account->status === Account::STATUS_BLOCKED) {
                    $this->addError($attribute, 'Ваша учетная запись была заблокирована. Для выяснения причин блокировки Вы можете обратиться в службу поддержки.');
                }
                $permissions = Yii::$app->authManager->getPermissionsByUser($account->id);

                if (!isset($permissions[Rbac::TASK_SHOW_BACKEND])) {
                    $this->addError($attribute, 'Недостаточно прав для авторизации.');
                }
            }
        }
    }

    /**
     * Validates the password.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $account = $this->getAccount();

            if ($account === null || !$account->validatePassword($this->password)) {
                $this->addError($attribute, 'Некорректный логин или пароль.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getAccount(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }

    /**
     * Поиск пользователя по e-mail
     * @return Account|null
     */
    protected function getAccount()
    {
        if ($this->_account === null) {
            $this->_account = Account::find()
            ->andWhere(['email' => $this->email])
            ->andWhere(['!=', 'status', Account::STATUS_DELETED])
            ->one();
        }
        return $this->_account;
    }

}