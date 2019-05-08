<?php

namespace LarryLi\Yii\Extras\Behaviors;

use Yii;
use yii\base\InvalidCallException;
use yii\base\Request;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * 自动记录 User-Agent
 */
class UserAgentBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive User-Agent value on create.
     * Set to false if you do not want record it
     */
    public $createdViaAttribute = 'created_via';
    /**
     * @var string the attribute that will receive User-Agent value on update.
     * Set to false if you do not want record it
     */
    public $updatedViaAttribute = 'updated_via';
    /**
     * @var callable|string
     * This can be either an anonymous function that returns the User-Agent value or a string.
     * If not set, it will use the value of `\Yii::$app->request->userAgent` to set the attributes.
     * NOTE! Null is returned if the user IP address cannot be detected.
     */
    public $value;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->attributes)) {
            $this->attributes = [
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdViaAttribute, $this->updatedViaAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedViaAttribute,
            ];
        }
    }

    /**
     * @inheritdoc
     */
    protected function getValue($event)
    {
        if (is_string($this->value)) {
            return $this->value;
        }
        return $this->value !== null ? call_user_func($this->value, $event) :
            is_a(Yii::$app->request, Request::class) ? Yii::$app->request->userAgent : 'Console';
    }

    /**
     * Sets an User-Agent to the attribute.
     *
     * ```php
     * $model->setUserAgent('updated_via');
     * ```
     * @param string $attribute the name of the attribute to update.
     */
    public function setUserAgent($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the user-agent is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array)$attribute, $this->getValue(null)));
    }
}
