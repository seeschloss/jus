<?php

class Base60 {
	public static $base60_alphabet = '0123456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
	public static $base10_alphabet = '0123456789';

	static function encode($dec) {
		return self::arbitrary_base_convert($dec, self::$base10_alphabet, self::$base60_alphabet);
	}

	static function decode($base60) {
		return self::arbitrary_base_convert($base60, self::$base60_alphabet, self::$base10_alphabet);
	}

	static function arbitrary_base_convert($numstring, $fromalphabet, $toalphabet) {
		$frombase = mb_strlen($fromalphabet);
		$tobase = mb_strlen($toalphabet);

		$length = mb_strlen($numstring);
		$result = '';

		for ($i = 0; $i < $length; $i++) {
			$number[$i] = mb_strpos($fromalphabet, mb_substr($numstring, $i, 1));
		}

		do {
			$divide = 0;
			$newlen = 0;
			for ($i = 0; $i < $length; $i++) {
				$divide = $divide * $frombase + $number[$i];
				if ($divide >= $tobase) {
					$number[$newlen++] = (int)($divide / $tobase);
					$divide = $divide % $tobase;
				} elseif ($newlen > 0) {
					$number[$newlen++] = 0;
				}
			}
			$length = $newlen;
			$result = mb_substr($toalphabet, $divide, 1) . $result;
		} while ($newlen != 0);

		return $result;
	}
}
