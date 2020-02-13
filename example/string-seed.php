<?php
require __DIR__ . "/../vendor/autoload.php";

use g105b\drng\Random;
use g105b\drng\StringSeed;

$rand = new Random(
	new StringSeed("i like cats")
);

echo "Random sequence: ";

for($i = 0; $i < 10; $i++) {
	if($i > 0) {
		echo ", ";
	}

	echo $rand->getInt(1, 10);
}