<?php

use yii\helpers\Html;

razmik\adminka\AdminkaAsset::register($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body class="hold-transition skin-blue sidebar-mini">
<?php $this->beginBody() ?>
<div class="wrapper">
    <h1>Sign Out</h1>
    <form action="sign-out>
        <input type="text" name="SignOut">
        <button type="submit"></button>
    </form>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
