<?php

namespace extras\beanstalk;

use Pheanstalk\Exception\ConnectionException;
use Pheanstalk\Exception\ServerException;
use Pheanstalk\Job;
use Yii;
use yii\console\Controller;
use yii\di\Instance;
use yii\helpers\Console;

/**
 * Queue Worker.
 */
class WorkerController extends Controller
{
    /**
     * @var string
     */
    public $defaultAction = 'run';
    /**
     * @var Queue|string
     */
    public $queue = 'queue';
    /**
     * @var string Exec command
     */
    public $exec;
    /**
     * @var int Reserve timeout
     */
    public $timeout = 3;
    /**
     * @var int Connect failed waiting
     */
    public $waitingTime = 1;
    /**
     * @var int Connect failed max waiting
     */
    public $waitingMax = 10;
    /**
     * @var string Delay priority
     */
    public $delayPriority = '100';
    /**
     * @var int Delay time
     */
    public $delayTime = 5;
    /**
     * @var int Used for Decaying. When max reached job is deleted or delayed with
     */
    public $delayMax = 3;
    /**
     * @var bool
     */
    private $_inProgress = false;
    /**
     * @var bool
     */
    private $_willTerminate = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->queue = Instance::ensure($this->queue, Queue::class);
    }

    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        switch ($actionID) {
            case 'run':
                return array_merge([
                    'exec',
                    'timeout',
                    'waitingTime',
                    'waitingMax',
                    'delayPriority',
                    'delayTime',
                    'delayMax',
                ], parent::options($actionID));
        }
        return parent::options($actionID);
    }

    /**
     * Run
     */
    public function actionRun()
    {
        if (empty($this->exec)) {
            $this->exec = 'YII_ENV=' . YII_ENV . ' "' . PHP_BINARY . '" "' . Yii::$app->request->scriptFile . '" ';
        }
        try {
            $this->stdout("Listening {$this->queue->tube} tube.\n", Console::FG_GREEN);
            $this->queue->pheanstalk->watchOnly($this->queue->tube);
        } catch (ConnectionException $e) {
            $this->stderr("Connect beanstalk service failed: {$e->getMessage()}\n", Console::FG_RED);
            return $this->end(static::EXIT_CODE_ERROR);
        }

        $this->registerSignalHandler();
        $sleep = 0;
        while (!$this->_willTerminate) {
            pcntl_signal_dispatch();
            try {
                $job = $this->queue->pheanstalk->reserve($this->timeout);
                if (!$job) {
                    continue;
                }
                $this->_inProgress = true;
                $this->executeJob($job);
            } catch (ConnectionException $e) {
                if ($sleep <= $this->waitingMax) {
                    $sleep += $this->waitingTime;
                    sleep($sleep);
                    continue;
                } else {
                    $this->stderr("Connect beanstalk service failed: {$e->getMessage()}\n", Console::FG_RED);
                    return $this->end(static::EXIT_CODE_ERROR);
                }
            }
            $sleep = 0;
            $this->_inProgress = false;
        }
        return $this->end(static::EXIT_CODE_NORMAL);
    }

    /**
     * Decay a job with a fixed delay
     *
     * @param $job
     */
    protected function decayJob($job)
    {
        $jobStats = $this->queue->pheanstalk->statsJob($job);
        $delay_job = $jobStats->releases + $jobStats->delay + $this->delayTime;
        if ($jobStats->releases >= $this->delayMax) {
            $this->queue->pheanstalk->bury($job);
            $this->stderr("Decaying Job Buried!\n", Console::FG_RED);
        } else {
            $this->queue->pheanstalk->release($job, $this->delayPriority, $delay_job);
        }
    }

    /**
     * @return bool
     */
    protected function registerSignalHandler()
    {
        if (!extension_loaded('pcntl')) {
            $this->stdout("Warning: Process Control Extension is not loaded. Signal Handling Disabled!\n", Console::FG_YELLOW);
            return null;
        }
        pcntl_signal(SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(SIGINT, [$this, 'signalHandler']);
        $this->stdout("Process Control Extension is loaded. Signal Handling Registered!\n", Console::FG_GREEN);
        return true;
    }

    /**
     * @param $signal
     *
     * @return bool
     */
    public function signalHandler($signal)
    {
        $this->stdout("Received signal {$signal}.\n", Console::FG_YELLOW);
        switch ($signal) {
            case SIGTERM:
            case SIGINT:
                $this->stdout("Exiting...\n", Console::FG_RED);
                if (!$this->_inProgress) {
                    return $this->end();
                }
                $this->terminate();
                break;
            default:
                break;
        }
        return null;
    }

    /**
     * Terminate job
     */
    protected function terminate()
    {
        $this->_willTerminate = true;
    }

    /**
     * End job
     *
     * @param int $status
     * @return bool
     * @throws \yii\base\ExitException
     */
    protected function end($status = 0)
    {
        return Yii::$app->end($status);
    }

    /**
     * Execute job and handle outcome
     *
     * @param \Pheanstalk\Job $job
     */
    protected function executeJob($job)
    {
        $command = $this->exec . strval($job->getData()) . ' --color=0 2>&1';
        try {
            exec($command, $output, $return);
            $output = implode("\n", $output);
            switch ($return) {
                case static::EXIT_CODE_NORMAL:
                    $this->stdout("Call {$command} success. Output: {$output}\n", Console::FG_GREEN);
                    $this->queue->pheanstalk->delete($job);
                    break;
                case static::EXIT_CODE_ERROR:
                    $this->stderr("Call {$command} failed. Return: {$return}, Output: {$output}\n", Console::FG_YELLOW);
                    $this->decayJob($job);
                    break;
                default:
                    $this->stderr("Call {$command} failed. Return: {$return}, Output: {$output}\n", Console::FG_RED);
                    $this->queue->pheanstalk->bury($job);
                    break;
            }
        } catch (\Exception $e) {
            $this->stderr("Call {$command} catched. Exception: \"{$e->getMessage()}\"\n", Console::FG_RED);
            $this->queue->pheanstalk->bury($job);
        }
    }

    /**
     * Stats
     * @return int
     */
    public function actionStats()
    {
        try {
            $stats = $this->queue->pheanstalk->statsTube($this->queue->tube);
            foreach ($stats as $k => $v) {
                $this->stdout("{$k}: {$v}\n");
            }
            return static::EXIT_CODE_NORMAL;
        } catch (ServerException $e) {
            $this->stderr("{$this->queue->tube} stats error: {$e->getMessage()}\n", Console::FG_RED);
            return static::EXIT_CODE_ERROR;
        }
    }

    /**
     * Get next buried job
     * @return int
     */
    public function actionBuried()
    {
        try {
            $job = $this->queue->pheanstalk->peekBuried($this->queue->tube);
            $this->stdout("{$this->queue->tube} peek buried job: #{$job->getId()} '{$job->getData()}'\n");
            return static::EXIT_CODE_NORMAL;
        } catch (ServerException $e) {
            $this->stderr("{$this->queue->tube} peek buried error: {$e->getMessage()}\n", Console::FG_RED);
            return static::EXIT_CODE_ERROR;
        }
    }

    /**
     * Delete job
     * @param $id
     * @return int
     */
    public function actionDelete($id)
    {
        try {
            $job = new Job($id, '');
            $this->queue->pheanstalk->delete($job);
            $this->stdout("delete job #{$id}: deleted\n");
            return static::EXIT_CODE_NORMAL;
        } catch (ServerException $e) {
            $this->stderr("delete job #{$id} error: {$e->getMessage()}\n", Console::FG_RED);
            return static::EXIT_CODE_ERROR;
        }
    }

    /**
     * Kick job
     * @param $id
     * @return int
     */
    public function actionKick($id)
    {
        try {
            $job = new Job($id, '');
            $this->queue->pheanstalk->kickJob($job);
            $this->stdout("kick job #{$id}: kicked\n");
            return static::EXIT_CODE_NORMAL;
        } catch (ServerException $e) {
            $this->stderr("kick job #{$id} error: {$e->getMessage()}\n", Console::FG_RED);
            return static::EXIT_CODE_ERROR;
        }
    }
}
