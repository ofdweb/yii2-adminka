<?php

namespace backend\controllers;

use backend\models\Admin;
use backend\models\LoginForm;
use backend\models\PasswordForm;
use backend\models\StatisticModel;
use common\components\selectel\RegenerateSiteCacheJob;
use common\models\PhoneInfo;
use DateTime;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\widgets\DetailView;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'password', 'phone-info', 'site-cache'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                    'password' => ['post'],
                ],
            ],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }
    
    /**
     * Инфо по номеру
     * @param $phone
     * @return string
     * @throws \Exception
     */
    public function actionPhoneInfo($phone)
    {
        if (Yii::$app->request->isAjax === false) {
            return $this->goBack();
        }
        
        if ((int)$phone >= 10) {
            /** @var PhoneInfo $model */
            $model = Yii::$app->sms->info($phone);
            if ($model === false) {
                return Yii::t('app', 'Не удалось получить данные.');
            }
            
            return DetailView::widget(
                [
                    'model' => $model,
                    'attributes' => [
                        //'country',
                        'operator',
                        'region',
                        [
                            'attribute' => 'tz',
                            'label' => Yii::t('app', 'Время'),
                            'value' => function ($date) {
                                date_default_timezone_set("UTC");
                                return (new DateTime())->modify("+{$date->tz} hours")->format('H:i');
                            },
                        ],
                    ],
                ]
            );
        }
        
        return Yii::t('app', 'Данные не найдены.');
    }
    
    /**
     * Главная страница
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render(
            'index',
            [
                'stat' => new StatisticModel(),
            ]
        );
    }
    
    /**
     * Действие авторизации
     *
     * @return string
     * @throws InvalidConfigException
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->layout = 'clear';
        
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
        
        return $this->render(
            'login',
            [
                'model' => $model,
            ]
        );
    }
    
    /**
     * Смена пароля администратора
     *
     * @return array
     * @throws BadRequestHttpException
     * @throws Exception
     */
    public function actionPassword()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $form = new PasswordForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            /** @var Admin $user */
            $user = Yii::$app->user->identity;
            $user->setPassword($form->password);
            if ($user->save()) {
                return [
                    'status' => true,
                    'message' => 'Пароль успешно изменен.',
                ];
            }
        }
        $message = 'Не удалось сменить пароль.';
        if ($errors = $form->getFirstErrors()) {
            $message = array_shift($errors);
        }
        throw new BadRequestHttpException($message);
    }
    
    /**
     * Действие выхода
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }
    
    /**
     * @param $action
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionSiteCache($action)
    {
        switch ($action){
            case 'restart' : {
                RegenerateSiteCacheJob::unlock();
                $job = new RegenerateSiteCacheJob(['site' => Yii::$app->params['hostName']]);
                Yii::$app->queue->push($job);
            }break;
            case 'start' :{
                $job = new RegenerateSiteCacheJob(['site' => Yii::$app->params['hostName']]);
                Yii::$app->queue->push($job);
            }break;
            default: {
                throw new NotFoundHttpException();
            }
        }
        return $this->redirect(['index']);
    }
}
