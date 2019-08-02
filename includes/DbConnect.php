<?php 
    class DbConnect{
        private $con; 
        function connect(){
            include_once dirname(__FILE__)  . '/Constants.php';
            $dsn = "pgsql:host=" . DB_HOST . ";port=" . "5432" .";dbname=" . DB_NAME. ";user=" . DB_USER . ";password=" . DB_PASSWORD. ";";

            $this->con = new PDO($dsn, DB_USER, DB_PASSWORD);
            if(mysqli_connect_errno()){
                echo "Failed  to connect " . mysqli_connect_error(); 
                return null; 
            }
            return $this->con; 
        }
    }
