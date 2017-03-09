<?php

namespace larryli\yii\extras\validators;

use yii\ext\helpers\Html;
use yii\helpers\Json;
use yii\validators\ValidationAsset;
use yii\validators\Validator;
use yii\web\JsExpression;

/**
 * 密码强度验证
 */
class NewPasswordValidator extends Validator
{
    /**
     * @var int
     */
    public $min = 8;
    /**
     * @var callable
     */
    public $validateSame;
    /**
     * @var string
     */
    public $tooShort;
    /**
     * @var string
     */
    public $pattern = '/(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z]).*/';
    /**
     * @var string
     */
    public $tooSimple;
    /**
     * @var string
     */
    public $sameAs;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = '{attribute}必须是字符串。';
        }
        if ($this->tooShort === null) {
            $this->tooShort = '{attribute}最少需要 {min, number} 位。';
        }
        if ($this->tooSimple === null) {
            $this->tooSimple = '{attribute}必须包含有大小写字母和数字。';
        }
        if ($this->sameAs === null) {
            $this->sameAs = '{attribute}和旧密码相同。';
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!is_string($value)) {
            $this->addError($model, $attribute, $this->message);
            return;
        }

        if (strlen($value) < $this->min) {
            $this->addError($model, $attribute, $this->tooShort, ['min' => $this->min]);
        }
        if (!preg_match($this->pattern, $value)) {
            $this->addError($model, $attribute, $this->tooSimple);
        }
        if ($this->validateSame !== null && call_user_func($this->validateSame, $value)) {
            $this->addError($model, $attribute, $this->sameAs);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        if (strlen($value) < $this->min) {
            return [$this->tooShort, ['min' => $this->min]];
        }

        if (!preg_match($this->pattern, $value)) {
            return [$this->tooSimple, []];
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => strtr($this->message, [
                '{attribute}' => $label,
            ]),
            'min' => $this->min,
            'tooShort' => strtr($this->tooShort, [
                '{attribute}' => $label,
                '{min, number}' => $this->min,
            ]),
        ];
        $js = 'yii.validation.string(value, messages, ' . Json::htmlEncode($options) . ');';

        $options = [
            'pattern' => new JsExpression(Html::escapeJsRegularExpression($this->pattern)),
            'not' => false,
            'message' => strtr($this->tooSimple, [
                '{attribute}' => $label,
            ]),
        ];
        $js .= 'yii.validation.regularExpression(value, messages, ' . Json::htmlEncode($options) . ');';

        ValidationAsset::register($view);

        return $js;
    }
}
