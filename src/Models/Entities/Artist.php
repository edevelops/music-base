<?php

namespace MagicSpa\Models\Entities;

use Spot\MapperInterface as Mapper;
use Spot\EntityInterface as Entity;

class Artist extends AbstractEntity {

    protected static $table = 'artists';

    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title' => ['type' => 'string'],
        ];
    }
    
}
