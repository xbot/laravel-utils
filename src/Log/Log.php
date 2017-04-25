<?php

namespace Ox3f\LaravelUtils\Log;

use Illuminate\Support\Facades\Log as LaravelLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Class Log
 * @author donie
 */
class Log
{
    private static $instance;

    private $id;              // Identity of the log, username by default.
    private $referer;         // Request path for RESTful APIs, method name for ordinary class methods.
    private $isHttp;          // True for RESTful APIs, otherwise, false.
    private $callStackParsed; // Whether call stack has been parsed.

    private function __construct() {
        $user = Auth::user();
        $this->id = !empty($user->name) ? $user->name : 'anonymous';
    }
    private function __clone() {}

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Parse the call stack
     *
     * @return void
     */
    private function parseCallStack() {
        $traceInfo = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT|DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $this->referer = '';
        $this->isHttp = false;
        foreach ($traceInfo as $callInfo) {
            if ($callInfo['class'] != __CLASS__) {
                if (preg_match('/Controller$/', $callInfo['class'])) {
                    $this->referer = Request::path();
                    $this->isHttp = true;
                } else {
                    $this->referer = $callInfo['class'].$callInfo['type'].$callInfo['function'];
                }
                break;
            }
        }
        $this->callStackParsed = true;
    }

    /**
     * Wrapper of the laravel log facade
     *
     * @return void
     */
    public static function __callStatic($name, $args)
    {
        if (!self::getInstance()->callStackParsed)
            self::getInstance()->parseCallStack();

        $id      = self::getInstance()->id;
        $referer = self::getInstance()->referer;
        $msg     = !empty($args) ? $args[0] : '';
        LaravelLog::$name("{$id} | {$referer} | {$msg}");

        self::getInstance()->callStackParsed = false;
    }
    
    /**
     * Save parameters of the request or arguments of the method to log at debug level
     *
     * @param mixed $args Empty for HTTP calls, needed for ordinary class methods
     * @return void
     */
    public static function saveInput($args=null)
    {
        self::getInstance()->parseCallStack();
        if (self::getInstance()->isHttp) $args = Request::except('_url');
        self::debug('Input:'.json_encode($args));
    }
    
    /**
     * Save the output to log at debug level
     *
     * @param mixed $result Result to be saved
     * @return void
     */
    public static function saveOutput($result)
    {
        self::getInstance()->parseCallStack();
        self::debug('Output:'.json_encode($result));
    }
}
