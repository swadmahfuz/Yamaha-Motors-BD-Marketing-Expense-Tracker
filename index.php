<?php

/**
 * Front controller for subdirectory installs (e.g. http://localhost/ymb-met/).
 * Keeps SCRIPT_NAME under /ymb-met so Laravel route matching works without /public in the URL.
 */
require __DIR__.'/public/index.php';
