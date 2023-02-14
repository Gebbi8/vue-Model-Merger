<?php
    error_reporting(E_ALL ^ E_WARNING);

    $BIVES = "https://bives.bio.informatik.uni-rostock.de/";
    //$storage = '/tmp/mergestorage';
    $f1 = $_FILES['file1'];
    $f2 = $_FILES['file2'];
    //$job = $_GET['jobID'];
    $commands = $_GET['commands'];
    $getFile = $_GET['getFile'];

    $saveMerge = true;


    $bivesJob = json_encode(array(
		'files' => array(
			$readFile1,
			$readFile2
		),
		'commands'=> $commands

	));

	callBives($bivesJob, $BIVES);

    function callBives($bivesJob, $BIVES){
        
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



        return $result;
    }

?>