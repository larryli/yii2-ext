<?php

namespace larryli\yii\extras\helpers;

use yii\base\Model;

/**
 * Class DateTimeHelper
 */
class DateTimeHelper
{
    /**
     * 时间段比较
     *
     * ```php
     * $query->andFilterWhere(DateTimeHelper::betweenDateRange('created_at', $this->created_at);
     * ```
     *
     * @param string $attribute
     * @param string $value
     * @return array
     */
    public static function betweenDateRange($attribute, $value)
    {
        list($start, $end) = static::dateRange($value);
        if ($start !== false && $end !== false) {
            return ['between', $attribute, $start, $end];
        }
        return [$attribute => ''];
    }

    /**
     * @param string $value
     * @return array
     */
    public static function dateRange($value)
    {
        if (!is_null($value) && strpos($value, ' - ') !== false) {
            list($start, $end) = explode(' - ', $value);
            return [strtotime($start . ' midnight'), strtotime($end . ' tomorrow')];
        }
        return [false, false];
    }

    /**
     * @param Model $model
     * @param string $attribute
     * @return array
     */
    public static function presetDateRangePicker($model, $attribute)
    {
        $template = <<< HTML
        <div class="form-control kv-drp-dropdown">
            <span class="range-value">{value}</span>
            <span class="pull-right"><b class="caret"></b></span>
        </div>
        {input}
HTML;
        return [
            'model' => $model,
            'attribute' => $attribute,
            'containerTemplate' => $template,
            'convertFormat' => true,
            'defaultPresetValueOptions' => ['class' => 'hidden'],
            'presetDropdown' => true,
            'pluginEvents' => [
                'apply.daterangepicker' => 'function(ev, picker) {
var val = picker.startDate.format(picker.locale.format) + picker.locale.separator + picker.endDate.format(picker.locale.format);
picker.element[0].children[0].textContent = val;
$(picker.element[0].nextElementSibling).val(val);
}',
                "cancel.daterangepicker" => "function(ev, picker) {
picker.element[0].children[0].textContent = '';
$(picker.element[0].nextElementSibling).val('').trigger('change');
}",
                ],
            'pluginOptions' => [
                'locale' => [
                    'cancelLabel' => '清除',
                    'format' => 'Y-m-d',
                ],
            ],
        ];
    }
}
