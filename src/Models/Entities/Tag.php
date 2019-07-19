<?php

namespace MagicSpa\Models\Entities;

use Spot\MapperInterface as Mapper;
use Spot\EntityInterface as Entity;
use MagicSpa\Models\Entities\Track;

class Tag extends AbstractEntity {

    protected static $table = 'tags';

    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title' => ['type' => 'string'],
            'color' => ['type' => 'string'],
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity) {
        return [
            'tracks' => $mapper->hasManyThrough($entity, Track::class, TrackTag::class, 'track_id', 'tag_id'),
        ];
    }


}
