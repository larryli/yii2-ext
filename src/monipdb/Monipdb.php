<?php

namespace extras\monipdb;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;

/**
 * IPv4 地址反查
 */
class Monipdb extends Component implements \Countable, \Iterator
{
    /**
     * @var string
     */
    public $filename;
    /**
     * @var bool
     */
    public $datx = false;
    /**
     * @var
     */
    protected $position;
    /**
     * @var int
     */
    protected $step = 8;
    /**
     * @var int
     */
    protected $reserve = 1024;
    /**
     * @var
     */
    protected $lengthFunc;
    /**
     * @var null
     */
    private $_fp = null;
    /**
     * @var int
     */
    private $_offset = 0;
    /**
     * @var int
     */
    private $_end = 0;
    /**
     * @var array
     */
    private $_index = [];
    /**
     * @var array
     */
    private $_data = [];
    /**
     * @var string[]
     */
    private $_cached;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (empty($this->filename)) {
            throw new InvalidConfigException('Monipdb::filename must be set.');
        }
        $this->filename = Yii::getAlias($this->filename);
        if ($this->datx) {
            $this->step = 9;
            $this->reserve = 262144;
            $this->lengthFunc = function($start) {
                return unpack('nlen', $this->_index{$start + 7} . $this->_index{$start + 8});
            };
        } else {
            $this->lengthFunc = function($start) {
                return unpack('Clen', $this->_index{$start + 7});
            };
        }
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return file_exists($this->filename);
    }

    /**
     * @throws InvalidConfigException
     */
    public function load()
    {
        if ($this->_fp !== null) {
            return;
        }
        $this->_fp = @fopen($this->filename, 'rb');
        if ($this->_fp === false) {
            throw new InvalidConfigException("Invalid {$this->filename} file!");
        }
        $offset = unpack('Nlen', fread($this->_fp, 4));
        $this->_offset = $offset['len'] - $this->reserve;
        if ($this->_offset < 4) {
            throw new InvalidConfigException("Invalid {$this->filename} file!");
        }
        $this->_end = $this->_offset - 4;
        $this->_index = fread($this->_fp, $this->_end);
        $this->rewind();
    }

    /**
     * @param $ip
     * @return string
     * @throws InvalidConfigException
     */
    public function find($ip)
    {
        $ip_start2 = intval(floor($ip / (256 * 256)));
        $ip_start = intval(floor($ip_start2 / 256));

        if ($ip_start < 0 || $ip_start > 255) {
            throw new InvalidValueException("{$ip} is not valid.");
        }
        if (isset($this->_cached[$ip]) === true) {
            return $this->_cached[$ip];
        }

        $this->load();
        $nip = pack('N', $ip);
        $tmp_offset = ($this->datx ? $ip_start2 : $ip_start) * 4;
        $start = unpack('Vlen', $this->_index{$tmp_offset} . $this->_index{$tmp_offset + 1} . $this->_index{$tmp_offset + 2} . $this->_index{$tmp_offset + 3});
        $start = $start['len'] * $this->step + $this->reserve;
        $end = $this->_end - $this->step;
        $start = $this->idx($nip, $start, $end);
        if ($start === null) {
            $this->_cached[$ip] = '';
        } else {
            $offset = unpack('Vlen', $this->_index{$start + 4} . $this->_index{$start + 5} . $this->_index{$start + 6} . "\x0");
            $length = call_user_func($this->lengthFunc, $start);
            $this->_cached[$ip] = $this->read($offset['len'], $length['len']);
        }
        return $this->_cached[$ip];
    }

    /**
     * @param $ip
     * @param $l
     * @param $r
     * @return int|null
     */
    private function idx($ip, $l, $r)
    {
        for ($m = $l; $m <= $r; $m += $this->step) {
            if ($this->_index{$m} . $this->_index{$m + 1} . $this->_index{$m + 2} . $this->_index{$m + 3} >= $ip) {
                return $m;
            }
        }
        return null;
    }

    /**
     * @param $offset
     * @param $len
     * @return mixed
     */
    private function read($offset, $len)
    {
        if (!isset($this->_data[$offset])) {
            fseek($this->_fp, $this->_offset + $offset);
            $this->_data[$offset] = fread($this->_fp, $len);
        }
        return $this->_data[$offset];
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = $this->reserve;
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->_fp != null) {
            fclose($this->_fp);
        }
    }

    /**
     * Return the current element
     *
     * @return string
     */
    public function current()
    {
        $offset = unpack('Vlen', $this->_index{$this->position + 4} . $this->_index{$this->position + 5} . $this->_index{$this->position + 6} . "\x0");
        $length = call_user_func($this->lengthFunc, $this->position);
        return $this->read($offset['len'], $length['len']);
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        $this->position += $this->step;
    }

    /**
     * Return the key of the current element
     *
     * @return integer
     */
    public function key()
    {
        $ip = unpack('Nlen', $this->_index{$this->position} . $this->_index{$this->position + 1} . $this->_index{$this->position + 2} . $this->_index{$this->position + 3});
        return intval($ip['len']);
    }

    /**
     * Checks if current position is valid
     *
     * @return boolean
     * @throws InvalidConfigException
     */
    public function valid()
    {
        $this->load();
        return $this->position < $this->_end;
    }

    /**
     * Count elements of an object
     *
     * @return int
     * @throws InvalidConfigException
     */
    public function count()
    {
        $this->load();
        return intval(($this->_end - $this->reserve) / $this->step);
    }
}
