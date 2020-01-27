<?php
namespace g105b\DRNG;

class Random {
	private string $seedBytes;

	public function __construct(string $seedBytes) {
		$this->seedBytes = $seedBytes;
	}

	public function getBytes(int $size):string {
		return "This is not random!";
	}
}