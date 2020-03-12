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
        global $logger;
        $crawler = self::getClient()->request('GET', $this->url);
        // volumes to be an array of id => count pairs
        $volumes = [];

        // Needed to treat as a collection of DOMElelements; the Goutte->each() wasn't working for me
        $logger_count = 0;
        foreach($crawler->filter('ul.issue-grid > li') as $li) {
            $logger_count++;
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

        $logger->debug("Crawler found $logger_count items.");
        // print_r("\n++++++++++++Crawler found $logger_count items\n");

        return $volumes;
    }

    function getUrl() {
        return $this->url;
    }
}

?>