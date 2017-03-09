<?php

namespace extras\behaviors;

use Yii;
use yii\base\InvalidCallException;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

/**
 * 自动记录 IP
 */
class IpBehavior extends AttributeBehavior
{
    /**
     * @var string the attribute that will receive IP value on create.
     * Set to false if you do not want record it
     */
    public $createdFromAttribute = 'created_from';
    /**
     * @var string the attribute that will receive IP value on update.
     * Set to false if you do not want record it
     */
    public $updatedFromAttribute = 'updated_from';
    /**
     * @var callable|string
     * This can be either an anonymous function that returns the IP value or a string.
     * If not set, it will use the value of `\Yii::$app->request->userIp` to set the attributes.
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
                BaseActiveRecord::EVENT_BEFORE_INSERT => [$this->createdFromAttribute, $this->updatedFromAttribute],
                BaseActiveRecord::EVENT_BEFORE_UPDATE => $this->updatedFromAttribute,
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
        } else {
            return $this->value !== null ? call_user_func($this->value, $event) : Yii::$app->request->userIP;
        }
    }

    /**
     * Sets an IP address to the attribute.
     *
     * ```php
     * $model->setIp('updated_from');
     * ```
     * @param string $attribute the name of the attribute to update.
     */
    public function setIp($attribute)
    {
        /* @var $owner BaseActiveRecord */
        $owner = $this->owner;
        if ($owner->getIsNewRecord()) {
            throw new InvalidCallException('Updating the ip is not possible on a new record.');
        }
        $owner->updateAttributes(array_fill_keys((array)$attribute, $this->getValue(null)));
    }
}
