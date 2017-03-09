<?php

namespace larryli\yii\extras\admin\assets;

use yii\base\Exception;
use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class AdminLTEAsset
 */
class AdminLTEAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@bower/admin-lte/dist';
    /**
     * @var array
     */
    public $css = ['css/AdminLTE.min.css'];
    /**
     * @var array
     */
    public $js = ['js/app.min.js'];
    /**
     * @var array
     */
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
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
        // Append skin color file if specified
        if ($this->skin) {
            if (('_all-skins' !== $this->skin) && (strpos($this->skin, 'skin-') !== 0)) {
                throw new Exception('Invalid skin specified');
            }
            $this->css[] = sprintf('css/skins/%s.min.css', $this->skin);
        }
        // 复制文件后移除 Google fonts
        $this->publishOptions['afterCopy'] = function ($from, $to) {
            if (is_file($to) && substr_compare($to, 'AdminLTE.min.css', -16) == 0) {
                $content = @file_get_contents($to);
                if ($content !== false) {
                    $content = str_replace('@import url(', '/* @import url(', $content);
                    @file_put_contents($to, $content);
                }
            }
        };
        parent::init();
    }
}
