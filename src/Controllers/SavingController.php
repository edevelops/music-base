<?php

/**
 * @author Ilya Dashevsky <il.dashevsky@gmail.com>
 * @license The MIT License (MIT), http://opensource.org/licenses/MIT
 * @link https://github.com/edevelops/magic-spa-backend
 */

declare(strict_types = 1);


namespace MagicSpa\Controllers;

use Monolog\Logger;
use MagicSpa\Services\DataService;
use MagicSpa\Services\Mp3TagsManager;
use MagicSpa\Views\SaveRequest;
use MagicSpa\Models\Entities\Track;
use OpenCore\Rest\RestError;
use OpenCore\Utils\Collections;
use MagicSpa\Models\Entities\Artist;
use MagicSpa\Models\Entities\Tag;

class SavingController {

    private $logger;
    private $dataService;
    private $mp3TagsManager;

    public function __construct(Logger $logger, DataService $dataService, Mp3TagsManager $mp3TagsManager) {
        $this->logger = $logger;
        $this->dataService = $dataService;
        $this->mp3TagsManager = $mp3TagsManager;
    }
    
    private static function titleToUnderscore(string $title){
        return preg_replace('/\s+/u', '_', $title);
    }
    
    private static function prepareTitle($title){
        $title=str_replace('"', '\'', $title ? $title : '');
        $title=str_replace(['<','>'], ['(',')'], $title);
        $title=str_replace(['/','\\','|'], ['-','-','-'], $title);
        $title=str_replace([':','?','*'], ['-','',''], $title);
        return $title;
    }
    
    private function saveTrack(Track $track, string $baseDir, string $template){
        $file=$this->dataService->getTrackFile($track);
        
        $album=$track->album;
        
        $originalArtistTitles=Collections::map($track->artists, function(Artist $artist){
            return $artist->title;
        });
        $originalTrackTitle=$track->title;
        $originalAlbumTitle=$album ? $album->title : null;
        $originalTrackVersion=$track->version;
        
        $artistTitles=Collections::map($originalArtistTitles, function($title){
            return self::prepareTitle($title);
        });
        $albumYear=$album ? $album->year : null;
        $albumTitle=self::prepareTitle($originalAlbumTitle);
        $trackTitle=self::prepareTitle($originalTrackTitle);
        $version=self::prepareTitle($originalTrackVersion);
        
        $replaces=[
            '{artists}' => implode(', ', $artistTitles),
            '{artists_}'=>self::titleToUnderscore(implode(',_', $artistTitles)),
            '{track}'=>$trackTitle,
            '{track_}'=>self::titleToUnderscore($trackTitle),
            '{album}'=>$albumTitle ? $albumTitle : 'UNKNOWN',
            '{version}'=>$version ? $version : '',
            '{ (version)}'=>$version ? ' ('.$version.')' : '',
            '{ - version}'=>$version ? ' - '.$version : '',
            '{_version_}'=>$version ? '_'.self::titleToUnderscore($version) : '',
            '{year}'=>$albumYear ? $albumYear : '0000',
        ];
        
        $compiledTemplate= str_replace(array_keys($replaces), array_values($replaces), $template);
        
        $resultFullName=$baseDir.'/'.$compiledTemplate.'.mp3';
        
        $parentDir=dirname($resultFullName);
        if(!file_exists($parentDir)){
            mkdir($parentDir, 0755, true);
        }
        
        file_put_contents($resultFullName, $file, LOCK_EX);
        
        
        $this->mp3TagsManager->writeFileInfo($resultFullName, [
            'title' => $originalTrackTitle.($originalTrackVersion ? ' ('.$originalTrackVersion.')' : ''),
            'artist' => implode(', ', $originalArtistTitles),
            'album' => $originalAlbumTitle,
            'year' => $albumYear,
        ]);
        
    }
    
    public function doSave(SaveRequest $body){
        $baseDir=$body->dir;
        $trackIds=$body->tracks;        
        if(!$trackIds){
            throw new RestError('No tracks', RestError::HTTP_CONFLICT);
        }
        if(!is_writable($baseDir)){
            throw new RestError('Dir '.$baseDir.' is not writable', RestError::HTTP_CONFLICT);
        }
        
        $tracks=$this->dataService->getTracksByIdsWithDeps($trackIds);
        $template=$body->template;
        foreach($tracks as $track){
            if($track->has_file){
                if($body->groupByTags && ($tags=Collections::toList($track->tags))){
                    foreach($tags as $tag){
                        $this->saveTrack($track, $baseDir, mb_strtolower($tag->title).'/'.$template);
                    }
                }else{
                    $this->saveTrack($track, $baseDir, $template);
                }
            }
        }
        
    }
    
    
}
