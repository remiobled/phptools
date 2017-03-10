<html>
<head></head>
<body>
<form action="index.php" method="post">
<select id='extractionType' name='type' onchange="myFunction()">
  <option value="null"></option>
  <option value="courrier">Courrier</option>
  <option value="courriel">Courriel</option>
  <option value="sms">Sms</option>
</select>
<button name='exporter'>Exporter</button>
</form>
<p id="desc"></p>

<script>
function myFunction() {
    
	var e = document.getElementById("extractionType");
	var extractionType = e.options[e.selectedIndex].value;
	if(extractionType=="courrier"){
		document.getElementById("desc").innerHTML = "Extraction des clients ayant la case mailing cochée.";
		}
		else if(extractionType=="courriel"){
		document.getElementById("desc").innerHTML = "Extraction des clients ayant au moins une adresse mail renseignée.";
		}
		else if(extractionType=="sms"){
		document.getElementById("desc").innerHTML = "Extraction des clients ayant au moins un numéro de mobile renseigné et la case mailing cochée.";
		}
	
}
</script>

</body>
</html>

<?php
if(isset($_POST['exporter'])){
$type = $_POST['type'];
$time = time();
$file = "{$type}-{$time}.csv";
include("../database.php");
$connection = GetManagerDatabaseConnection();

$request ="SELECT (Select Intitulé from Civilité where [N°] = Civilité) as Civilité, Nom, Prénom, 
[Numéro de rue], (Select Intitulé from [Type de voie] where [N°] = [Type de voie]) as [Type de Voie], [Voie], (Select Intitulé from [Lotissement] where [N°] = [Lotissement]) as Lotissement, 
Localisation.[Code postal], Localisation.Ville, (Select Intitulé from Pays where [N°] = Localisation.Pays) as Pays, [Courriel Madame], [Courriel Monsieur], [Mobile Madame],[Mobile Monsieur] FROM Client, 
Localisation where Client.Localisation=Localisation.[N°] ";
switch ($type) {
    case "courrier":
        $request = $request." and Mailing=1";
        break;
    case "courriel":
        $request = $request." and ([Courriel Madame] is not null or [Courriel Monsieur] is not null)";
        break;
    case "sms":
        $request = $request." and ([Mobile Madame] is not null or [Mobile Monsieur] is not null) and Mailing=1";
        break;
}
$return = odbc_exec($connection, $request);

$resultArray = array();
while ( ($row = odbc_fetch_array($return)))
{
    array_push($resultArray, $row);
}

$header = array('Civilité', 'Nom', 'Prénom', 'Numéro de rue', 'Type de Voie', 'Voie', 'Lotissement', 'Code postal', 'Ville', 'Pays', 'Courriel Madame', 
'Courriel Monsieur', 'Mobile Madame', 'Mobile Monsieur');

$fp = fopen($file, 'w');

fputcsv($fp, $header, ";",chr(0));
foreach ($resultArray as $lines) 
{
	fputcsv($fp, $lines, ";", chr(0));
}
if (file_exists($file)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'.basename($file).'"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
	ob_clean();
	flush();
	readfile($file);
}
}
?>