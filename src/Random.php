<?php
namespace g105b\drng;

class Random {
	private string $seedBytes;
	private int $aesCounter;

	/**
	 * Must be constructed with a random seed of 16*n bytes, as required
	 * by openssl to generate the randomness.
	 */
	public function __construct(string $seedBytes = null) {
		if(is_null($seedBytes)) {
			$seedBytes = random_bytes(16);
		}

		$this->checkSeedSize($seedBytes);

		$this->seedBytes = $seedBytes;
// We are using OpenSSL in AES counter method, so need to retain a counter.
		$this->aesCounter = 0;
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

	/** @throws SeedSizeOutOfBoundsException */
	private function checkSeedSize(string $seed):void {
		$strlen = strlen($seed);
		if($strlen === 0 || $strlen % 16 !== 0) {
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
}