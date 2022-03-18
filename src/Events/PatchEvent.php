<?php

namespace Dentro\Patcher\Events;

use Dentro\Patcher\Patch;

abstract class PatchEvent
{
    public function __construct(
        public Patch $patch
    ) {}
}
