<?php
/**
 * @author Ilya Dashevsky <il.dashevsky@gmail.com>
 * @license The MIT License (MIT), http://opensource.org/licenses/MIT
 * @link https://github.com/edevelops/magic-spa-backend
 */

declare(strict_types = 1);

namespace MagicSpa\Services;

use getID3;
use getid3_lib;
use getid3_writetags;

use ErrorException;


class Mp3TagsManager{
    
    private function exec(array $args){
        
        $command='eyeD3 '.implode(' ', array_map(function($arg){
            return '"'.addslashes($arg).'"';
        }, $args));
        
        //echo 'COMMAND: '.$command;
        
        exec($command);
    }
    
    public function removeAllTags(string $filePath){
        $this->exec([$filePath, '--remove-all', '--remove-all-images', '--remove-all-lyrics', '--remove-all-comments', '--remove-all-objects']);
    }
        
    public static function stripInvalidUtf8BytesRecursive($data) {
		if (is_string($data)) {
			
            // Inspired by https://stackoverflow.com/a/1401716
            $regex = <<<'END'
/
  (
    (?: [\x00-\x7F]               # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]    # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2} # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3} # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                      # ...one or more times
  )
| ( [\x80-\xBF] | [\xC0-\xFF] )   # invalid byte in range 10000000 - 10111111 or 11000000 - 11111111
/x
END;
            $ret=preg_replace_callback($regex, function($captures){
                return $captures[1] ? $captures[1] : utf8_encode($captures[2]);
            }, $data);
		} else if (is_array($data)) {
			$ret = [];
			foreach ($data as $key => $value) {
				$ret[$key] = self::stripInvalidUtf8BytesRecursive($value);
			}
		}else{
            $ret=$data;
        }
		return $ret;
	}
    
    public function writeFileInfo(string $filePath, array $tags){
        
        $lib = $this->getId3Lib(); // need to init

        $writer = new getid3_writetags();

        $writer->filename = $filePath;
        $writer->tagformats = ['id3v1', 'id3v2.4'];
        $writer->tag_encoding = 'UTF-8';
        $writer->remove_other_tags = true;
        // populate data array
        $resultTagData = [];
        
        $resultTagData['title']=[$tags['title']];
        
        if($tags['artist']){
            $resultTagData['artist']=[$tags['artist']];
        }
        if($tags['album']){
            $resultTagData['album']=[$tags['album']];
        }
        if($tags['year']){
            $resultTagData['year']=[$tags['year']];
        }
        
        $writer->tag_data = $resultTagData;
        // write tags
        if (!$writer->WriteTags()) {
            throw new ErrorException('Unable to write tags: '.implode('; ', (array)$writer->errors));
        }
    }

    private function getId3Lib(){
        
        $encoding = 'UTF-8';
        $lib = new getID3();
        $lib->setOption(array('encoding' => $encoding));
        
        return $lib;
    }

    public function getFileInfo(string $filePath){
        
        $lib=$this->getId3Lib();
        
        $info = $lib->analyze($filePath);
        
        getid3_lib::CopyTagsToComments($info);
        
        getid3_lib::ksort_recursive($info);
        return self::stripInvalidUtf8BytesRecursive($info, 'utf-8');
        
    }
    
}
