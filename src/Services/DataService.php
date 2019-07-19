<?php

/**
 * @author Ilya Dashevsky <il.dashevsky@gmail.com>
 * @license The MIT License (MIT), http://opensource.org/licenses/MIT
 * @link https://github.com/edevelops/magic-spa-backend
 */

declare(strict_types = 1);

namespace MagicSpa\Services;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use OpenCore\Rest\RestError;
use ErrorException;
use MagicSpa\Services\CronHandler;
use OpenCore\Utils\TextUtils;
use Monolog\Logger;
use MagicSpa\Services\DbLocator;
use MagicSpa\Models\Entities\Track;
use MagicSpa\Models\Entities\Album;
use MagicSpa\Models\Entities\Artist;
use MagicSpa\Models\Entities\Tag;
use MagicSpa\Models\Entities\AbstractEntity;
use MagicSpa\Services\Mp3TagsManager;
use OpenCore\Utils\Collections;

class DataService {


    private $container;
    private $logger;
    private $db;
    private $mp3TagsManager;

    public function __construct(ContainerInterface $container, Logger $logger, DbLocator $db, Mp3TagsManager $mp3TagsManager) {
        $this->container = $container;
        $this->logger = $logger;
        $this->db = $db;
        $this->mp3TagsManager = $mp3TagsManager;
    }
    
    private function makeFilePath(Track $track){
        return DATA_ROOT.'/files/'.sprintf('%08d.mp3', $track->id);
    }
        
    // ============= Albums =============

    public function addAlbum($title, int $year=null){
        return $this->db->getMapper(Album::class)->create([
            'title' => $title,
            'year' => $year,
        ]);
    }
    
    public function getAlbums(){
        return $this->db->getMapper(Album::class)->all();
    }
    
    public function getAlbumsByIds(array $albumIds){
        return Collections::toList($this->db->getMapper(Album::class)->where(['id' => $albumIds]));
    }
    
    // ============= Artists =============
    
    public function addArtist($title){
        return $this->db->getMapper(Artist::class)->create([
            'title' => $title,
        ]);
    }
    
    public function getArtists(){
        return $this->db->getMapper(Artist::class)->all();
    }
    
    public function getArtistsByIds(array $artistIds){
        return Collections::toList($this->db->getMapper(Artist::class)->where(['id' => $artistIds]));
    }
    
    public function getTagsByIds(array $tagIds){
        return Collections::toList($this->db->getMapper(Tag::class)->where(['id' => $tagIds]));
    }
    
    // ============= Tracks =============
    
    public function addTrack($title, array $artists, Album $album=null, string $version=null, float $duration=null){
        $mapper=$this->db->getMapper(Track::class);
        
        $ret=$mapper->build([
            'title' => $title,
            'album_id' => $album ? $album->id : null,

            'version' => $version,

            'has_file' => false,
            'duration' => $duration,
            'bitrate' => null,
        ]);
        
        $ret->relation('artists', $artists);
        
        $mapper->saveRecursive($ret);
        return $ret;
    }
    
    public function getTracks(){
        return $this->db->getMapper(Track::class)->all()->with(['artists', 'tags']);
    }
    
    public function updateTrack(Track $track, array $data){
        $track->relation('tags', $data['tags']);
        $track->relation('artists', $data['artists']);
        $track->album_id = $data['album'] ? $data['album']->id : null;
        $track->title=$data['title'];
        $track->version=$data['version'];
        $this->db->getMapper(Track::class)->saveRecursive($track);
    }
    
    public function deleteTrack(Track $track){
        $this->deleteTrackFile($track);
        $mapper=$this->db->getMapper(Track::class);
        $track->relation('artists', []);
        $track->relation('tags', []);
        $mapper->saveRecursive($track); // remove links with tracks first
        $mapper->delete($track);
    }
        
    public function deleteTrackFile(Track $track){
        if($track->has_file){
            $filePath=$this->makeFilePath($track);

            unlink($filePath);

            $track->has_file = false;
            $track->duration = null;
            $track->bitrate = null;

            $this->db->getMapper(Track::class)->save($track);
        }
    }
    
    public function getTrackFile(Track $track){
        $filePath=$this->makeFilePath($track);
        return file_exists($filePath) ? file_get_contents($filePath) : null;
    }
    
    public function getTrackFileInfo(Track $track){
        $filePath=$this->makeFilePath($track);
        return $this->mp3TagsManager->getFileInfo($filePath);
    }
    
    public function storeTrackFile(Track $track, string $tmpFilePath){
        
        $this->mp3TagsManager->removeAllTags($tmpFilePath);
        
        $filePath=$this->makeFilePath($track);
        
        copy($tmpFilePath, $filePath);
        
        $info=$this->mp3TagsManager->getFileInfo($filePath);
        
        //var_export($info);die;
        
        $track->has_file = true;
        $track->duration = $info['playtime_seconds'];
        $track->bitrate = $info['bitrate'];
        
        $this->db->getMapper(Track::class)->save($track);
        
    }
    
    public function getTracksByIdsWithDeps(array $trackIds){
        return Collections::toList($this->db->getMapper(Track::class)->where(['id' => $trackIds])->with(['album', 'artists']));
    }
        
    public function getTrackById(int $trackId){
        return $this->db->getMapper(Track::class)->get($trackId)->entity();
    }
    
    // ============= Tags =============
    
    public function addTag(string $title, string $color){
        return $this->db->getMapper(Tag::class)->create([
            'title' => $title,
            'color' => $color,
        ]);
    }
    
    public function getTags(){
        return $this->db->getMapper(Tag::class)->all();
    }
    
    public function getTagById(int $tagId){
        return $this->db->getMapper(Tag::class)->get($tagId)->entity();
    }
    
    public function updateTag(Tag $tag, string $title, string $color){
        $tag->title=$title;
        $tag->color=$color;
        $this->db->getMapper(Tag::class)->save($tag);
        return $tag;
    }
    
    public function deleteTag(Tag $tag){
        $mapper=$this->db->getMapper(Tag::class);
        $tag->relation('tracks', []);
        $mapper->saveRecursive($tag); // remove links with tracks first
        $mapper->delete($tag);
    }
    
    
}
