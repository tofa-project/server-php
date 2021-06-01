<?php

namespace TofaProject\ServerPhp\Errors;

use Error;

/**
 * When there is a conflict between client GUI and Daemon.
 * Nothing which server can fix from its side
 */
class ClDaConflict extends Error {}
