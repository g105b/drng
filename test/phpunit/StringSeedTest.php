<?php
namespace g105b\drng\Test;

use g105b\drng\StringSeed;
use PHPUnit\Framework\TestCase;

class StringSeedTest extends TestCase {
	public function test16byteString() {
		$bytes = random_bytes(16);
		$sut = new StringSeed($bytes);
		self::assertEquals($bytes, $sut);
	}

	public function testShorterString() {
		$inputString = "cat mouse";
		$sut = new StringSeed($inputString);
		$seedValue = (string)$sut;
		self::assertStringStartsWith($inputString, $seedValue);
		self::assertGreaterThan(strlen($inputString), strlen($seedValue));
	}

	public function testLongerString() {
		$inputString = "here comes the sun, do do do do, here comes the sun";
		$sut = new StringSeed($inputString);
		$seedValue = (string)$sut;
		self::assertStringStartsWith($inputString, $seedValue);
		self::assertGreaterThan(strlen($inputString), strlen($seedValue));
	}
}