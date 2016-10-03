<?php
require('config.php');
class MySqlDatabaseClass
{
    //Fields
    private $db_connection;
    //Properties
    public function getDb_connection() { return $this->db_connection; }
    //Constructor
    public function __construct()
    {
        try
        {
            $this->db_connection = new PDO("mysql:host=".SERVERNAME."; dbname=".DATABASENAME, USERNAME, PASSWORD);
            $this->db_connection.setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "De connectie met de server is succesvol";
        }
        catch(PDOException $e)
        {
            echo "De connectie met de server is niet gelukt. Foutmelding: ". $e->getMessage();
        }
    }
    //Methods
    public function fire_query($query)
    {
        try
        {
            $this->db_connection
            }
        catch(PDOException e)
            {
            }
            $result = mysqli_query($this->db_connection, $query);
			return $result;
		}
}
$database = new MySqlDatabaseClass();
?>