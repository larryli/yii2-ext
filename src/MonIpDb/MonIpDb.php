<?php

namespace LarryLi\Yii\Extras\MonIpDb;

use ArrayAccess;
use Countable;
use Exception;
use Iterator;
use larryli\monipdb\MonipdbTrait;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;

/**
 * MonIpDb Component
 *
 * `Yii::$app->ipDb['202.103.24.68']`
 */
class MonIpDb extends Component implements ArrayAccess, Countable, Iterator
{
    use MonipdbTrait {
        offsetGet as protected traitOffsetGet;
    }

    /**
     * @var string
     */
    public $filename;
    /**
     * @var bool
     */
    public $datx = true;
    /**
     * @var string
     */
    protected $data;
    /**
     * @var array
     */
    protected $cached = [];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     * @throws InvalidValueException
     */
    public function init()
    {
        if (empty($this->filename)) {
            throw new InvalidConfigException('Monipdb::filename must be set.');
        }
        $this->filename = Yii::getAlias($this->filename);
        try {
            $file = $this->load($this->filename, $this->datx);
            $this->data = fread($file, fstat($file)['size'] - 4);
            fclose($file);
        } catch (Exception $e) {
            throw new InvalidValueException("Invalid {$this->filename} file!");
        }
    }

    /**
     * @return bool
     * @deprecated
     */
    public function exists()
    {
        return file_exists($this->filename);
    }

    /**
     * @param $ip
     * @return string
     * @deprecated
     */
    public function find($ip)
    {
        return $this->offsetGet($ip);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        if (!isset($this->cached[$offset])) {
            $this->cached[$offset] = static::traitOffsetGet($offset);
        }
        return $this->cached[$offset];
    }

    /**
     * @param int $offset
     * @param int $len
     * @return string
     */
    protected function read($offset, $len)
    {
        return substr($this->data, $offset, $len);
    }
}
