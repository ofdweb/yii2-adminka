<?php

namespace frontend\controllers;

use common\components\template\entity\InstanceFileManagedTemplateEntity;
use common\components\template\filters\ConvertToPdfFilterTemplate;
use common\components\template\TemplateEngineService;
use common\helpers\BankDayHelper;
use common\helpers\Ean13Helper;
use common\models\Account;
use common\models\AuthHandler;
use common\models\Faq;
use common\models\LoginForm;
use common\models\Offer;
use common\models\OfferCounter;
use common\modules\esign\components\EasingEvent;
use common\modules\esign\components\EasingQualifiedComponent;
use common\modules\esign\components\EasingService;
use common\modules\esign\components\EasingSimpleComponent;
use common\modules\esign\components\EsignProtocolEngine;
use common\modules\esign\components\EsignSmsEngine;
use common\modules\esign\components\templates\filters\QualifiedSignStampFilterTemplate;
use common\modules\esign\components\templates\filters\SimpleSignStampFilterTemplate;
use common\modules\esign\models\AccountEsignSertificate;
use common\modules\esign\models\AccountSignedDocument;
use common\modules\nominal\components\payments\Ean13PaymentHeader;
use common\modules\nominal\components\templates\OfferRepaymentScheduleTemplateEntity;
use common\modules\nominal\components\transfers\transactions\TransferHeaderTransaction;
use common\modules\nominal\helpers\NominalSocketSender;
use common\modules\nominal\jobs\OfferOutputAmountJob;
use common\modules\nominal\models\NominalPaymentHeader;
use common\modules\payment\models\PaymentOrder;
use common\modules\quiz\forms\QuizTestForm;
use common\modules\tochka\components\commands\decorators\misc\AccountBeneficiaryClientDecorator;
use common\modules\transaction\components\logs\TransferAmountTransactionLog;
use common\modules\transaction\components\TransactionActionService;
use common\modules\transaction\models\TransactionActionLog;
use frontend\forms\QuestionForm;
use frontend\models\PasswordResetRequestForm;
use frontend\modules\file\models\FileManaged;
use frontend\modules\file\models\FileStorageUploadForm;
use frontend\modules\file\models\FileUsage;
use frontend\modules\master\helpers\MasterHelper;
use frontend\modules\offer\components\nominal\CreateCommissionPaymentComponent;
use frontend\modules\offer\components\OfferCloseComponent;
use frontend\modules\offer\components\ReceiptInvestorTemplateEngine;
use frontend\modules\offer\components\ReceiptPaymentOrderTemplateEngine;
use frontend\modules\offer\components\templates\BorrowerOfferDeclineTemplateEntity;
use frontend\modules\offer\components\templates\BorrowerOfferRequestTemplateEntity;
use frontend\modules\offer\components\templates\InvestorOfferCounterEasingTemplateEntity;
use frontend\modules\offer\components\templates\OfferBaseConditionsTemplateEntity;
use frontend\modules\offer\components\transactions\OfferCounterCreateTransaction;
use frontend\modules\offer\components\transactions\OfferDeclineTransaction;
use Yii;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\UnprocessableEntityHttpException;

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
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            [
                'class' => 'frontend\behaviors\HtmlCompressorFilter',
            ],
            [
                'class' => 'yii\filters\HttpCache',
                'only' => ['index'],
                'lastModified' => function ($action, $params) {
                    return Offer::find()->displayOnMainPage()->orderBy(['publishedAt' => SORT_DESC])->max('updatedAt');
                },
                'etagSeed' => function ($action, $params) {
                    $offer = Offer::find()->displayOnMainPage()->orderBy(['updatedAt' => SORT_DESC])->one();
                    return serialize([$offer->id, $offer->accountId]);
                },
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
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
            'auth' => [
                'class' => 'frontend\controllers\AuthAction',
                'successCallback' => [$this, 'onAuthSuccess'],
                'cancelCallback' => [$this, 'onAuthCancel'],
            ],
        ];
    }

    public function eventLinkSignedFile(EasingEvent $event)
    {
        $fileManaged = $event->signedFile;
        $offerCounter = OfferCounter::findOne(405);
        $reflection = (new \ReflectionClass(InvestorOfferCounterEasingTemplateEntity::class));

        $fileUsage = new FileUsage([
            'fid' => $fileManaged->id,
            'entity' => $offerCounter->formName(),
            'entity_id' => $offerCounter->id,
            'type' => $reflection->getShortName()
        ]);

        if ($fileUsage->save() === false) {
            throw new InvalidValueException('Не удалось сформировать документы.');
        }
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->redirect(
                MasterHelper::stageUrl($model->getUser()->status)
            );
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Ссылка для восстановление пароля отправлена на Ваш e-mail адрес.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Не удалось выслать Вам письмо с иструкцией для восстановления пароля.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new PasswordResetForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'Новый пароль установлен.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * @return \yii\web\Response
     */
    public function actionTg()
    {
        return $this->redirect('tg://resolve?domain=moneyfriends_ru');
    }

    public function actionAnalytics()
    {
        return $this->render('analytics');
    }

    public function onAuthSuccess($client)
    {
        return (new AuthHandler($client))->handle();
    }

    public function onAuthCancel($client)
    {
        return (new AuthHandler($client))->cancel();
    }

    public function actionPartner($referral = null)
    {
        return $this->render('partner', ['referral' => $referral]);
    }
}
