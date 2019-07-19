<?php

namespace MagicSpa\Views;

class TrackApi {
    /**
     * @var int
     */
    public $id;
    
    /**
     * @var string
     * @required
     */
    public $title;
    
    /**
     * @var int
     */
    public $albumId;
    
    /**
     * @var int[]
     */
    public $artistIds;
       
    /**
     * @var int[]
     */
    public $tagIds;
    
    /**
     * @var string
     */
    public $version;
    
    /**
     * @var boolean
     */
    public $hasFile;
    
    /**
     * @var double
     */
    public $duration;
    
    /**
     * @var int
     */
    public $bitrate;
    
}
