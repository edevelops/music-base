<?php

/**
 * @author Ilya Dashevsky <il.dashevsky@gmail.com>
 * @license The MIT License (MIT), http://opensource.org/licenses/MIT
 * @link https://github.com/edevelops/magic-spa-backend
 */

declare(strict_types = 1);


namespace MagicSpa\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use OpenCore\Rest\RestError;
use ErrorException;
use MagicSpa\Services\CronHandler;
use OpenCore\Utils\TextUtils;
use Monolog\Logger;
use MagicSpa\Services\DataService;
use MagicSpa\Views\TrackApi;
use MagicSpa\Views\ArtistApi;
use MagicSpa\Views\AlbumApi;
use MagicSpa\Views\TagApi;
use MagicSpa\Models\Entities\Track;
use MagicSpa\Models\Entities\Album;
use MagicSpa\Models\Entities\Artist;
use MagicSpa\Models\Entities\Tag;
use OpenCore\Utils\Collections;
use MagicSpa\Views\UploadRequst;


class TrackController {

    private $logger;
    private $dataService;

    public function __construct(Logger $logger, DataService $dataService) {
        $this->logger = $logger;
        $this->dataService = $dataService;
    }
    
    private function getAndCheckTrack(int $id){
        $track=$this->dataService->getTrackById($id);
        if(!$track){
            throw new RestError('Track not found', RestError::HTTP_NOT_FOUND);
        }
        return $track;
    }
    
    private function getAndCheckTag(int $id){
        $tag=$this->dataService->getTagById($id);
        if(!$tag){
            throw new RestError('Tag not found', RestError::HTTP_NOT_FOUND);
        }
        return $tag;
    }
    
    public function getTrackFile(int $id){
        $track=$this->getAndCheckTrack($id);
        if(!$track->has_file){
            throw new RestError('Track has not file', RestError::HTTP_NOT_FOUND);
        }
        return ['data' => base64_encode($this->dataService->getTrackFile($track))];
    }
    
    public function deleteTrack(int $id){
        $track=$this->getAndCheckTrack($id);
        $this->dataService->deleteTrack($track);
    }
    
    public function updateTrack(int $id, TrackApi $body){
        $track=$this->getAndCheckTrack($id);
        
        $tags=[];
        foreach($body->tagIds as $tagId){
            $tags[]=$this->getAndCheckTag($tagId);
        }
        $this->dataService->updateTrack($track, [
            'title' => $body->title,
            'version' => $body->version,
            'tags'=>$body->tagIds ? $this->dataService->getTagsByIds($body->tagIds) : [],
            'artists' => $body->artistIds ? $this->dataService->getArtistsByIds($body->artistIds) : [],
            'album' => $body->albumId ? $this->dataService->getAlbumsByIds([$body->albumId])[0] : null,
        ]);
        
    }
    
    public function deleteTag(int $id){
        $tag=$this->getAndCheckTag($id);
        $this->dataService->deleteTag($tag);
    }
    
    public function deleteTrackFile(int $id){
        $track=$this->getAndCheckTrack($id);
        $this->dataService->deleteTrackFile($track);
    }
    
    public function getTrackFileInfo(int $id){
        $track=$this->getAndCheckTrack($id);
        if(!$track->has_file){
            throw new RestError('Track has not file', RestError::HTTP_NOT_FOUND);
        }
        return ['data' => $this->dataService->getTrackFileInfo($track)];
    }
    
    public function uploadFile(int $id, UploadRequst $body){
        $track=$this->getAndCheckTrack($id);
        $url=$body->url;
                
        $tmpFilePath=null;
        $tmpFilePath='/tmp/mb'.mt_rand(100000, 1000000).'.mp3';
        
        try{
            $fileContent=file_get_contents($url);
            file_put_contents($tmpFilePath, $fileContent);
        } catch (Exception $ex) {
            throw new RestError('Unable to get file: '.$ex->getMessage(), RestError::HTTP_CONFLICT);
        }
        
        if(!file_exists($tmpFilePath)){
            throw new RestError('File was not created', RestError::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            $this->dataService->storeTrackFile($track, $tmpFilePath);
            unlink($tmpFilePath);
        }
        
    }
    
    public function getTracks(){
        return Collections::map($this->dataService->getTracks()->order(['id' => 'ASC']), function(Track $track){
            return self::trackToApi($track);
        });
    }
    
    public function getTags(){
        return Collections::map($this->dataService->getTags(), function(Tag $tag){
            return self::tagToApi($tag);
        });
    }
        
    public function getArtists(){
        return Collections::map($this->dataService->getArtists(), function(Artist $artist){
            return self::artistToApi($artist);
        });
    }
        
    public function getAlbums(){
        return Collections::map($this->dataService->getAlbums(), function(Album $album){
            return self::albumToApi($album);
        });
    }
    
    public function createAlbum(AlbumApi $body){
        $album=$this->dataService->addAlbum($body->title, $body->year ? $body->year : null);
        return $this->albumToApi($album);
    }
    
    public function createArtist(ArtistApi $body){
        $album=$this->dataService->addArtist($body->title);
        return $this->artistToApi($album);
    }
    
    public function createTrack(TrackApi $body){
        $track=$this->dataService->addTrack(
            $body->title,
            $body->artistIds ? $this->dataService->getArtistsByIds($body->artistIds) : [],
            $body->albumId ? $this->dataService->getAlbumsByIds([$body->albumId])[0] : null,
            $body->version ? $body->version : null
        );
        return $this->trackToApi($track);
    }
    
    public function createTag(TagApi $body){
        $tag=$this->dataService->addTag($body->title, $body->color);
        return $this->tagToApi($tag);
    }
    
    public function updateTag(int $id, TagApi $body){
        $tag=$this->getAndCheckTag($id);
        $this->dataService->updateTag($tag, $body->title, $body->color);
        return $this->tagToApi($tag);
    }
    
    private static function trackToApi(Track $track){
        $ret=new TrackApi();
        
        $ret->id=(int)$track->id;
        $ret->title=$track->title;
        $ret->version=$track->version;
        $ret->albumId=$track->album_id ? (int)$track->album_id : null;
        $ret->artistIds=Collections::map($track->artists, function(Artist $artist){
            return (int)$artist->id;
        });
        $ret->tagIds=Collections::map($track->tags, function(Tag $tag){
            return (int)$tag->id;
        });
        $ret->hasFile=$track->has_file;
        $ret->duration=$track->duration;
        $ret->bitrate=$track->bitrate;
        
        return $ret;
    }
    
    private static function tagToApi(Tag $tag){
        $ret=new TagApi();
        
        $ret->id=(int)$tag->id;
        $ret->title=$tag->title;
        $ret->color=$tag->color;
        
        return $ret;
    }
    
    private static function albumToApi(Album $album){
        $ret=new AlbumApi();
        
        $ret->id=(int)$album->id;
        $ret->title=$album->title;
        $ret->year=$album->year;
        
        
        return $ret;
    }
    
    private static function artistToApi(Artist $artist){
        $ret=new ArtistApi();
        
        $ret->id=(int)$artist->id;
        $ret->title=$artist->title;
        
        
        return $ret;
    }
    
}
