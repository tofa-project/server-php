<?php

namespace TofaProject\ServerPhp;

use Psr\Http\Message\ResponseInterface;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

use TofaProject\ServerPhp\Uri\Decode;
use TofaProject\ServerPhp\Errors\ConnectTimedOut;
use TofaProject\ServerPhp\Errors\CallTimedOut;
use TofaProject\ServerPhp\Errors\RequestFailed;
use TofaProject\ServerPhp\Errors\CallForbidden;
use TofaProject\ServerPhp\Errors\BadCall;
use TofaProject\ServerPhp\Errors\CallConflicts;
use TofaProject\ServerPhp\Errors\CallRejected;
use TofaProject\ServerPhp\Errors\ClDaConflict;
use TofaProject\ServerPhp\Errors\UnsupportedResponseCode;

class Calls {
    /**
     * HTTP client instance
     */
    protected GuzzleClient $Client;

    /**
     * default vars
     */
    protected array $vars = [
        // 60 seconds to download descriptors and establish connection to Tofa client
        // used for PING calls
        'CALL_CONNECT_TIMEOUT' => 60,

        // 45 seconds for user to decide
        // 30 seconds to compensate for network timeout
        'CALL_RESPONSE_TIMEOUT' => 45 + 30,
    ];

    /**
     * $proxy_addr: Tor proxy server addr:port
     * $vars: overwrites defaults
     */
    function __construct(string $proxy_addr, array $vars = []){
        // overwrite default vars
        foreach($vars as $key => $val)
            $this->vars[$key] = $val;

        // make new client
        $this->Client = new GuzzleClient([
            'proxy'=> 'socks5h://'.$proxy_addr,
        ]);
    }

    /**
     * Performs a request through proxy
     */
    protected function makeRequest(
        string $method,
        string $url,
        string $bodyJson = "",
        array $headers = []
    ): ResponseInterface 
    {
        try {
            $Res = $this->Client->request(
                $method,
                $url,
                [
                    'timeout' => $method === "PING" ?  $this->vars['CALL_CONNECT_TIMEOUT'] :  $this->vars['CALL_RESPONSE_TIMEOUT'],
                    'body' => $bodyJson,
                    'headers' => $headers,
                ]
            );

            return $Res;
        } 

        catch(ConnectException $E) {
            $msg = $E->getMessage();

            if(strpos($msg, "timed out") !== false)
            {
                if($method === "PING") throw new ConnectTimedOut($msg);
                else throw new CallTimedOut($msg);
            }

            throw new RequestFailed($msg);
        }

        catch(ClientException | ServerException $E) {
            $code = $E->getCode();

            switch($code){
                case 400: throw new BadCall;
                case 403: throw new CallForbidden;
                case 408: throw new CallTimedOut;
                case 409: throw new CallConflicts;
                case 570: throw new CallRejected;
                case 571: throw new ClDaConflict;
                default: throw new UnsupportedResponseCode($code);
            }
        }

    }

    /**
     * Sends a PING call to client
     */
    function ping(string $uri): bool
    {
        $DecUri = Decode::decode($uri);

        $this->makeRequest("PING", $DecUri->toUrl());

        return true;
    }

    /**
     * Sends a REG call to client
     * 
     * 'meta' must contain 'name' and 'description'
     * 
     * @return auth_token string
     */
    function reg(string $uri, array $meta): string
    {
        $this->ping($uri);

        $DecUri = Decode::decode($uri);

        $Res = $this->makeRequest(
            "REG", $DecUri->toUrl(), json_encode($meta)
        );

        $decRes = json_decode($Res->getBody());

        return $decRes->auth_token;
    }

    /**
     * Sends an ASK call to client
     * 
     * 'meta' must contain 'auth_token' and 'description'
     * 
     * returns true if client allowed call, false otherwise
     */
    function ask(string $uri, array $meta): bool
    {
        $this->ping($uri);

        $DecUri = Decode::decode($uri);

        try {
            $this->makeRequest(
                "ASK", 
                $DecUri->toUrl(), 
                json_encode($meta),
                [ 'Authorization' => 'Bearer '.$meta['auth_token']]
            );     
            
            return true;
        } catch(CallRejected $E) {
            return false;
        }
    }


    /**
     * Sends an INFO call to client
     * 
     * 'meta' must contain 'auth_token' and 'description'
     */
    function info(string $uri, array $meta)
    {
        $this->ping($uri);

        $DecUri = Decode::decode($uri);

        $this->makeRequest(
            "INFO", 
            $DecUri->toUrl(), 
            json_encode($meta),
            [ 'Authorization' => 'Bearer '.$meta['auth_token']]
        );
    }
}
