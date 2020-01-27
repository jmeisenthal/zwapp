<?php
declare(strict_types=1);
require 'vendor/autoload.php'; // include Composer's autoloader
require('php/comicVine.php');
require('php/mongo.php');

use PHPUnit\Framework\TestCase;

final class ZwappTest extends TestCase {
	public static function setUpBeforeClass(): void {
		exec("npm run init_data");
	}

	protected function setUp(): void {
	}

	public function testInitData() {
		$client = new MongoDB\Client("mongodb://localhost:27017");

		$this->assertEquals(9, $client->zwapp->publishers->count());
		$this->assertEquals(263, $client->zwapp->characters->count());
		$this->assertEquals(1838, $client->zwapp->volumes->count());
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
		$expected_url = "https://comicvine.gamespot.com/api/image/square_avatar/426367-marvel.gif";
		$actual_url = $marvel->icon_url;
		$this->assertEquals($actual_url, $expected_url, "The 'icon_url' property should be '$expected_url'; instead it is '$actual_url'");
	}
}
?>