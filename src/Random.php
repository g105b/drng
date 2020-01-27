<?php
namespace g105b\DRNG;

class Random {
	private string $seedBytes;
	private int $aesCounter;

	public function __construct(string $seedBytes = null) {
		if(is_null($seedBytes)) {
			$seedBytes = $this->generateSeed();
		}

		$this->seedBytes = $seedBytes;
		$this->aesCounter = 0;
	}

	public function getBytes(int $size):string {
		return openssl_encrypt(
			str_repeat("\0", $size),
			"aes-128-ctr",
			$this->seedBytes,
			OPENSSL_RAW_DATA,
			$this->getIv($size)
		);
	}

	private function generateSeed():string {
		return random_bytes(16);
	}

	private function getIv(int $size):string {
		$iv = "";
		$originalAesCounter = $this->aesCounter;

		$numBytesToIncrement = ceil(($size + ($size % 16)) / 16);
		$this->aesCounter += $numBytesToIncrement;

		while ($originalAesCounter > 0) {
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