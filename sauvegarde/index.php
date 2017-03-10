<?php
include("../database.php");
$connection = GetManagerDatabaseConnection();
$tablesRequest ="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE Table_Type='BASE TABLE' AND TABLE_SCHEMA ='Access' AND TABLE_NAME not like '%?%'";
$tables = executeRequest($connection, $tablesRequest);

$date = date("Y-m-d H_i_s");;
$directory = "Sauvegarde Manager {$date}";
mkdir($directory);
foreach ($tables as $table) 
{
	createFile($table[TABLE_NAME], $directory, $connection);
}
zip($directory);
removeFolder($directory);
sendMail($directory.'.zip');
echo "<script>window.close();</script>";

function executeRequest($connection, $request)
{
	$return = odbc_exec($connection, $request);
	$resultArray = array();
	while ( ($row = odbc_fetch_array($return)))
	{
		array_push($resultArray, $row);
	}
	return $resultArray;
}

function createFile($tableName, $directory, $connection)
{
	$file = $directory.'/'.$tableName.".csv";
	$fp = fopen($file, 'w');
	
	$columnsRequest = "select COLUMN_NAME from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME='".$tableName."'";
	$columns = executeRequest($connection, $columnsRequest);
	$columnNames = array();
	foreach($columns as $columnName)
	{
		array_push($columnNames, $columnName[COLUMN_NAME]);
	}
	fputcsv($fp, $columnNames, ";",chr(0));
	
	$dataRequest = "select * from [".$tableName."]";
	$data = executeRequest($connection, $dataRequest);
	foreach ($data as $dataLine) 
	{
		fputcsv($fp, $dataLine, ";", chr(0));
	}
}

function zip($folder)
{
	// Get real path for our folder
	$rootPath = realpath($folder);

	// Initialize archive object
	$zip = new ZipArchive();
	$zip->open($folder.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

	// Create recursive directory iterator
	/** @var SplFileInfo[] $files */
	$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($rootPath),
		RecursiveIteratorIterator::LEAVES_ONLY
	);

	foreach ($files as $name => $file)
	{
		// Skip directories (they would be added automatically)
		if (!$file->isDir())
		{
			// Get real and relative path for current file
			$filePath = $file->getRealPath();
			$relativePath = substr($filePath, strlen($rootPath) + 1);
			// Add current file to archive
			$zip->addFile($filePath, iconv("CP1252", "CP850", $relativePath));
		}
	}

	// Zip archive will be created only after closing object
	$zip->close();
}

function removeFolder ($folder)
{
	$files = glob($folder."/*"); // get all file names
	foreach($files as $file)
	{
		if(is_file($file))
			unlink($file); // delete file
	}
	rmdir($folder);
}

function sendMail($filename)
{
	require_once('../PHPMailer-master/class.phpmailer.php');
	include("../PHPMailer-master/class.smtp.php");

$mail             = new PHPMailer();
$body             = "Sauvegarde";
$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "in-v3.mailjet.com"; // SMTP server
//$mail->SMTPDebug  = 2;                   // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->Host       = "in-v3.mailjet.com";   // sets the SMTP server
$mail->Port       = 25;                    // set the SMTP port for the GMAIL server
$mail->Username   = "46963ea547c00fdcefa591ce2579c2af"; // SMTP account username
$mail->Password   = "f003d7b7fd581a7575c327206a0c899a";        // SMTP account password
$mail->SetFrom('remi.obled@gmail.com', 'Sauvegarde Automatique');
$mail->AddReplyTo("noreply@fermetures-must.fr", "noreply");
$mail->Subject    = "Sauvegarde des donnees de Must Manager du ".date("Y-m-d H:i:s");
$mail->MsgHTML($body);

$mail->AddAddress("ojc.obled@orange.fr", "Jean-Claude Obled");
$mail->AddAddress("remi.obled@gmail.com", "Remi Obled");

$mail->AddAttachment($filename);      // attachment
if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}   
}
?>

