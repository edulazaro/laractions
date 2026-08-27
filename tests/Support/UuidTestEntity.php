<?php

namespace EduLazaro\Laractions\Tests\Support;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use EduLazaro\Laractions\Concerns\HasActions;

/**
 * Entity with a string primary key, to cover models using UUIDs or ULIDs.
 */
class UuidTestEntity extends Model
{
    use HasActions;
    use HasUuids;

    protected $table = 'uuid_test_entities';

    protected $guarded = [];
}
