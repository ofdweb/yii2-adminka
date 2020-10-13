<?php

namespace backend\assets;

use Yii;
use yii\web\AssetBundle;
use yii\web\JsExpression;
use yii\web\View;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $basePath = '@webroot';
    /**
     * @var string
     */
    public $baseUrl = '@web';
    /**
     * @var array
     */
    public $css = [
        'https://use.fontawesome.com/releases/v5.3.1/css/all.css',
        'app-assets/css/app.css?v=0.02',
        'app-assets/css/colors.css',
        'app-assets/css/core/menu/menu-types/vertical-menu.css',
        'app-assets/css/core/menu/menu-types/vertical-overlay-menu.css',
    ];
    /**
     * @var array
     */
    public $js = [
    ];
    /**
     * @var array
     */
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    /**
     * @var array
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
    /**
     * @param View $view
     */
    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);
        $dadataUrl = Yii::$app->urlManager->hostName . '/v1/dadata/';
        $dadataKey = Yii::$app->dadata->apiKey;
        $view->registerJs(
            new JsExpression(
                "
                const daDataUrl = '$dadataUrl';
                const daDataKey = '$dadataKey';
                "
            ), View::POS_HEAD
        );
    }
}
