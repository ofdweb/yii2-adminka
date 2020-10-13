<?php
/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\widgets\ActiveForm;

$this->title = 'Авторизация';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-md-4 offset-md-4 col-xs-10 offset-xs-1  box-shadow-2 p-0">
    <div class="card border-grey border-lighten-3 m-0">
        <div class="card-header no-border">
            <div class="card-title text-xs-center">
                <div class="p-1"><img src="/public/image/logo-h.png" alt="branding logo"></div>
            </div>
            <h6 class="card-subtitle line-on-side text-muted text-xs-center font-small-3 pt-2"><span><?= $this->title; ?></span></h6>
        </div>
        <div class="card-body">
            <div class="card-block">
                <?php $form = ActiveForm::begin(['id' => 'login-form', 'class' => 'form-horizontal form-simple']); ?>
                <?=
                $form->field($model, 'email', [
                    'template' => "{input}\n<div class=\"form-control-position\">\n<i class=\"ft-user\"></i>\n</div>\n{error}",
                    'options' => [
                        'class' => 'form-group position-relative has-icon-left mb-0',
                        'tag' => 'fieldset',
                    ],
                    'inputOptions' => [
                        'class' => 'form-control form-control-lg input-lg',
                    ],
                ])
                ->textInput([
                    'autofocus' => true,
                    'placeholder' => $model->getAttributeLabel('email'),
                ]);
                ?>
                <?=
                $form->field($model, 'password', [
                    'template' => "{input}\n<div class=\"form-control-position\">\n<i class=\"fa fa-key\"></i>\n</div>\n{error}",
                    'options' => [
                        'class' => 'form-group position-relative has-icon-left',
                        'tag' => 'fieldset',
                    ],
                    'inputOptions' => [
                        'class' => 'form-control form-control-lg input-lg',
                    ],
                ])
                ->passwordInput([
                    'placeholder' => $model->getAttributeLabel('password'),
                ]);
                ?>
                <fieldset class="form-group row">
                    <div class="col-md-6 col-xs-12 text-xs-center text-md-left">
                        <?=
                        $form->field($model, 'rememberMe', [
                            'template' => "{input}\n{label}\n{error}",
                            'options' => [
                                'class' => '',
                                'tag' => 'fieldset',
                            ],
                        ])->checkbox([
                            'class' => 'chk-remember',
                        ], false);
                        ?>
                    </div>
                </fieldset>
                <button type="submit" class="btn btn-primary btn-lg btn-block"><i class="ft-unlock"></i> Войти</button>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
\backend\assets\ICheckAsset::register($this);
$js = <<<JS
if ($('.chk-remember').length){
    $('.chk-remember').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
    });
}
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
