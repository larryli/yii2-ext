<?php

namespace extras\beanstalk;

use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use yii\base\Component;

/**
 * Class Beanstalk
 *
 * @property Pheanstalk $pheanstalk
 */
class Queue extends Component
{
    /**
     * @var string connection host
     */
    public $host = 'localhost';
    /**
     * @var int connection port
     */
    public $port = PheanstalkInterface::DEFAULT_PORT;
    /**
     * @var int connection timeout
     */
    public $connectTimeout = null;
    /**
     * @var bool connection persistent
     */
    public $connectPersistent = false;
    /**
     * @var string beanstalk tube
     */
    public $tube = 'queue';
    /**
     * @var Pheanstalk
     * */
    private $_pheanstalk;

    /**
     * @param string $string
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     * @return int
     */
    public function push($string, $priority = PheanstalkInterface::DEFAULT_PRIORITY, $delay = PheanstalkInterface::DEFAULT_DELAY, $ttr = PheanstalkInterface::DEFAULT_TTR)
    {
        return $this->getPheanstalk()->putInTube($this->tube, $string, $priority, $delay, $ttr);
    }

    /**
     * @return Pheanstalk
     */
    protected function getPheanstalk()
    {
        if (!$this->_pheanstalk) {
            $this->_pheanstalk = new Pheanstalk($this->host, $this->port, $this->connectTimeout, $this->connectPersistent);
        }
        return $this->_pheanstalk;
    }
}
