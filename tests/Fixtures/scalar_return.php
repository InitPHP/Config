<?php

declare(strict_types=1);

// Intentionally returns a non-array value to exercise the
// "file must return an array" failure path.
return 'not-an-array';
