<?php
// scripts/serial_reader.php

if (PHP_OS_FAMILY === 'Windows') {
    $port = '\\\\.\\COM3';
    exec("mode \"{$port}\" baud=9600 data=8 parity=n stop=1");
} else {
    $port = '/dev/ttyACM0';
    // Compatible stty for various systems
    exec("stty -F {$port} 9600 cs8 -cstopb -parenb -icanon -echo 2>/dev/null") ||
    exec("stty 9600 cs8 -cstopb -parenb -icanon -echo < {$port}");
}

$fp = fopen($port, 'r+');
if (!$fp) {
    echo "Error: cannot open port {$port}\n";
    exit(1);
}

stream_set_blocking($fp, false);
usleep(500_000); // Wait 0.5s after opening the port

echo "Listening on {$port} (9600 baud)...\n";

while (true) {
    $line = fgets($fp, 128);

    if ($line === false) {
        usleep(200_000);
        continue;
    }

    $line = trim($line);
    if ($line === '') {
        continue;
    }

    echo "Received raw line: “{$line}”\n";

    if (preg_match('/BIO:([\d.]+),NONBIO:([\d.]+)/', $line, $m)) {
        $bio = (float)$m[1];
        $nonbio = (float)$m[2];

        echo "Parsed BIO={$bio}%, NONBIO={$nonbio}%\n";

        $url = 'http://127.0.0.1:8000/api/bin-reading-read';
        $query = http_build_query(['bio' => $bio, 'nonbio' => $nonbio]);

        $resp = file_get_contents("{$url}?{$query}");
        if ($resp === false) {
            $error = error_get_last();
            echo "Error: could not reach Laravel API.\n";
            echo "Details: " . $error['message'] . "\n";
        } else {
            echo "Laravel replied: {$resp}\n";
        }
    } else {
        echo "Line didn’t match expected pattern.\n";
    }
}
