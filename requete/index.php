<html>
<head></head>
<body>
<form action="index.php" method="post">
<input type="text" name="query" id="query" size=100/><br>
 
<button name='run'>Lancer</button>
</form>

</body>
</html>

<?php
if(isset($_POST['run'])){
$request = $_POST['query'];
include("../database.php");
$connection = GetManagerDatabaseConnection();

$return = odbc_exec($connection, $request);
odbc_result_all($return);

}
?>