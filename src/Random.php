<?php
namespace g105b\drng;

class Random {
	const BYTE_SIZE = 16;
	private string $seedBytes;
	private int $aesCounter;

	/**
	 * Must be constructed with a random seed of 16*n bytes, as required
	 * by openssl to generate the randomness.
	 */
	public function __construct(string $seedBytes = null) {
		if(is_null($seedBytes)) {
			$seedBytes = random_bytes(self::BYTE_SIZE);
		}

// We are using OpenSSL in AES counter method, so need to retain a counter.
		$this->aesCounter = 0;
		$this->checkSeedSize($seedBytes);
		$this->seedBytes = $seedBytes;
	}

	/**
	 * Return $size bytes from the random sequence determined by the seed.
	 */
	public function getBytes(int $size):string {
		return openssl_encrypt(
			str_repeat("\0", $size),
			"aes-128-ctr",
			$this->seedBytes,
			OPENSSL_RAW_DATA,
			$this->getIv($size)
		);
	}

	/**
	 * Return an integer greater than or equal to $min, and less than or
	 * equal to $max.
	 */
	public function getInt(int $min, int $max):int {
		if($min === $max) {
			return $min;
		}

		if($min > $max) {
			throw new MinMaxOutOfBoundsException();
		}

		$bitRegister = 0;
		$numBytes = 0;
		$bitMask = 0;
		$range = $max - $min;

// Generate the $bitMask to remove from the $intValue when finding a valid int:
		while($range > 0) {
			if($bitRegister % PHP_INT_SIZE === 0) {
				$numBytes++;
			}

			$bitRegister++;
// This bitwise operator is more efficient than: $range = floor($range / 2)
			$range >>= 1;
// Shift the bitmask 1 bit left. The | 1 ensures the first iteration is set
// to 1 when the bitMask has no 1 bits in it.
			$bitMask = $bitMask << 1 | 1;
		}
		$offset = $min;

// Brute-force find an integer value that falls within our requested range.
		do {
			$bytes = $this->getBytes($numBytes);

			$intValue = 0;
			for($i = 0; $i < $numBytes; $i++) {
				$intValue |= ord($bytes[$i])
					<< ($i * PHP_INT_SIZE);
			}

			$intValue &= $bitMask;
			$intValue += $offset;
		}
		while($intValue > $max || $intValue < $min);

		return $intValue;
	}

	/**
	 * Return a floating point value between 0 and $max.
	 */
	public function getScalar(float $max = 1.0):float {
		$intScalar = $this->getInt(0, PHP_INT_MAX);
		return ($max * $intScalar) / PHP_INT_MAX;
	}

	/** @throws SeedSizeOutOfBoundsException */
	private function checkSeedSize(string $seed):void {
		$strLen = strlen($seed);
		if($strLen === 0 || $strLen % 16 !== 0) {
			throw new SeedSizeOutOfBoundsException();
		}
	}

	/**
	 * OpenSSL is used to generate random values, according to the
	 * initialisation vector (IV) provided. This function returns an IV
	 * that follows a set sequence, allowing for the generation of
	 * deterministic random number generation.
	 */
	private function getIv(int $size):string {
		$iv = "";
		$originalAesCounter = $this->aesCounter;

		$numBytesToIncrement = ceil(($size + ($size % 16)) / 16);
		$this->aesCounter += $numBytesToIncrement;

		while($originalAesCounter > 0) {
			$iv = pack(
				"C",
				$originalAesCounter & 0xFF
			) . $iv;

			$originalAesCounter >>= 8;
		}

		return str_pad(
			$iv,
			16,
			"\0",
			STR_PAD_LEFT
		);
	}

	public function reset():void {
		$this->aesCounter = 0;
	}
}