<?php
namespace Crawler;

use Goutte;

class CharacterVolumes {
    const URL_PREFIX = "https://comicvine.gamespot.com/batman/4005-1699/issues-cover/";
    private static $client = NULL;
    private $url;

    private static function getClient() {
        if (is_null(self::$client)) {
            self::$client = new Goutte\Client();
        }

        return self::$client;
    }

    function __construct(String $detail_url) {
        $this->url = $detail_url . "/issues-cover";
    }

    function getVolumes() {
        $crawler = self::getClient()->request('GET', $this->url);
        // volumes to be an array of id => count pairs
        $volumes = [];
        $test=0;
        foreach($crawler->filter('ul.issue-grid > li')->siblings() as $li) {
            $a = NULL;
            $test2 = "";
            foreach($li->childNodes as $node) {
                $test2 = $test2 . $node->tagName;
                if($node->tagName == 'a') {
                    $a = $node;
                }
            }
            $id;
            $href;
            if (!is_null($a)) {
                $href = $a->getAttribute('href');
                $matches = [];
                preg_match("/4050-(\d+)/", $href, $matches);
                $id = $matches[0];
                preg_match("/\d+(?= appearances)/", $a->textContent, $matches);
                $count = $matches[0];
                $volumes[$id] = $count;
            }
        };

        return $volumes;
    }

    function getUrl() {
        return $this->url;
    }
}

?>