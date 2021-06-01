<?php 

namespace TofaProject\ServerPhp\Uri;

/**
 * Contains URI bones and helper methods to handle them
 */
class Decoded {
    public int $version, $port;
    public string $onion, $path;

    public function __construct(
        int $version, 
        string $onion,
        int $port,
        string $path
    ){
        $this->version = $version;
        $this->onion = $onion;
        $this->port = $port;
        $this->path = $path;
    }

    /**
     * Converts URI bones to HTTP URL
     */
    public function toUrl(): string {
        return "http://" . 
            $this->onion . 
            ".onion:" . 
            $this->port . 
            "/" .
            $this->path
        ;
    }
}
