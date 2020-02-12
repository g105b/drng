Deterministic random number generator
=====================================

This library generates byte sequences with the ability to convert to integers and floating points. The sequences generated appear to be random, but can be fully determined by providing a known seed value. The main use of this library is in procedural generation algorithms.

The cryptographic algorithms provided by OpenSSL are used to generate the random data, specifically using the Advanced Encryption Standard (AES) operating in Galois/Counter mode (GCM).

Each instance of the `Random` class maintains its AES counter, so there is theoretically no limit to the amount of random data produced. The deterministic nature of this library is possible due to the counter's persistence while generating the initialisation vector, along with the encryption key being set from a provided seed value.

*****

Usage example
-------------

### Using a string seed to determine the random sequence

`string-seed.php`:

```php
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
```

The above example will always output the same sequence, due to the use of the seed "i like cats":

```
Random sequence: 1, 9, 7, 6, 5, 6, 8, 10, 2, 5
```