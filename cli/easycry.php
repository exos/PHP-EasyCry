<?php


require('../lib/EasyCry.php');


$options = array(
	'b' => array('Block size', true, 8 ),
	'Z' => array('Don\'t compress data', false, 1, 0),
	'k' => array('Key file', true),
	'p' => array('Password phrase', true),
	'a' => array('Output in ASCII', false, 1, 0),
	'w' => array('Wordwrap (with -a option)', true),
	'o' => array('Output file',true),
	't' => array('Text to encode/decode', true)
);

$params = array();


function help() {
	global $options,$argv;

	echo "Usage " . $argv[0] . "[-";
	
	foreach ($options as $k=>$op) {
		echo $k;
	}

	echo "] order [file] ";
	echo "\n\n";

	echo "Options:\n";
	foreach ($options as $k=>$op) {
                echo "\t -{$k} {$op[0]} ";
		if ($op[1] && isset($op[2]) && $op[2]) {
			echo "(By default: {$op[2]})";
		}
		echo "\n";
        }


	echo "\n";
	echo "For bugs/issues: https://github.com/exos/PHP-EasyCry\n";

	exit (0);
}

function getOption ($val) {
	global $options, $params;

	if (isset($params[$val])) {
		return $params[$val];
	} elseif (isset($options[$val])) {
                if (isset($options[$val][2])) {
			return $options[$val][2];
		} else {
			return null;
		}
        } else {
		throw new Exception ("Option $val dont exist");
	}

}


$rest = array();

for ($i = 1; $i < $argc; $i++ ) {

	if (preg_match('#-([\w]+)#', $argv[$i], $mod)) {
		$pars = $mod[1];
		$sj = 0;
		for ($j=0; $j < strlen($pars); $j++) {

			if (isset($options[$pars{$j}])) {

				if ($options[$pars{$j}][1]) {
	
					if (!isset($argv[$i+$sj+1])) {
						help();
					}

					$params[$pars{$j}] = $argv[$i+$sj+1];
					$sj++;

				} else {
					if (isset($options[$pars{$j}][3])) {
						$params[$pars{$j}] = $options[$pars{$j}][3];
					}
				}

			} else {
				fwrite(STDOUT, "Unknow option " . $pars{$j} . "\n"); 
				exit(1);
			}
		}
		$i += $sj;
	} else {
		$rest[] = $argv[$i];
	}

}


if (!isset($rest[0])) {
	help();
}

$cry = new EasyCry();

$cry->bloksize = getOption('b');
$cry->compress = (bool) getOption('Z');
$cry->binary = (bool) getOption('a');


if (getOption('p')) {
	$pass = getOption('p');
} elseif (getOption('k')) {

	if (file_exists(getOption('k'))) {
		$pass = file_get_contents(getOption('k'));
        } else {
                fwrite(STDOUT, "Key file don't exist \n");
                exit(2);
        }
} else {
        echo "Password: ";
        $pass = fgets(STDIN);
	$pass = substr($pass,0,-1);
}

if (getOption('t')) {
	$text = getOption('t');
} elseif (isset($rest[1])) {
	if (file_exists($rest[1])) {
		$text = file_get_contents($rest[1]);
	} else {
		fwrite(STDOUT, "{$rest[1]} don't exist \n");
                exit(3);
	}
} else {
	help();
}

switch ($rest[0]) {

	case 'encode':
		$res = $cry->encode($text, $pass);
		if (!$cry->binary && getOption('w')) {
			$res = wordwrap($res, (int) getOption('w'), "\n", true);
		}

		break;
	case 'decode':
		$res = $cry->decode($text, $pass);
		$res = $res['data'];
		break;

	default:
		help;
}

if (getOption('o')) {
	file_put_contents(getOption('o'), $res);
} else {
	print ($res);
	echo "\n";
	exit(0);
}
