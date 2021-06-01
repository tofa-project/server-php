<?php

namespace TofaProject\ServerPhp\Errors;

use Error;

/**
 *  When client is busy processing another call to the same app
 */
class CallConflicts extends Error {}
