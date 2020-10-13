<?php

namespace backend\assets;

use yii\web\AssetBundle;

class ChartsAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
        '/public/js/charts/chart.min.js',
        '/public/js/charts/jquery.sparkline.min.js'
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
