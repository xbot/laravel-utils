<?php
namespace Ox3f\LaravelUtils\Log;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log as LaravelLog;
use Ox3f\LaravelUtils\Log\Log;

$calledInController = false;

function debug_backtrace() {
    global $calledInController;
    if ($calledInController) {
        return json_decode('[{"file":"\/Users\/xbot\/Sites\/sample-project\/vendor\/xbot\/laravel-utils\/src\/Log\/Log.php","line":85,"function":"parseCallStack","class":"Ox3f\\\LaravelUtils\\\Log\\\Log","object":{},"type":"->"},{"file":"\/Users\/xbot\/Sites\/sample-project\/app\/Api\/V1\/Controllers\/WorkController.php","line":29,"function":"saveInput","class":"Ox3f\\\LaravelUtils\\\Log\\\Log","type":"::"},{"function":"save","class":"App\\\Api\\\V1\\\Controllers\\\WorkController","object":{},"type":"->"},{"file":"\/Users\/xbot\/Sites\/sample-project\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Controller.php","line":55,"function":"call_user_func_array"},{"file":"\/Users\/xbot\/Sites\/sample-project\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/ControllerDispatcher.php","line":44,"function":"callAction","class":"Illuminate\\\Routing\\\Controller","object":{},"type":"->"}]', true);
    } else {
        return json_decode('[{"file":"\/Users\/xbot\/Sites\/sample-project\/vendor\/xbot\/laravel-utils\/src\/Log\/Log.php","line":85,"function":"parseCallStack","class":"Ox3f\\\LaravelUtils\\\Log\\\Log","object":{},"type":"->"},{"file":"\/Users\/xbot\/Sites\/sample-project\/app\/Notation.php","line":21,"function":"saveInput","class":"Ox3f\\\LaravelUtils\\\Log\\\Log","type":"::"},{"file":"\/Users\/xbot\/Sites\/sample-project\/app\/Api\/V1\/Controllers\/NotationController.php","line":32,"function":"incrNo","class":"App\\\Notation","type":"::"},{"function":"save","class":"App\\\Api\\\V1\\\Controllers\\\NotationController","object":{},"type":"->"},{"file":"\/Users\/xbot\/Sites\/sample-project\/vendor\/laravel\/framework\/src\/Illuminate\/Routing\/Controller.php","line":55,"function":"call_user_func_array"}]', true);
    }
}

class LogTest extends TestCase
{
    /**
     * @covers Ox3f\LaravelUtils\Log\Log::saveInput
     * @covers Ox3f\LaravelUtils\Log\Log::saveOutput
     * @covers Ox3f\LaravelUtils\Log\Log::parseCallStack
     * @covers Ox3f\LaravelUtils\Log\Log::getInstance
     * @covers Ox3f\LaravelUtils\Log\Log::__construct
     * @covers Ox3f\LaravelUtils\Log\Log::__callStatic
     */
    public function testAll()
    {
        global $calledInController;

        Auth::shouldReceive('user')
            ->once()
            ->andReturn((object)['name' => 'jim',]);

        // test being called in a plain method
        $calledInController = false;

        LaravelLog::shouldReceive('debug')
            ->once()
            ->with('jim | App\Notation::incrNo | Input:1');

        Log::saveInput(1);

        LaravelLog::shouldReceive('debug')
            ->once()
            ->with('jim | App\Notation::incrNo | Output:2');

        Log::saveOutput(2);

        // test being called in a controller action
        $calledInController = true;

        Request::shouldReceive('path')
            ->once()
            ->andReturn('api/user');
        Request::shouldReceive('except')
            ->once()
            ->with('_url')
            ->andReturn(['id' => 18,]);
        LaravelLog::shouldReceive('debug')
            ->once()
            ->with('jim | api/user | Input:{"id":18}');

        Log::saveInput();

        Request::shouldReceive('path')
            ->once()
            ->andReturn('api/user');
        LaravelLog::shouldReceive('debug')
            ->once()
            ->with('jim | api/user | Output:2');

        Log::saveOutput(2);

        Request::shouldReceive('path')
            ->once()
            ->andReturn('api/user');
        LaravelLog::shouldReceive('error')
            ->once()
            ->with('jim | api/user | this is an error');
        Log::error('this is an error');
        
        $this->assertEquals(0, 0);
    }
}
