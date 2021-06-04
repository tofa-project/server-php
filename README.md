## PHP Adapter
Use this package if you're developing web apps in PHP. It requires Composer.

## Include
I did not add it to packagist yet.

1. Download it as ZIP and store it somewhere in your project folder.
2. Update your project composer according to the following scheme (aka require must contain, autoload must contain):
```
  "require": {  
    "guzzlehttp/guzzle": "^7.3",
  },

  "autoload": {
      "psr-4": {"TofaProject\\ServerPhp\\": "where you placed ZIP content/src/"}
  },
```
3. Do `composer du`

## Use
Flow of use:
1. Initialize with Tor socks5 proxy address
2. Use adapter methods to communicate with Tofa Clients

All calls are synchronous.
```php
<?php

use \TofaProject\ServerPhp\Calls;
use \TofaProject\ServerPhp\Errors\BadURI;
use \TofaProject\ServerPhp\Errors\BadCall;

try {
  /**
  * First create an adapter instance with Tor proxy address. Usually it's 127.0.0.1:9050
  * Adapter instance contains all call methods used to interact with Tofa Client.
  * Adapter instance should be reused.
  */
  $Calls = new Calls("127.0.0.1:9050");

  /**
  * Attempts to register with Tofa Client. 
  * It requires Client URI, and metadata so human can recognize your service.
  * Metadata must contain "name" and "description" (both strings).
  * 
  * @returns: the authentication token which is mandatory when performing ASK and INFO calls.
  *           If any error occurred it will throw an exception
  *
  * Registration process must occur only once, and authentication token
  * must be stored in a database and re-used for eternity.
  */
  $auth_token = $Calls->reg($uri, [
      'name' => "server-php test",
      'description' => "server-php test",
  ]);


  /**
  * Attempts to ask for confirmation form Tofa Client amid an action. 
  * It requires Client URI, and metadata so human can recognize the action.
  * Metadata must contain a comprehensive "description" and the "auth_token" (both strings).
  * 
  * @returns: true/false whether human allowed the action or not.
  *           If any error occurred it will throw an exception
  */
  $does_client_allow_action = $Calls->ask($uri, [
      'auth_token' => $auth_token,
      'description' => "some app attempted something",
  ]);


  /**
  * Attempts to send an INFO call. This is only a notification sent to the Client.
  * It requires Client URI, and metadata so human can recognize your service.
  * Metadata must contain "name" and "description" (both strings).
  * 
  * @returns: void
  *           If any error occurred it will throw an exception
  */
  $Calls->info($uri, [
      'auth_token' => $auth_token,
      'description' => "some app did something",
  ]);
} 

/**
* Exceptions are splitted based on error case.
* You can take actions based on which error occurred.
*
* A full documented list can be browsed within IDE at \TofaProject\ServerPhp\Errors
*/
catch(BadURI $E) {

} catch(BadCall $E {

} // ...


```
