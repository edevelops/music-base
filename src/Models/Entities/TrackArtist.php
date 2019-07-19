<?php

namespace MagicSpa\Models\Entities;

class TrackArtist extends AbstractEntity {

    protected static $table = 'track_artists';

    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'track_id' => ['type' => 'integer'],
            'artist_id' => ['type' => 'integer'],
        ];
    }


}
