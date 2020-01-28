<?php
namespace g105b\drng\Test;

use g105b\drng\SeedSizeOutOfBoundsException;
use PHPUnit\Framework\TestCase;
use g105b\drng\Random;

class RandomTest extends TestCase {
	public function testSequenceIsDeterministic() {
		$seed = random_bytes(16);
		$sut1 = new Random($seed);
		$sut2 = new Random($seed);

		// Get a couple of outputs from SUT 1:
		$output1 = $sut1->getBytes(16);
		$sut1->getBytes(16);
		$sut1->getBytes(16);

		// Then get the first output from SUT 2:
		$output2 = $sut2->getBytes(16);
		self::assertSame($output1, $output2);
	}

	public function testSequenceIsNotSame() {
		$sut = new Random();
		$output1 = $sut->getBytes(16);
		$output2 = $sut->getBytes(16);

		self::assertNotSame($output1, $output2);
	}

	public function testManyCalls() {
		$sut = new Random();
		$totalBytes = "";
		$expectedLength = 0;
		$previousByteArray = [];

		for($i = 0; $i < 500; $i++) {
			$newBytes = $sut->getBytes(16);
			$expectedLength += 16;

			$totalBytes .= $newBytes;
			self::assertNotContains(
				$newBytes,
				$previousByteArray,
				"Random bytes should never provide the same sequence twice"
			);
			$previousByteArray []= $totalBytes;
		}

		self::assertEquals($expectedLength, strlen($totalBytes));
	}

	public function testSeedSizeOutOfBounds() {
		for($i = 0; $i < 128; $i++) {
			$exception = null;

			try {
				$bytes = str_repeat("\0", $i);
				new Random($bytes);
			}
			catch(SeedSizeOutOfBoundsException $exception) {}

			if($i > 0 && $i % 16 === 0) {
				self::assertNull($exception);
			}
			else {
				self::assertNotNull(
					$exception,
					"Exception should be thrown when byte size is not a multiple of 16"
				);
			}
		}
	}
}