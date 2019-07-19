<?php

namespace MagicSpa\Models\Entities;

class TrackTag extends AbstractEntity {

    protected static $table = 'track_tags';

    public static function fields() {
        return [
            'id' => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'track_id' => ['type' => 'integer'],
            'tag_id' => ['type' => 'integer'],
        ];
    }


}
