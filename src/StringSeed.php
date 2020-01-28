<?php
namespace g105b\drng;

class StringSeed {
	const PAD_SIZE = 16;
	const PAD_STRING = "R3JlZyBCb3dsZXIh";

	private string $name;

	public function __construct(string $name) {
		$this->name = $name;
	}

	public function __toString():string {
		return $this->getPadded($this->name);
	}

	public function getPadded(string $string):string {
		$i = 0;

		while(strlen($string) % 16 !== 0) {
			$string .= self::PAD_STRING[$i];
			$i++;
		}

		return $string;
	}
}