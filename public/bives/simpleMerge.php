<?php
 // return "check for update: Version Für Martin";
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
//
error_reporting(E_ALL ^ E_WARNING);

$BIVES = "https://bives.bio.informatik.uni-rostock.de/";
$storage = '/tmp/mergestorage';
$f1 = $_FILES['file1'];
$f2 = $_FILES['file2'];
$job = $_POST['jobID'];
$commands = $_POST['commands'];
$getFile = $_POST['getFile'];

$saveMerge = true;



if (isset($f1) && !empty($f2) && isset($f2) && !empty($f2) && !isset($job)) {
	// save both to $storage
	$rnd = md5(time());
	while (is_dir($storage . '/' . $rnd)) $rnd = md5(time());
	$dir = $storage . '/' . $rnd;
	mkdir($dir, 0755 , true);
	move_uploaded_file($_FILES['file1']['tmp_name'], $dir . '/f1');
	move_uploaded_file($_FILES['file2']['tmp_name'], $dir . '/f2');

	$filename = $dir . '/f1';
	$openFile = fopen($filename, "r");
	$readFile1 = fread($openFile, filesize($filename));
	fclose($openFile);

	$filename = $dir . '/f2';

	$openFile = fopen($filename, "r");
	$readFile2 = fread($openFile, filesize($filename));
	fclose($openFile);

	//build bivesJob and call bives.php
	/*	$bivesJobArr = new \stdClass();
	$bivesJobArr->success = false;
	$bivesJobArr->files = array($readFile1, $readFile2);
	$bivesJobArr->commands = array("merge");
*/
	$bivesJob = json_encode(array(
		'files' => array(
			$readFile1,
			$readFile2
		),
		'commands'=> $commands

	));

	callBives($bivesJob, $saveMerge, $BIVES, $storage, $rnd);

	//echo "mkdir echo: " . file_exists($dir);
	echo $rnd;
} else if (isset($job) && !empty($job) && isset($getFile) && !empty($getFile) && !preg_match('[^A-Za-z0-9]', $job) && file_exists($storage . '/' . $job . '/' . $getFile)) {
	header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
	header("Content-Type: file/xml");
	header("Content-Transfer-Encoding: Binary");
	header("Content-Length:" . filesize($storage . '/' . $job . '/' . $getFile));
	header("Content-Disposition: attachment; filename=mergedModel.xml");

	//echo "\n\nGenau HIER!\n\n\n";

	$filename = $storage . '/' . $job . '/' . $getFile;

	if(!file_exists($filename)) echo "the file does not exist: " . $filename;
	else {
		$openFile = fopen($filename, "r");
		$readFile = fread($openFile, filesize($filename));
		fclose($openFile);	
	
		echo $readFile;
	}


} else {
	if(isset($job) && empty($job)) echo "\n Job set but empty \n";
	if (!file_exists($storage) ) echo "STORAGE does not exist " . $storage;
	if (!file_exists($storage . '/' . $job) ) echo "\nID does not exist " . $storage . '/' . $job . "\n";
	if (!file_exists($storage . '/' . $job . '/' . $getFile)) echo "FILE doesnt exist " . $storage . '/' . $job . '/' . $getFile;
	echo "\n\nFAILED 4---> getFile:" . $getFile . ", job: " . $job;
}

function callBives($bivesJob, $saveMerge, $BIVES, $storage, $job)
{
	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $BIVES);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_AUTOREFERER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_USERAGENT, "stats website diff generator");
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $bivesJob);


	$headers = array();
	$headers[] = 'Content-Type: application/json';
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($curl);
	if ($result === false) {
		throw new Exception(curl_error($curl), curl_errno($curl));
	}
	curl_close($curl);

	if ($saveMerge) {
		$dir = $storage . '/' . $job;
		$decodeResult = json_decode($result)->merge;
		file_put_contents($dir . "/mergedModel", $decodeResult);
	}



	return $result;
}
?>