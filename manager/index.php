<!DOCTYPE html>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>MustManager - Edition</title>
</head></html>
<?php
$time = time();
$document = $_GET['document'];
$id = $_GET['id'];

if($document == '')
{
if ($handle = opendir("C:\PDFManager")) 
{
    while (false !== ($entry = readdir($handle))) 
{
	if ($entry != "." && $entry != "..")
		echo "$entry"."</br>";
}
    closedir($handle);
}
}

$docBase = "C:\PDFManager\\{$document}.docm";
$docCopy = "C:\PDFManager\\{$document}{$id}-{$time}.docm";

if (!copy($docBase, $docCopy)) {
    echo "La copie $docBase du fichier a échoué...\n";
}
try
{
$word=new COM("word.application") or die("Cannot start word for you");
$PDFFile ="C:\PDFManager\\{$document}{$id}.pdf";
$ArchivePDFFile ="C:\PDFManager\archive\\{$document}{$id}.pdf";

$word->visible = 1;
$word->Documents->Open($docCopy);
$word->Run("Main",$id);
$word->Documents->Close();
$word->Quit();
$word = null;

if (file_exists($PDFFile)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($PDFFile).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($PDFFile));
    readfile($PDFFile);
	rename($PDFFile, $ArchivePDFFile); 
}
}
catch(Exception $e)
{
	echo "Probleme avec Word, re-essayez.";
	echo 'Exception reçue : ',  $e->getMessage(), "\n";
	shell_exec('taskkill /f /im winword.exe');
	shell_exec('taskkill /f /im OfficeClickToRun.exe');
	shell_exec('taskkill /f /im OfficeC2RClient.exe');
	shell_exec('taskkill /f /im winword.exe');
	shell_exec('taskkill /f /im OfficeClickToRun.exe');
	shell_exec('taskkill /f /im OfficeC2RClient.exe');
}
?>