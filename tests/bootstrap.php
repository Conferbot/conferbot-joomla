<?php

/**
 * Test bootstrap: loads the pure helper without a Joomla runtime.
 * CONFERBOT_TEST_SUITE lets the file guard pass.
 */

define('CONFERBOT_TEST_SUITE', true);

require_once __DIR__ . '/../src/Helper/LoaderHelper.php';
