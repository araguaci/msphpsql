--TEST--
queries a call to procedure with input and output parameters.
--SKIPIF--
<?php require('skipif.inc'); ?>
--FILE--
<?php
/* Connect to a server using Windows Authentication and 
specify the AdventureWorks database as the database in use. */
require('connect.inc');
$connectionInfo = array( "Database"=>"$databaseName", "UID"=>"$uid", "PWD"=>"$pwd");
$conn = sqlsrv_connect( $server, $connectionInfo);
if( $conn === false )
{
     echo "Could not connect.\n";
     die( print_r( sqlsrv_errors(), true));
}

/* Drop the stored procedure if it already exists. */
dropProc($conn, 'GetEmployeeSalesYTD');

/* Create the stored procedure. */
$tsql_createSP = " CREATE PROCEDURE GetEmployeeSalesYTD
                   @SalesPerson nvarchar(50),
                   @SalesYTD money OUTPUT
                   AS
                   SELECT @SalesYTD = SalesYTD
                   FROM Sales.SalesPerson AS sp
                   JOIN HumanResources.vEmployee AS e 
                   ON e.BusinessEntityID = sp.BusinessEntityID
                   WHERE LastName = @SalesPerson";
$stmt = sqlsrv_query( $conn, $tsql_createSP);
if( $stmt === false )
{
     echo "Error in executing statement 2.\n";
     die( print_r( sqlsrv_errors(), true));
}

/*--------- The next few steps call the stored procedure. ---------*/

/* Define the Transact-SQL query. Use question marks (?) in place of
 the parameters to be passed to the stored procedure */
$tsql_callSP = "{call GetEmployeeSalesYTD( ?, ? )}";

/* Define the parameter array. By default, the first parameter is an
INPUT parameter. The second parameter is specified as an OUTPUT
parameter. Initializing $salesYTD to 0.0 sets the returned PHPTYPE to
float. To ensure data type integrity, output parameters should be
initialized before calling the stored procedure, or the desired
PHPTYPE should be specified in the $params array.*/
$lastName = "Blythe";
$salesYTD = 0.0;
$params = array( 
                 array(&$lastName, SQLSRV_PARAM_IN),
                 array(&$salesYTD, SQLSRV_PARAM_OUT)
               );

/* Execute the query. */
$stmt = sqlsrv_query( $conn, $tsql_callSP, $params);
if( $stmt === false )
{
     echo "Error in executing statement 3.\n";
     die( print_r( sqlsrv_errors(), true));
}

/* Display the value of the output parameter $salesYTD. */
echo "YTD sales for ".$lastName." are ". $salesYTD. ".";

dropProc($conn, 'GetEmployeeSalesYTD', false);

/*Free the statement and connection resources. */
sqlsrv_free_stmt( $stmt);
sqlsrv_close( $conn);
?>
--EXPECT--
YTD sales for Blythe are 3763178.1787.