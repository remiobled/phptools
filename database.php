<?php
function GetManagerDatabaseConnection()
{
return odbc_connect("Driver={SQL Server};
//Add here your connection string
);	
}

?>