<?php
// scripts/serial_reader.php

require __DIR__ . '/../vendor/autoload.php';

use lepiaf\SerialPort\SerialPort;
use lepiaf\SerialPort\Parser\SeparatorParser;
use lepiaf\SerialPort\Configure\TTYConfigure;
use lepiaf\SerialPort\Configure\WindowsConfigure;   // Custom-made Configure file to allow Windows devices
use lepiaf\SerialPort\Exception\DeviceNotFound;
use lepiaf\SerialPort\Exception\DeviceNotAvailable;
use lepiaf\SerialPort\Exception\DeviceNotOpened;

// 1) Candidates + Configure choice
if (PHP_OS_FAMILY === 'Windows') {
    $candidates = [
        '\\\\.\\\\COM3',    // Possible PHP literal for \\.\COM3
        '\\\\.\\COM3',    // PHP literal for \\.\COM3
        'COM3',
    ];
    $configure = new WindowsConfigure(9600);  // sets baud via mode COM3:
} else {
    $candidates = array_merge(
        glob('/dev/ttyACM*') ?: [],
        glob('/dev/ttyUSB*') ?: []
    );
    $configure = new TTYConfigure();          // configures via stty -F
}

if (empty($candidates)) {
    echo "No serial port candidates found.\n";
    exit(1);
}

$parser     = new SeparatorParser("\n");
$serialPort = new SerialPort($parser, $configure);
$openedPort = null;

foreach ($candidates as $port) {
    try {
        echo "Trying to open “{$port}”...\n";
        $serialPort->open($port);
        // **1.b** Assign to the same variable
        $openedPort = $port;
        echo "Successfully opened Port “{$port}”\n\n";
        break;
    } catch (DeviceNotFound $e) {
        echo "  • Not found: {$port}\n";
    } catch (DeviceNotAvailable $e) {
        echo "  • Not available: {$port}\n";
    } catch (DeviceNotOpened $e) {
        echo "  • Not opened: {$port}\n";
    } catch (\Throwable $e) {
        echo "  • Other error: " . $e->getMessage() . "\n";
    }
}

if (!$openedPort) {
    echo "Could not open any ports. Check Device Manager.\n";
    exit(1);
}

// **2.** Now that it’s guaranteed non‐null, you can safely echo it:
echo "Listening on {$openedPort} @ 9600 baud...\n";
while (true) {
    $raw = trim($serialPort->read());
    if ($raw === '') {
        usleep(200_000);
        continue;
    }

    // 1. Only process lines that start with "BIO:"
    if (strpos($raw, 'BIO:') !== 0) {
        // silently skip lines like "-----------------------"
        continue;
    }

    // 2. Anchor the regex to the start of the line
    if (preg_match('/^BIO:([\d.]+),NONBIO:([\d.]+)$/', $raw, $m)) {
        $bio    = (float)$m[1];
        $nonbio = (float)$m[2];
        echo "Fill Level Data:\nBIO={$bio}%, NONBIO={$nonbio}%\n";

        // 3. Forward to Laravel API
        $url   = 'http://localhost:8000/api/bin-reading-read';
        $query = http_build_query(compact('bio', 'nonbio'));
        $resp  = @file_get_contents("{$url}?{$query}");
        echo $resp === false
            ? "[API] Warning: Data cannot be sent to app. Open the Laravel app for it to read the data.\n"
            : "[API] {$resp}\n";
    }
    // else: this shouldn't happen now, but you could log/uncomment for debugging
}

