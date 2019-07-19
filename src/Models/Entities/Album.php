<?php

namespace MagicSpa\Models\Entities;

use Spot\MapperInterface as Mapper;
use Spot\EntityInterface as Entity;
use MagicSpa\Models\Entities\Track;

class Album extends AbstractEntity {

    protected static $table = 'albums';

    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title' => ['type' => 'string'],
            'year' => ['type' => 'integer'],
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity) {
        return [
            'tracks' => $mapper->hasMany($entity, Track::class, 'album_id'),
        ];
    }


}
