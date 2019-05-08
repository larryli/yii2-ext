<?php

namespace LarryLi\Yii\Extras\Assets;

use yii\web\AssetBundle;

/**
 * Wechat UI asset bundle.
 */
class WeUIJSAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/weui.js/dist';
    /**
     * @var array
     */
    public $js = ['weui.js'];
    /**
     * @var array
     */
    public $depends = [
        WeUIAsset::class,
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->publishOptions['except'] = ['example'];
        parent::init();
    }
}
