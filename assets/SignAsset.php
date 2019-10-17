<?php
namespace razmik\adminka;
use yii\base\Exception;
use yii\web\AssetBundle;
/**
 * AdminLte AssetBundle
 * @since 0.1
 */
class SignAsset extends AssetBundle
{
    public $sourcePath = '@vendor/razmik/adminka/web';

    public $css = [
        'css/vendor.css',
        'css/style.css',
    ];
    public $js = [
        //'js/app.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];
    /**
     * @var string|bool Choose skin color, eg. `'skin-blue'` or set `false` to disable skin loading
     * @see https://almsaeedstudio.com/themes/AdminLTE/documentation/index.html#layout
     */
    public $skin = '_all-skins';
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
}