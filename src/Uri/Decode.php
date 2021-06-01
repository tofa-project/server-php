<?php

namespace TofaProject\ServerPhp\Uri;

use TofaProject\ServerPhp\Uri\Decoded;
use TofaProject\ServerPhp\Errors\BadURI;
use TofaProject\ServerPhp\Errors\UnsupportedURI;

/**
 * Helps decoding an URI into bones
 */
class Decode {

    /**
     * Decodes version 0 URIs
     */
    private static function decodeV0(array $splUri): Decoded{
    
        // split by '/'
        $splPortPath = explode('/', $splUri[2]);
        if(count($splPortPath) < 2)
            throw new BadURI('splPortPath');
    
        // return bones
        return new Decoded(
            $splUri[0],
            $splUri[1],
            $splPortPath[0],
            $splPortPath[1],
        );
    }

    /**
     * Decodes an URI returning a Decoded type or throwing exception on failiure
     */
    static function decode(string $uri): Decoded {
        // decode b64
        $decUri = base64_decode($uri);
        if(!$decUri)
            throw new BadURI('base64');

        // split by ':'
        $splUri = explode(":", $decUri);
        if(count($splUri) < 2)
            throw new BadURI('splUri');

        // handle based on version
        switch($splUri[0]) {
            case "0": return self::decodeV0($splUri);
            default: throw new UnsupportedURI;
        }
    }
}
