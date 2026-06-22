<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;

/**
 * Single parameter typed as a concrete object: a single array passed to run() must NOT be
 * treated as the "bag" (that would assign the whole array to a non-array param and crash);
 * its keys are mapped by name instead. Mirrors handle(File $file) in real packages.
 */
class TypedObjectAction extends Action
{
    protected function handle(\stdClass $payload): \stdClass
    {
        return $payload;
    }
}
