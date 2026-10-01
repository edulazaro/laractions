<?php

namespace EduLazaro\Laractions\Tests\Support;

use EduLazaro\Laractions\Action;

class DataAndOptionAction extends Action
{
    protected function handle(array $data, ?\stdClass $option = null): array
    {
        return ['data' => $data, 'option' => $option];
    }
}
