<?php
declare(strict_types=1);
require 'vendor/autoload.php'; // include Composer's autoloader

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
}
?>