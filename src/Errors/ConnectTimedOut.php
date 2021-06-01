<?php

namespace TofaProject\ServerPhp\Errors;

use Error;

/**
 *  Fired in preflight/ping requests
 *  When connection doesn't establish in mean time or indicates circuit is unstable
 */
class ConnectTimedOut extends Error {}
