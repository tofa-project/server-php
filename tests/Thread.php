<?php

namespace TofaProject\ServerPhp\Tests;

use TofaProject\ServerPhp\Calls;

class Thread {
    static function run(string $uri, string $proxy_host = '127.0.0.1:9050')
    {
        
        $Calls = new Calls($proxy_host);

        echo $Calls->ping($uri);

        $auth_token = $Calls->reg($uri, [
            'name' => "server-php test",
            'description' => "server-php test",
        ]);

        echo $Calls->ask($uri, [
            'auth_token' => $auth_token,
            'description' => "some app attempted something",
        ]);

        echo $Calls->info($uri, [
            'auth_token' => $auth_token,
            'description' => "some app did something",
        ]);
        
    }
}