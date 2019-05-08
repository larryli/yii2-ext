<?php

namespace larryli\yii\extras\behaviors;

use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * ActiveRecord 上传文件行为
 */
class UploadBehavior extends BaseUploadBehavior
{
    /**
     * @var ActiveRecord the owner of this behavior
     */
    public $owner;
    /**
     * @var UploadedFile[] 新上传的文件
     */
    protected $files = [];
    /**
     * @var string[] 旧有文件数据
     */
    protected $oldFiles = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * 文件验证失败时，还原旧数据
     */
    public function afterValidate()
    {
        foreach ($this->attributes as $attribute) {
            $file = $this->owner->$attribute;
            if (!$this->owner->isNewRecord && (empty($file) || $this->owner->hasErrors($attribute))) {
                // 非上传文件数据或发生验证错误，恢复旧数据
                $this->owner->$attribute = ArrayHelper::getValue($this->owner->oldAttributes, $attribute, '');
            }
        }
    }

    /**
     * 处理文件替换，保存旧文件到 oldAttributes
     * @throws Exception
     */
    public function beforeSave()
    {
        foreach ($this->attributes as $i => $attribute) {
            $file = $this->owner->$attribute;
            if ($file instanceof UploadedFile) {
                $this->files[$i] = $file;
                $this->owner->$attribute = $this->generatePath($file->extension);
                if (!$this->owner->isNewRecord) {
                    // 保留旧文件数据到保存操作时删除
                    $this->oldFiles[$i] = ArrayHelper::getValue($this->owner->oldAttributes, $attribute, null);
                }
            } elseif (!$this->owner->isNewRecord && empty($file)) {
                // 自动删除旧数据
                $this->oldFiles[$i] = ArrayHelper::getValue($this->owner->oldAttributes, $attribute, null);
            }
        }
    }

    /**
     * 保存上传的文件，同时删除旧有的文件。
     *
     * @throws Exception
     */
    public function afterSave()
    {
        foreach ($this->attributes as $i => $attribute) {
            if (isset($this->oldFiles[$i])) {
                $this->delete($this->oldFiles[$i]);
            }
            if (isset($this->files[$i])) {
                $this->save($this->files[$i], $this->owner->$attribute);
            }
        }
    }

    /**
     * 删除上传的文件。
     */
    public function afterDelete()
    {
        foreach ($this->attributes as $attribute) {
            $this->deleteFile($attribute);
        }
    }
}
