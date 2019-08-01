<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


require '../vendor/autoload.php';

require '../includes/DbOperations.php';

$app = new \Slim\App(['settings'=>['displayErrorDetails'=>true]]);

$app->post('/createuser', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('email', 'password', 'name', 'school'), $response)){
        $request_data = $_REQUEST; 
        $email = $request_data['email'];
        $password = $request_data['password'];
        $name = $request_data['name'];
        $school = $request_data['school']; 
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $db = new DbOperations; 
        $result = $db->createUser($email, $hash_password, $name, $school);
        
        if($result == USER_CREATED){
            $message = array(); 
            $message['error'] = false;  
            $message['message'] = 'User created successfully';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);
        }
        
        else if($result == USER_FAILURE){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
        
        else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'User Already Exists';
            $response->write(json_encode($message));
            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
                        
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});


$app->post('/userlogin', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('email', 'password'), $response)){
        $request_data = $_REQUEST; 
        $email = $request_data['email'];
        $password = $request_data['password'];
        
        $db = new DbOperations; 
        $result = $db -> userLogin($email, $password);
        if($result == 201){
            $user = $db-> getUserByEmail($email);
            $response_data = array();
            $response_data['error']=false; 
            $response_data['message'] = 'Login Successful';
            $response_data['user']=$user; 
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200); 

        }
        
        else if($result == 202){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'User not exist';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    
        }else if($result == 203){
            $response_data = array();
            $response_data['error']=true; 
            $response_data['message'] = 'Invalid credential';
            $response->write(json_encode($response_data));
            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});




$app->put('/updateuser/{id}', function(Request $request, Response $response, array $args){
    $id = $args['id'];
    if(!haveEmptyParameters(array('email','name','school'), $response)){
        $request_data = $_REQUEST; 
        $email = $request_data['email'];
        $name = $request_data['name'];
        $school = $request_data['school']; 
     
        $db = new DbOperations; 
        if($db->updateUser($email, $name, $school, $id)){
            $response_data = array(); 
            $response_data['error'] = false; 
            $response_data['message'] = 'User Updated Successfully';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  

            
        
        }else{
            $response_data = array(); 
            $response_data['error'] = true; 
            $response_data['message'] = 'Please try again later';
            $user = $db->getUserByEmail($email);
            $response_data['user'] = $user; 
            $response->write(json_encode($response_data));
            return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);  
              
        }
    }
    
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
});


$app->get('/allusers', function(Request $request, Response $response){
    $db = new DbOperations; 
    $users = $db -> getAllUsers();
    $response_data = array();
    $response_data['error'] = false; 
    $response_data['users'] = $users; 
    $response->write(json_encode($response_data));
    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  
});




$app->put('/updatepassword', function(Request $request, Response $response){
    if(!haveEmptyParameters(array('currentpassword', 'newpassword', 'email'), $response)){
        
        $request_data = $_REQUEST; 
        $currentpassword = $request_data['currentpassword'];
        $newpassword = $request_data['newpassword'];
        $email = $request_data['email']; 
        $db = new DbOperations; 
        $result = $db->updatePassword($currentpassword, $newpassword, $email);
        if($result == PASSWORD_CHANGED){
            $response_data = array(); 
            $response_data['error'] = false;
            $response_data['message'] = 'Password Changed';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }else if($result == PASSWORD_DO_NOT_MATCH){
            $response_data = array(); 
            $response_data['error'] = true;
            $response_data['message'] = 'You have given wrong password';
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }else if($result == PASSWORD_NOT_CHANGED){
            $response_data = array(); 
            $response_data['error'] = true;
            $response_data['message'] = 'Some error occurred';
            
            $response->write(json_encode($response_data));
            return $response->withHeader('Content-type', 'application/json')
                            ->withStatus(200);
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);  
});


function haveEmptyParameters($required_params, $response){
        $error = false;
        $error_params = '';
        $request_params = $_REQUEST;
    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true;
            $error_params .= $param.',';

        }

    } 

    if($error){
        $error_detail = array();
        $error_detail['error']= true;
        $error_detail ['message']='Required Parameters '.substr($error_params, 0, -1).' are missing';
        $response-> write(json_encode($error_detail));
    }
    return $error;

}
$app->run();

