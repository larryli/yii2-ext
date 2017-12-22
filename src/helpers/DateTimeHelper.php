<?php

namespace extras\helpers;

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
     * @param $model
     * @param $attribute
     * @return array
     */
    static public function presetDateRangePicker($model, $attribute)
    {
        return [
            'model' => $model,
            'attribute' => $attribute,
            'convertFormat' => true,
            'presetDropdown' => true,
            'pluginEvents' => [
                "cancel.daterangepicker" => "function(ev, picker) {
picker.element[0].children[1].textContent = '';
$(picker.element[0].nextElementSibling).val('').trigger('change');
}",
                'apply.daterangepicker' => 'function(ev, picker) { 
var val = picker.startDate.format(picker.locale.format) + picker.locale.separator +
picker.endDate.format(picker.locale.format);

picker.element[0].children[1].textContent = val;
$(picker.element[0].nextElementSibling).val(val);
}',
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
