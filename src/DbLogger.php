<?php namespace Klaravel\DbLogger;

/**
 * DbLogger class is helper class to log all database
 * query into log file.
 */
class DbLogger
{
	/**
     * Application version
     *
     * @var string
     */
    protected $version;

    /**
     * Whether SQL queries should be logged
     *
     * @var bool
     */
    protected $logStatus;

    /**
     * Whether slow SQL queries should be logged
     *
     * @var bool
     */
    protected $slowLogStatus;

    /**
     * Slow query execution time
     *
     * @var float
     */
    protected $slowLogTime;

    /**
     * Whether log file should be overridden for each request
     *
     * @var bool
     */
    protected $override;

    /**
     * Location where log files should be stored
     *
     * @var string
     */
    protected $directory;

    /**
     * Whether query execution time should be converted to seconds
     *
     * @var bool
     */
    protected $convertToSeconds;

    /**
     * Whether artisan queries should be saved into separate files
     *
     * @var bool
     */
    protected $separateConsoleLog;

    /**
     * SqlLogger constructor.
     *
     * @param $app
     */
    public function __construct() {
        $this->app = app();

        $this->logStatus = config('dblogger.log_queries');
        $this->slowLogStatus = config('dblogger.log_slow_queries');
        $this->slowLogTime = config('dblogger.slow_queries_min_exec_time');
        $this->override = config('dblogger.override_log');
        $this->directory = rtrim(config('dblogger.directory'), '\\/');
        $this->convertToSeconds = config('dblogger.convert_to_seconds');
        $this->separateConsoleLog = config('dblogger.log_console_to_separate_file');
    }

    /**
     * Log query
     *
     * @param mixed $query
     * @param mixed $bindings
     * @param mixed $time
     */
    public function log($query, $bindings, $time)
    {
        static $queryNr = 0;

        ++$queryNr;

        try {
            list($sqlQuery, $execTime) =
                $this->getSqlQuery($query, $bindings, $time);
        } catch (\Exception $e) {
            $this->app->log->notice("SQL query {$queryNr} cannot be bound: " .
                $query);

            return;
        }

        $logData = $this->getLogData($queryNr, $sqlQuery, $execTime);

        $this->save($logData, $execTime, $queryNr);
    }

    /**
     * Save data to log file
     *
     * @param string $data
     * @param int $execTime
     * @param int $queryNr
     */
    protected function save($data, $execTime, $queryNr)
    {
        $filePrefix = ($this->separateConsoleLog &&
        $this->app->runningInConsole()) ? '-artisan' : '';

        // save normal query to file if enabled
        if ($this->logStatus) {
            $this->saveLog($data, date('Y-m-d') . $filePrefix . '-log.sql',
                ($queryNr == 1 && (bool)$this->override));
        }

        // save slow query to file if enabled
        if ($this->slowLogStatus && $execTime >= $this->slowLogTime) {
            $this->saveLog($data,
                date('Y-m-d') . $filePrefix . '-slow-log.sql');
        }
    }

    /**
     * Save data to log file
     *
     * @param string $data
     * @param string $fileName
     * @param bool $override
     */
    protected function saveLog($data, $fileName, $override = false)
    {
        file_put_contents($this->directory . DIRECTORY_SEPARATOR . $fileName,
            $data, $override ? 0 : FILE_APPEND);
    }

    /**
     * Get full query information to be used to save it
     *
     * @param int $queryNr
     * @param string $query
     * @param int $execTime
     *
     * @return string
     */
    protected function getLogData($queryNr, $query, $execTime)
    {
        $time = $this->convertToSeconds ? ($execTime / 1000.0) . '.s'
            : $execTime . 'ms';

        return '/* Query ' . $queryNr . ' - ' . date('Y-m-d H:i:s') . ' [' .
        $time . ']' . "  */\n" . $query . ';' .
        "\n/*==================================================*/\n";
    }

    /**
     * Get SQL query and query exection time
     *
     * @param mixed $query
     * @param mixed $bindings
     * @param mixed $execTime
     *
     * @return array
     */
    protected function getSqlQuery($query, $bindings, $execTime)
    {
        $bindings = $query->bindings;
        $execTime = $query->time;
        $query = $query->sql;
    
        // need to format bindings properly
        foreach ($bindings as $i => $binding) {
            if ($binding instanceof \DateTime) {
                $bindings[$i] = $binding->format('Y-m-d H:i:s');
            } elseif (is_string($binding)) {
                $bindings[$i] = str_replace("'", "\\'", $binding);
            }
        }

        // now we create full SQL query - in case of failure, we log this
        $query = str_replace(['%', '?', "\n"], ['%%', "'%s'", ' '], $query);
        $fullSql = vsprintf($query, $bindings);

        return [$fullSql, $execTime];
    }
}