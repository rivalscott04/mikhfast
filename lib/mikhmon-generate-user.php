<?php
/*
 * Hotspot user generation helpers (credentials + batched RouterOS API writes).
 */

function mikhmon_hotspot_user_add_sentences($server, $profile, $timelimit, $datalimit, $commt, $users)
{
	$sentences = array();
	foreach ($users as $row) {
		$sentences[] = array(
			'/ip/hotspot/user/add',
			array(
				'server' => (string) $server,
				'name' => (string) $row['name'],
				'password' => (string) $row['pass'],
				'profile' => (string) $profile,
				'limit-uptime' => (string) $timelimit,
				'limit-bytes-total' => (string) $datalimit,
				'comment' => (string) $commt,
			),
		);
	}
	return $sentences;
}

function mikhmon_hotspot_user_add_batch($API, $sentences, $chunkSize = 50)
{
	if (empty($sentences)) {
		return;
	}
	if (method_exists($API, 'commMulti')) {
		$API->commMulti($sentences, $chunkSize);
		return;
	}
	foreach ($sentences as $sentence) {
		$API->comm($sentence[0], $sentence[1]);
	}
}

function mikhmon_generate_hotspot_users($user, $char, $userl, $prefix, $qty)
{
	$users = array();
	$a = array('1' => '', '', 1, 2, 2, 3, 3, 4);
	$userl = (int) $userl;
	$qty = (int) $qty;

	if ($user === 'up') {
		for ($i = 0; $i < $qty; $i++) {
			$name = mikhmon_random_username($char, $userl);
			$pass = mikhmon_random_password($userl);
			$users[] = array(
				'name' => $prefix . $name,
				'pass' => $pass,
			);
		}
	} elseif ($user === 'vc') {
		$shuf = $userl - $a[$userl];
		for ($i = 0; $i < $qty; $i++) {
			$row = mikhmon_random_voucher_code($char, $userl, $shuf, $prefix);
			$users[] = array(
				'name' => $row['name'],
				'pass' => $row['pass'],
			);
		}
	}

	return $users;
}

function mikhmon_random_username($char, $userl)
{
	if ($char === 'lower') {
		return randLC($userl);
	}
	if ($char === 'upper') {
		return randUC($userl);
	}
	if ($char === 'upplow') {
		return randULC($userl);
	}
	if ($char === 'mix') {
		return randNLC($userl);
	}
	if ($char === 'mix1') {
		return randNUC($userl);
	}
	if ($char === 'mix2') {
		return randNULC($userl);
	}
	return randLC($userl);
}

function mikhmon_random_password($userl)
{
	if ($userl >= 3 && $userl <= 8) {
		return randN($userl);
	}
	return randN(4);
}

function mikhmon_random_voucher_suffix($userl)
{
	if ($userl === 3) {
		return randN(1);
	}
	if ($userl === 4 || $userl === 5) {
		return randN(2);
	}
	if ($userl === 6 || $userl === 7) {
		return randN(3);
	}
	if ($userl === 8) {
		return randN(4);
	}
	return randN(2);
}

function mikhmon_random_voucher_code($char, $userl, $shuf, $prefix)
{
	$name = '';
	$pass = '';

	if ($char === 'lower') {
		$name = randLC($shuf);
	} elseif ($char === 'upper') {
		$name = randUC($shuf);
	} elseif ($char === 'upplow') {
		$name = randULC($shuf);
	}

	$suffix = mikhmon_random_voucher_suffix($userl);
	$name = $prefix . $name . $suffix;
	$pass = $name;

	if ($char === 'num') {
		$num = randN($userl);
		$name = $prefix . $num;
		$pass = $name;
	} elseif ($char === 'mix') {
		$code = randNLC($userl);
		$name = $prefix . $code;
		$pass = $name;
	} elseif ($char === 'mix1') {
		$code = randNUC($userl);
		$name = $prefix . $code;
		$pass = $name;
	} elseif ($char === 'mix2') {
		$code = randNULC($userl);
		$name = $prefix . $code;
		$pass = $name;
	}

	return array('name' => $name, 'pass' => $pass);
}
