<?php
class db
{
    protected $connection;

    function setconnection()
    {
        try {
            // Make sure database name is correct and no trailing spaces
            $this->connection = new PDO("mysql:host=localhost;port=3307;dbname=library_management_system", "root", "");
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connectin Done";
        } catch (PDOException $e) {
            // echo "Error";
        }
    }
}
