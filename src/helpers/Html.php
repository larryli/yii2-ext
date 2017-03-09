<?php

namespace larryli\yii\extras\helpers;

use Yii;
use yii\bootstrap\Html as BaseHtml;
use yii\helpers\ArrayHelper;

/**
 * Html 助手
 */
class Html extends BaseHtml
{
    /**
     * Font Awesome icon
     *
     * ```
     * Html::fa('user'); // Basic Icons
     * Html::fa('user fa-lg'); // BLarger Icons, eg. `fa-2x` - `fa-5x`
     * Html::fa('user fa-fw'); // Fixed Width Icons
     * ```
     * @see http://fontawesome.io/examples/
     * @param $name
     * @param array $options
     * @return string
     */
    public static function fa($name, $options = [])
    {
        $tag = ArrayHelper::remove($options, 'tag', 'i');
        $classPrefix = ArrayHelper::remove($options, 'prefix', 'fa fa-');
        static::addCssClass($options, $classPrefix . $name);
        return static::tag($tag, '', $options);
    }

    /**
     * Font Awesome Stacked Icons
     *
     * ```
     * Html::faStack('square fa-stack-2x', 'terminal fa-stack-1x fa-inverse');
     * ```
     * @see http://fontawesome.io/examples/
     * @param $first
     * @param $second
     * @param array $options
     * @return string
     */
    public static function faStack($first, $second, $options = [])
    {
        $firstOptions = ArrayHelper::remove($options, 'firstOptions', []);
        $secondOptions = ArrayHelper::remove($options, 'secondOptions', []);
        $content = static::fa($first, $firstOptions) . static::fa($second, $secondOptions);
        $tag = ArrayHelper::remove($options, 'tag', 'span');
        static::addCssClass($options, 'fa-stack');
        return static::tag($tag, $content, $options);
    }

    /**
     * 生成带图标的链接
     *
     * @param string $text 链接文本
     * @param null $url 链接
     * @param array $options 链接选项，`icon` 为图标名称
     * @return string 链接完整内容
     */
    public static function a($text, $url = null, $options = [])
    {
        if (isset($options['icon'])) {
            $text = self::icon($options['icon']) . $text;
            unset($options['icon']);
        } elseif (isset($options['fa'])) {
            $text = self::fa($options['fa']) . $text;
            unset($options['fa']);
        }
        return parent::a($text, $url, $options);
    }

    /**
     * 返回分隔的页面标题
     *
     * @param array $titles
     * @param string $sep
     * @return string
     */
    public static function getPageTitle(array $titles, $sep = ' - ')
    {
        $titles[] = Yii::$app->name;
        return implode($sep, $titles);
    }

    /**
     * 时间段
     *
     * @return array
     */
    public static function getRelativeDurations()
    {
        return [
            strtotime('today') => '今天',
            strtotime('midnight monday this week') => '本周内',
            strtotime('midnight first day of this month') => '本月内',
        ];
    }
}
