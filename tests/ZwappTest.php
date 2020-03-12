<?php
declare(strict_types=1);
	// require_once '/Development/zwapp/public/php/init_logger.php';
// include_once(__DIR__ . '/../vendor/autoload.php');
// include_once('php/init_logger.php');
include_once('vendor/autoload.php');
include_once('php/mongo.php');
include_once('php/comicVine.php');
include_once('php/crawler.php');
// require_once 'php/mongo.php';

use PHPUnit\Framework\TestCase;

final class ZwappTest extends TestCase {
	public static function setUpBeforeClass(): void {
		exec("npm run init_data");
	}

	protected function setUp(): void {
	}

	public function testComicVineURL() {
		$marvel = new ComicVine\Publisher("31");
		$url = $marvel->build_query_url("id,name,image");
		$expected_url = "https://comicvine.gamespot.com/api/publisher/31/?api_key=a587d36b7356338e04d0b132d105c9384a11691a&format=json&field_list=id%2Cname%2Cimage";
		$this->assertEquals($expected_url, $url, "URL formulation incorrect: was \"$url\"; should be \"$expected_url\"");
	}

	public function testInitData() {
		$client = new MongoDB\Client("mongodb://localhost:27017");

		$this->assertEquals(9, $client->zwapp->publishers->count());
		$this->assertEquals(263, $client->zwapp->characters->count());
		$this->assertEquals(1838, $client->zwapp->volumes->count());
	}

    public function testCharacterVolumesCrawl() {
        $crawler = new Crawler\CharacterVolumes("https://comicvine.gamespot.com/batman/4005-1699");

        $this->assertEquals("https://comicvine.gamespot.com/batman/4005-1699/issues-cover", $crawler->getUrl());
        $volumes = $crawler->getVolumes();
        $this->assertEquals(32, count($volumes),"Volume[0]: ".(array_values($volumes)[0]));

        // World's Finest:
        $this->assertEquals("4050-18058", array_keys($volumes)[0]);
    }

	public function testPublisherCacheCreation() {
		$publishers = ZwappMongo\Collection::getPublishers();
		$this->assertCount(9, $publishers->getList());

		$map = $publishers->getMap();
		$mapCount = count($map);
		$this->assertEquals(9, $mapCount, "Publisher map should have 9 entries. It has $mapCount.");

		$marvel = $map["31"];

		$this->assertNotNull($marvel, "Publisher with id-31 (Marvel) is missing");
		$this->assertNotNull($marvel->cv_query, "The ComicVine query for Marvel should not be null");

		return $marvel;
	}

	/**
	 * 
	 * @depends testPublisherCacheCreation
	 */
	public function testPublisherCacheValid($marvel) {
		$expected_url = "https://comicvine1.cbsistatic.com/uploads/square_avatar/0/2/426367-marvel.gif";
		$actual_url = $marvel->icon_url;
		$this->assertEquals($actual_url, $expected_url, "The 'icon_url' property should be '$expected_url'; instead it is '$actual_url'");
	}

	// /**
	//  * 
	//  * @depends testPublisherCacheCreation
	//  */
	// public function testPublisherGetChildren($marvel) {
	// 	$start = microtime(TRUE);
	// 	$children = $marvel->getChildren();
	// 	$stop = microtime(TRUE);
	// 	$time1 = $stop - $start;
	// 	print_r("\nTime1: $time1\n");
	// 	// print_r("\nChildren:\n");
	// 	// var_dump($children);

	// 	// Test that we get the right count of characters (may change over time)
	// 	$this->assertNotNull($children, "The children value retrieved for publisher \"$marvel->name\" should not be null");
	// 	$this->assertGreaterThan(0, count($children));
		
	// 	// Test for a particular character
		
	// 	// Test that the second time is much faster
	// 	$start = microtime(TRUE);
	// 	$children = $marvel->getChildren();
	// 	$stop = microtime(TRUE);
	// 	$time2 = $stop - $start;
	// 	print_r("\nTime2: $time2\n");
	// 	$this->assertGreaterThan($time2, $time1);
	// }

	// /**
	//  * 
	//  * @depends testPublisherCacheCreation
	//  */
	// public function testPublisherGetTopChildren($marvel) {
	// 	$characters = $marvel->getTopChildren();
	// 	// print_r("\ncharacters:\n");
	// 	// var_dump($characters);
	// 	$this->assertEquals("4005-1443", $characters[0]->id, "Expecting top character at marvel to be Spider-Man (4005-1443). Instead got {$characters[0]->name} ({$characters[0]->id})");
	// }

	public function testCharacterCacheCreation() {
		$characters = ZwappMongo\Collection::getCharacters();
		$this->assertCount(263, $characters->getList());

		$map = $characters->getMap();
		$mapCount = count($map);
		$this->assertEquals(263, $mapCount, "Character map should have 263 entries. It has $mapCount.");

		// $black_panther = $map["1477"];
        $black_panther = $map["4005-1477"];
		// print_r("\nbp:\n");
		// var_dump($black_panther);

		$this->assertNotNull($black_panther, "Character with id=\"4005-1477\" (Black Panther) is missing");
		$this->assertNotNull($black_panther->cv_query, "The ComicVine query for Black Panther should not be null");

		return $black_panther;
	}

	/**
	 * 
	 * @depends testCharacterCacheCreation
	 */
	public function testCharacterCacheValid($black_panther) {
		$expected_url = "https://comicvine1.cbsistatic.com/uploads/square_avatar/3/31666/5011137-blap2016001-cov-d6d2a.jpg";
		$actual_url = $black_panther->icon_url;
		$this->assertEquals($actual_url, $expected_url, "The 'icon_url' property should be '$expected_url'; instead it is '$actual_url'");
        $publisher = $black_panther->publisher;
        $this->assertNotNull($publisher, "No publisher found for character Black Panther");
	}

	// public function testGetTopCharacters() {
	// 	$id_array = ["4005-2268", "4005-1525", "4005-3202", "4005-1502", "4005-1443"]; // Thor, Punisher, Nick Fury, Wasp, Spider-Man
	// 	$top_characters = ZwappMongo\Collection::getCharacters()->getTopMatches($id_array);
	// 	// print_r("top characters:\n");
	// 	// var_dump($top_characters);

	// 	// Spider-Man should be first
	// 	$top_character = $top_characters[0];
	// 	// print_r("top character:\n");
	// 	// var_dump($top_character);
	// 	$this->assertEquals("4005-1443", $top_character->id, "Top character in list should be Spider-Man (4005-1443). Instead got {$top_character->id}");
		
	// 	// Punisher should be last
	// 	$this->assertEquals("4005-1525", $top_characters[4]->id, "Last character in list should be The Punisher (4005-1525). Instead got {$top_characters[4]->id}");
		
	// 	// Check nicj Fury's icon_url
	// 	$expected_url = "https://comicvine1.cbsistatic.com/uploads/square_avatar/10/100647/4076005-untitled-2.jpg";
	// 	$this->assertEquals($expected_url, $top_characters[2]->icon_url, "Icon URL for third in list (Nick Fury) should be \"$expected_url\". Instead got \"{$top_characters[2]->icon_url}\"");
	// }
}
?>