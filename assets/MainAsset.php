<?php

namespace backend\assets;

use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\JsExpression;

/**
 * Class MainAsset
 * @package backend\assets
 */
class MainAsset extends AssetBundle
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
    ];
    /**
     * @var array
     */
    public $js = [
        'app-assets/js/core/app-menu.js',
        'app-assets/js/core/app.js',
        'public/js/CKEditorPlugins.js',
    ];
    /**
     * @var array
     */
    public $depends = [
        'backend\assets\AppAsset',
    ];
    
    /**
     * @var array
     */
    public $publishOptions = [
        'forceCopy' => YII_DEBUG,
    ];
}
