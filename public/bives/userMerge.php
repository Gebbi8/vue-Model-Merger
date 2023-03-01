<?php
    error_reporting(E_ALL ^ E_WARNING);

/*     foreach ($_POST as $key => $value) {
        echo "<tr>";
        echo "<td>";
        echo $key;
        echo "</td>";
        echo "<td>";
        echo $value;
        echo "</td>";
        echo "</tr>";
    } */

    $BIVES = "https://bives.bio.informatik.uni-rostock.de/";
    //$storage = '/tmp/mergestorage';
    $f1 = $_FILES['file1'];
    print_r($f1);
    echo "\n        get contents:   ";
    echo file_get_contents($f1);
   /*  $openFile = fopen($f1, "r");
	$readFile1 = fread($openFile, filesize($f1));
	fclose($openFile); */

    $f2 = $_FILES['file2'];
    /* $openFile = fopen($f2, "r");
	$readFile2 = fread($openFile, filesize($f2));
	fclose($openFile); */
    //$job = $_GET['jobID'];
    $c = $_POST['commands'];
    //echo "commands: " . $commands;
    $commands = explode(',', $c);
    //$getFile = $_GET['getFile'];

    $saveMerge = true;


    $bivesJob = json_encode(array(
		'files' => array($f1, $f2),
		'commands'=> array($commands[0], $commands[1])
	));

    echo "0.3 ";

    if (isset($f1) && !empty($f1) && isset($f2) && !empty($f2) && isset($commands)) {
	    echo callBives($bivesJob, $BIVES);
    } else {
        echo "FAILED \n
              f1 set: " . isset($f1) . ", not empty: " . !empty($f1) .
              "\n
              f2 set: " . isset($f2) . ", not empty: " . !empty($f2) .
              "\n 
              commads: " . $commands;
    }

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