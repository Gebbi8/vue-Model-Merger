<?php
error_reporting(E_ALL ^ E_WARNING);

$BIVES   = getenv('BIVES_URL') ?: "https://bives.bio.informatik.uni-rostock.de/";
$storage = '/tmp/mergestorage';

$f1       = $_FILES['file1'] ?? null;
$f2       = $_FILES['file2'] ?? null;
$job      = $_REQUEST['jobID'] ?? '';
$getFile  = $_REQUEST['getFile'] ?? '';
$commands = $_REQUEST['commands'] ?? 'merge';

$saveMerge = true;

$hasUploads = isset($f1, $f2) && !empty($f1['tmp_name']) && !empty($f2['tmp_name']);
// $job / $getFile are concatenated into a filesystem path below - keep them strictly alphanumeric.
$validJob = $job !== '' && preg_match('/^[A-Za-z0-9]+$/', $job);
$validGet = $getFile !== '' && preg_match('/^[A-Za-z0-9]+$/', $getFile);

if ($hasUploads && $job === '') {
	// step 1: accept two models, run the merge, hand back a job id
	$rnd = md5(uniqid('', true));
	while (is_dir($storage . '/' . $rnd)) $rnd = md5(uniqid('', true));
	$dir = $storage . '/' . $rnd;
	mkdir($dir, 0755, true);
	move_uploaded_file($f1['tmp_name'], $dir . '/f1');
	move_uploaded_file($f2['tmp_name'], $dir . '/f2');

	$readFile1 = file_get_contents($dir . '/f1');
	$readFile2 = file_get_contents($dir . '/f2');

	$commandList = array_values(array_filter(array_map('trim', explode(',', $commands))));
	if (!$commandList) $commandList = array('merge');

	$bivesJob = json_encode(array(
		'files'    => array($readFile1, $readFile2),
		'commands' => $commandList,
	));

	if (callBives($bivesJob, $saveMerge, $BIVES, $dir)) {
		echo $rnd;
	} else {
		http_response_code(502);
		echo "merge failed";
	}
} else if ($validJob && $validGet && is_file($storage . '/' . $job . '/' . $getFile)) {
	// step 2: stream a stored result file back
	$filename = $storage . '/' . $job . '/' . $getFile;
	header("Content-Type: application/xml");
	header("Content-Transfer-Encoding: Binary");
	header("Content-Length: " . filesize($filename));
	header('Content-Disposition: attachment; filename="mergedModel.xml"');
	echo file_get_contents($filename);
} else {
	http_response_code(404);
	if ($job !== '' && !$validJob) echo "invalid job id\n";
	else if ($validJob && !is_dir($storage . '/' . $job)) echo "unknown job id: " . $job . "\n";
	else echo "file not found: " . $getFile . " for job " . $job . "\n";
}

function callBives($bivesJob, $saveMerge, $BIVES, $dir)
{
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $BIVES);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "stats website diff generator");
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $bivesJob);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

	$result = curl_exec($curl);
	if ($result === false) {
		throw new Exception(curl_error($curl), curl_errno($curl));
	}
	curl_close($curl);

	if ($saveMerge) {
		$decoded = json_decode($result);
		$merge = (is_object($decoded) && isset($decoded->merge)) ? $decoded->merge : null;
		if ($merge === null) {
			// BiVeS returned an error or an unexpected payload - keep it for debugging,
			// but do not expose it through the alphanumeric-only getFile route.
			file_put_contents($dir . "/mergedModel.error", $result);
			return false;
		}
		file_put_contents($dir . "/mergedModel", $merge);
	}

	return true;
}
?>
