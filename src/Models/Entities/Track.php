<?php

namespace MagicSpa\Models\Entities;

use Spot\MapperInterface as Mapper;
use Spot\EntityInterface as Entity;

use MagicSpa\Models\Mappers\TrackMapper;
use MagicSpa\Models\Entities\Album;
use MagicSpa\Models\Entities\TrackArtist;
use MagicSpa\Models\Entities\Artist;
use MagicSpa\Models\Entities\TrackTag;
use MagicSpa\Models\Entities\Tag;

class Track extends AbstractEntity {

    protected static $table = 'tracks';
    protected static $mapper = TrackMapper::class;

    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'title' => ['type' => 'string'],
            'album_id' => ['type' => 'integer'],
            'version' => ['type' => 'string'],
            
            'has_file' => ['type' => 'boolean'],
            'duration' => ['type' => 'float'],
            'bitrate' => ['type' => 'float'],
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity) {
        return [
            'album' => $mapper->belongsTo($entity, Album::class, 'album_id'),
            'artists' => $mapper->hasManyThrough($entity, Artist::class, TrackArtist::class, 'artist_id', 'track_id'),
            'tags' => $mapper->hasManyThrough($entity, Tag::class, TrackTag::class, 'tag_id', 'track_id'),
        ];
    }


}
