<?php

namespace MagicSpa\Views;

class SaveRequest {
    /**
     * @var string
     * @required
     */
    public $dir;
    /**
     * @var string
     * @required
     */
    public $template;
    /**
     * @var int[]
     * @required
     */
    public $tracks;
    
    /**
     * @var bool
     */
    public $groupByTags;
    
}
