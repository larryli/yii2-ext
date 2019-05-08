<?php

namespace LarryLi\Yii\Extras\Behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * 基础上传行为
 *
 * 此行为不会自动保存上传的数据，需要手工 saveFile() 保存文件数据。
 */
class BaseUploadBehavior extends Behavior
{
    /**
     * @var Model
     */
    public $owner;
    /**
     * @var string 保存上传文件名前缀，可以使用时间格式化 date() 字符串，斜杠 / 表示目录分割
     */
    public $prefix = 'Y/m/d/His-';
    /**
     * @var int 保存上传文件名随机字符串长度
     */
    public $random = 8;
    /**
     * @var string 保存上传文件目录，可以使用 Yii 别名
     */
    public $basePath = '@images/';
    /**
     * @var string Web 访问路径
     */
    public $baseUrl = '/images/';
    /**
     * @var string 默认 Web 访问地址
     */
    public $defaultUrl = '';
    /**
     * @var array 要处理属性
     */
    public $attributes = [];
    /**
     * @var bool 上传图片文件使用 GD 处理后保存
     */
    public $gd = false;
    /**
     * @var int GD 保存 JPG 文件质量
     */
    public $quality = 75;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            Model::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    /**
     * 处理上传文件数据。
     *
     * 可以手工在控制器中预处理数据：
     * ```
     * $model->uploaded = UploadedFile::getInstanceByName('uploaded');
     * ```
     */
    public function beforeValidate()
    {
        foreach ($this->attributes as $attribute) {
            $file = $this->owner->$attribute;
            if ($file instanceof UploadedFile) {
                break;
            }
            $file = UploadedFile::getInstance($this->owner, $attribute);
            if (empty($file)) {
                $file = UploadedFile::getInstanceByName($attribute);
            }
            if ($file instanceof UploadedFile) {
                $this->owner->$attribute = $file;
            }
        }
    }

    /**
     * @param string $attribute
     * @return string|false
     */
    public function getUploadedFilename($attribute)
    {
        $path = $this->owner->$attribute;
        if (is_string($path)) {
            return empty($path) ? false : $this->getFilename($path);
        }
        return false;
    }

    /**
     * @param string $attribute
     * @param string $defaultUrl
     * @return string
     */
    public function getUploadedUrl($attribute, $defaultUrl = '')
    {
        $path = $this->owner->$attribute;
        $defaultUrl = empty($defaultUrl) ? $this->defaultUrl : $defaultUrl;
        if (is_string($path)) {
            return empty($path) ? $defaultUrl : $this->baseUrl . $path;
        }
        return $defaultUrl;
    }

    /**
     * @param UploadedFile $attribute
     * @return bool
     * @throws Exception
     */
    public function saveFile($attribute)
    {
        $file = $this->owner->$attribute;
        if ($file instanceof UploadedFile) {
            $path = $this->generatePath($file->extension);
            $this->save($file, $path);
            $this->owner->$attribute = $path;
            return true;
        }
        return false;
    }

    /**
     * @param string $attribute
     */
    public function deleteFile($attribute)
    {
        $path = $this->owner->$attribute;
        if (is_string($path)) {
            $this->delete($path);
        }
    }

    /**
     * @param string $extension
     * @return string
     * @throws Exception
     */
    protected function generatePath($extension = 'jpg')
    {
        return date($this->prefix) . Yii::$app->security->generateRandomString($this->random) . '.' . $extension;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function getFilename($path)
    {
        return Yii::getAlias($this->basePath) . $path;
    }

    /**
     * @param string $path
     */
    protected function delete($path)
    {
        if (!empty($path)) {
            $filename = $this->getFilename($path);
            if (file_exists($filename) && !is_dir($filename)) {
                unlink($filename);
            }
        }
    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @throws Exception
     */
    protected function save(UploadedFile $file, $path)
    {
        $filename = $this->getFilename($path);
        $dir = dirname($filename);
        if (!FileHelper::createDirectory($dir)) {
            throw new Exception("Create directory {$dir} error. {$filename} cannot save.");
        }
        if ($this->gd) {
            $this->saveByGD($file->tempName, $filename);
        } elseif (!$file->saveAs($filename)) {
            throw new Exception("Save {$filename} error.");
        }
    }

    /**
     * @param $src
     * @param $dst
     * @throws Exception
     */
    protected function saveByGD($src, $dst)
    {
        $is = getimagesize($src);
        switch (@$is[2]) {
            case 1:
                $im = imagecreatefromgif($src);
                if ($im !== false) {
                    if (!imagegif($im, $dst)) {
                        throw new Exception("Save {$dst} error.");
                    }
                    imagedestroy($im);
                }
                break;
            case 2:
                $im = imagecreatefromjpeg($src);
                if ($im !== false) {
                    if (!imagejpeg($im, $dst, $this->quality)) {
                        throw new Exception("Save {$dst} error.");
                    }
                    imagedestroy($im);
                }
                break;
            case 3:
                $im = imagecreatefrompng($src);
                if ($im !== false) {
                    if (!imagepng($im, $dst)) {
                        throw new Exception("Save {$dst} error.");
                    }
                    imagedestroy($im);
                }
                break;
        }
        unlink($src);
    }
}
