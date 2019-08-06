<?php 
    class DbOperations{
        private $con; 
        function __construct(){
            require_once dirname(__FILE__) . '/DbConnect.php';
            $db = new DbConnect; 
            $this->con = $db->connect(); 
        }
        public function createUser($email, $password, $name, $school){
           if(!$this->isEmailExist($email)){
                $stmt = $this->con->prepare("INSERT INTO users (email, password, name, school) VALUES (?, ?, ?, ?)");
                $stmt->bindParam("ssss", $email, $password, $name, $school);
                if($stmt->execute()){   
                    return USER_CREATED; 
                }else{
                    return USER_FAILURE;
                }
           } 
           return USER_EXISTS; 
        }




        public function updateUser($email, $name, $school, $id){
            $stmt = $this->con->prepare("UPDATE users SET email = ?, name = ?, school = ? WHERE id = ?");
            $stmt->bindParam("sssi", $email, $name, $school, $id);
            if($stmt->execute())
                return true; 
            return false; 
        }
        
        public function userLogin($email, $password){
            if($this->isEmailExist($email)){
                $hased_password = $this->getUsersPasswordByEmail($email);
                if(password_verify($password,$hased_password)){
                        return 201;
                }else{
                        return 203;
                }          
            }
            else{
                return 202;
            }

        }
        
        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM users WHERE email = ?");
            $stmt->bindParam("s", $email);
            $stmt->execute(); 
            $stmt->fetchColumn($password);
            return $password; 
        }

        public function getUserByEmail($email){
            $stmt = $this->con->prepare("SELECT id , email,name,school FROM users WHERE email = ?");
            $stmt->bindParam("s", $email);
            $stmt->execute(); 
            $stmt->fetchColumn($id, $email, $name, $school);
            $user = array(); 
            $user['id'] = $id; 
            $user['email']=$email; 
            $user['name'] = $name; 
            $user['school'] = $school; 
            return $user;
        }
        private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bindParam("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }

        public function updatePassword($currentpassword, $newpassword, $email){
            $hashed_password = $this->getUsersPasswordByEmail($email);
            
            if(password_verify($currentpassword, $hashed_password)){
                
                $hash_password = password_hash($newpassword, PASSWORD_DEFAULT);
                $stmt = $this->con->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->bindParam("ss",$hash_password, $email);
                if($stmt->execute())
                    return PASSWORD_CHANGED;
                return PASSWORD_NOT_CHANGED;
            }else{
                return PASSWORD_DO_NOT_MATCH; 
            }
        }



        public function getAllUsers(){
            $stmt = $this->con->prepare("SELECT id, email, name, school FROM users;");
            // $stmt->fetchColumn($id, $email, $name, $school);
            $stmt->execute(); 
            $users = $stmt->fetchall(PDO::FETCH_ASSOC);             
            return $users; 
        }


    } 
