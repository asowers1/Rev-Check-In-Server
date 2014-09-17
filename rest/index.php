<?hh

function getStatusCodeMessage($status)
{
    // these could be stored in a .ini file and loaded
    // via parse_ini_file()... however, this will suffice
    $codes = Array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => '(Unused)',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported'
    );
 
    return (isset($codes[$status])) ? $codes[$status] : '';
}
 
// Helper method to send a HTTP response code/message
function sendResponse($status = 200, $body = '', $content_type = 'text/html')
{
    $status_header = 'HTTP/1.1 ' . $status . ' ' . getStatusCodeMessage($status);
    header($status_header);
    header('Content-type: ' . $content_type);
    echo $body;
}


class RestAPI {
    
    // Main method to redeem a code
    private $db;
 
    // Constructor - open DB connection
    function __construct() {
        $this->db = new mysqli('localhost', 'root', 'titan', 'rev');
        $this->db->autocommit(TRUE);
    }
 
    // Destructor - close DB connection
    function __destruct() {
        $this->db->close();
    }
 
    function checkPushID($id){
		if($id!="123"){
			return false;
		}
		return true;
	}

	function cleanVariable($var){
		return stripslashes(strip_tags($var));
	}


    /*
    *	getBeacon
    *
    *	@PUSH_ID REST API key, @uuid the provided uuid for feting beacon credentials
    *
    *	gets all beacons associated with a paticular uuid, saves them to a JSON array, and sends it out via HTTP
    */
    function getBeacon() {
	if (isset($_GET["uuid"])) {
		$uuidIn = $_GET["uuid"];
		$stmt = $this->db->prepare('SELECT * FROM beacon WHERE uuid = ?');
		$stmt->bind_param("s",$uuidIn);
		$stmt->execute();
		$stmt->bind_result($beacon_id,$identifier,$uuid,$major,$minor);
		/* fetch values */
		while ($stmt->fetch()) {
			$output[]=array($beacon_id,$identifier,$uuid,$major,$minor);
		}
	    $stmt->close();
		
		// headers for not caching the results
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 2020 05:00:00 GMT');

		// headers to tell that result is JSON
		header('Content-type: application/json');
		sendResponse(200, json_encode($output));

		return true;
	}
	sendResponse(400, 'Invalid request beacon');
    return false;
    }
    
    /*
    *	setBeacon
    *
    *	@PUSH_ID key for REST, @beacon_id id of beaocn for query, @identifier the text name for the identifier, @uuid beacon uuid, @major beacon major, @minor beacon minor
    *
    *	@return affected rows from update or error associated with the provided beacon
    */
    function setBeacon() {
	    if(isset($_POST["beacon_id"])&&isset($_POST["identifier"])&&isset($_POST["uuid"])&&isset($_POST["major"])&&isset($_POST["minor"])&&isset($_POST["PUSH_ID"])){
			if(!$this->checkPushID($_POST["PUSH_ID"])){
				sendResponse(400, 'Invalid code');
				return false;
			}
		    $beaconIdIn = $_POST["beacon_id"];
		    $uuidIn = $_POST["uuid"];
		    
		    $majorIn = $_POST["major"];
		    $minorIn = $_POST["minor"];
		    
		    $stmt = $this->db->prepare('UPDATE beacon SET uuid=?, major=?, minor=? WHERE beacon_id = ?');
		    $stmt->bind_param('siis',$uuidIn,$majorIn,$minorIn,$beaconIdIn);
		    $stmt->execute(); 
		    // send affected rows, zero if failure 
			sendResponse(200, $stmt->affected_rows);
			$stmt->close();	
			return true;
	    }
	    // send zero; failure
	    sendResponse(400, 0);
	    return false;
    }

    /*
    *	getAllBeacons
    *
    *	@PUSH_ID key for REST
    *
    *	@return JSON object of all beacon rows from push_interactive DB
    */
    function getAllBeacons(){
    	$json;
	    if(isset($_GET["PUSH_ID"])){
		    if(!$this->checkPushID($_GET["PUSH_ID"])){
				sendResponse(400,json_encode($output));
				return false;   
		    }
		    $stmt = $this->db->prepare('SELECT * FROM beacon');
		    $stmt->execute();
			$stmt->bind_result($id,$uuid,$major,$minor,$ident);
			/* fetch values */
			while ($stmt->fetch()) {
				$output[]=array($id,$uuid,$major,$minor,$ident);
			}
		    $stmt->close();	
			// headers for not caching the results
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 2001 05:00:00 GMT');
			// headers to tell that result is JSON
			header('Content-type: application/json');
			sendResponse(200, json_encode($output));
			return true;
	    }
	    sendResponse(400, json_encode($output)); 	
	    return false;
    }

    function getAllUsers(){
    	$json;
	    if(isset($_GET["PUSH_ID"])){
		    if(!$this->checkPushID($_GET["PUSH_ID"])){
				sendResponse(400,json_encode($json));
				return false;   
		    }
		    $stmt = $this->db->prepare('SELECT name, role, email, phone, business_name,(SELECT timestamp FROM user_status WHERE user_id = id ORDER BY timestamp DESC LIMIT 1) as timestamp, (SELECT state FROM user_status WHERE user_id = id ORDER BY timestamp DESC  LIMIT 1) as state FROM user');
		    $stmt->execute();
			$stmt->bind_result($name,$role,$email,$phone,$business_name,$timestamp,$state);
			/* fetch values */
			
			while ($stmt->fetch()) {
				$picture = "http://experiencepush.com/rev/rest/?PUSH_ID=123&call=getUserPicture&username=".$email;
				$output[]=array("username"=>$email,"name"=>$name,"role"=>$role,"email"=>$email,"phone"=>$phone,"picture"=>$picture,"business_name"=>$business_name,"timestamp"=>$timestamp,"state"=>$state);
			}
		    $stmt->close();	
			// headers for not caching the results
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 2001 05:00:00 GMT');
			// headers to tell that result is JSON
			header('Content-type: application/json');
			sendResponse(200, json_encode($output));
			return true;
	    }
	    sendResponse(400, json_encode($output)); 	
	    return false;
    }
    
    function login(){
    	$json;
	    if(isset($_GET["PUSH_ID"])&&isset($_GET["username"])&&isset($_GET["password"])){
		    if(!$this->checkPushID($_GET["PUSH_ID"])){
				sendResponse(400,json_encode($json));
				return false;   
		    }
		    $username = stripslashes(strip_tags($_GET["username"]));
		    $password = md5(stripslashes(strip_tags($_GET["password"])));
		    $stmt = $this->db->prepare('SELECT * FROM user WHERE username = ? AND password = ?');
		    $stmt->execute();
		    $stmt->bind_param("ss",$username,$password);
		    $stmt->execute();
		    $stmt->store_result();
		    $rows = $stmt->num_rows;
		    if($rows>0){
			    sendResponse(200, '1');
			    return true;
		    }
		    sendResponse(400, '-1');
			return false;
		
	    }
	    sendResponse(400, '0'); 	
	    return false;
    }
    
    /*
    *	addNewUser
    *
    *	@super_global_param String: PUSH_ID, String: username, String: password, String: name, String: email, String: role, String: phone, String: code
    *
    *	takes new user credentials and adds new user to the database
    */
    function addNewUser(){
		if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["password"])&&isset($_POST["name"])&&isset($_POST["email"])&&isset($_POST["role"])&&isset($_POST["phone"])&&isset($_POST["code"])){
		    if(!$this->checkPushID($_POST["PUSH_ID"])||$_POST["username"]=="-1"||$_POST["email"]=="-1"){
				sendResponse(400,"-1");
				return false;   
		    }
		    $username = stripslashes(strip_tags($_POST["email"]));
		    $password = md5(stripslashes(strip_tags($_POST["password"])));
		    $email    = stripslashes(strip_tags($_POST["email"]));
		    $name     = stripslashes(strip_tags($_POST["name"]));
		    $phone	  = stripslashes(strip_tags($_POST["phone"]));
		    $role     = stripslashes(strip_tags($_POST["role"]));
		    $code     = stripslashes(strip_tags($_POST["code"]));
		    //$device   = $this->cleanVariable($_POST["device_id"]);
		    $business = '';

		    $stmt = $this->db->prepare("SELECT business_name FROM business WHERE business_code = ?");
		    $stmt->bind_param("i",$code);
		    $stmt->execute();
		   	$stmt->bind_result($business_name);
		   	if($stmt->fetch()){
		   		$business = $business_name;
		   	}else{
		   		sendResponse(400,"-2");
		   		return false;
		   	}
		   	$stmt->close();
		   	
		    $stmt = $this->db->prepare('SELECT * FROM user WHERE username = ? OR name = ? OR email = ?');
		    $stmt->bind_param("sss",$username,$name,$email);
		    $stmt->execute();
		    if($stmt->fetch()){
		    	sendResponse(400,'-1');
		    	return false;
		    }
		    $stmt->close();
		    $stmt = $this->db->prepare('INSERT INTO user (username,password,name,email,business_name,role,phone) values(?,?,?,?,?,?,?)');
		    $stmt->bind_param("sssssss",$username,$password,$name,$email,$business,$role,$phone);
		    $stmt->execute();

		    $stmt = $this->db->prepare('INSERT INTO user_status (state,user_id) values (0,(select id from user where username = ?))');
		    $stmt->bind_param("s",$username);
		    $stmt->execute();

		    // check old user device token
		    /*
			$stmt = $this->db->prepare('SELECT device_id FROM user_device WHERE user_device = ?');
		    $stmt->bind_param("s",$device);
		    $stmt->execute();
			$stmt->bind_result($user_device);

			if ($stmt->fetch()) {
				//sendResponse(200,"test");
				//return true;
				$stmt->close();
				$stmt = $this->db->prepare('DELETE FROM user_device WHERE device_id = ?');
				
				$stmt->bind_param("s",$device_id);

				$stmt->execute();
			}
			$stmt->close();

		    $stmt = $this->db->prepare('INSERT INTO user_device (user_id,device_id) values((SELECT id from user where username = ?),?)');
		    $stmt->bind_param("ss",$username,$device);
		    $stmt->execute();
		    */


			sendResponse(200, '1');
			return true;
	    }
	    sendResponse(400, '0'); 	
	    return false;
    }

    /*
    *	linkDeviceToUser
    *
    *	@super_gloabl_param String: PUSH_ID, String: username, String: device
    *
    *	links an iOS device APNS identifier to a user and writes changes to the database
    */
    function linkDeviceToUser(){
    	if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["device_id"])){
    		if(!$this->checkPushID($_POST["PUSH_ID"])){
				sendResponse(400,"-1");
				return false;   
		    }
		    $username = $this->cleanVariable($_POST["username"]);
		    $device   = $this->cleanVariable($_POST["device_id"]);

		    //$stmt = $this->db->prepare("UPDATE user_device SET device_id = ? WHERE user_id = (SELECT id FROM user WHERE username = ?)");
		    $stmt = $this->db->prepare("SELECT * FROM user_device WHERE device_id = ? AND user_id = (SELECT id FROM user WHERE username = ?)");
		    $stmt->bind_param("ss",$device,$username);
		    $stmt->execute();
		    
		    if($stmt->fetch()){
		    	sendResponse(400,"-2");
		    	return false;
		    }
		    $stmt->close();
		    
		    $stmt = $this->db->prepare('INSERT INTO user_device (user_id,device_id) VALUES((SELECT id from user where username = ?),?)');
		    $stmt->bind_param("ss",$username,$device);
		    $stmt->execute();
		    sendResponse(200, '1');
		    return true;
    	}
    	sendResponse(400, '0');
    	return false;
    }

    /*
    *	updateUserState
    *
    *	@super_global_param String: PUSH_ID, String: username, String: state
    *
    *	assosiates new state with specified user and writes changed state to the database
    */
    function updateUserState(){
     	if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["state"])&&isset($_POST["appTimestamp"])){
    		if(!$this->checkPushID($_POST["PUSH_ID"])){
				sendResponse(400,"-1");
				return false;   
		    }
			$username     = $this->cleanVariable($_POST["username"]);
		    $state        = $this->cleanVariable($_POST["state"]);
		    $appTimestamp = $this->cleanVariable($_POST["appTimestamp"]);

		    $stmt = $this->db->prepare("SELECT (SELECT state FROM user_status WHERE user_id = id ORDER BY timestamp DESC  LIMIT 1) as state FROM user WHERE username = ?");
		    $stmt->bind_param("s",$username);
		    $stmt->execute();
		    $stmt->bind_result($oldState);
		    if($stmt->fetch()){
		    	if($oldState == $state){
		    		sendResponse(200,"-2");
		    		return false;
		    	}
		    }
		    $stmt->close();

		    $stmt = $this->db->prepare("INSERT INTO user_status (state, user_id, appTimestamp) VALUES (?,(SELECT id FROM user WHERE username = ?),?)");
		    $stmt->bind_param("sss",$state,$username,$appTimestamp);
		    $stmt->execute();
		    $stmt->close();

		    $stmt = $this->db->prepare("SELECT device_id FROM user_device WHERE device_id != '0' AND  device_id != '-1'");
		    $stmt->execute();
		    $stmt->bind_result($device_id);
		    while($stmt->fetch()){
				if($state == "1"){
		    		$notification = $username . " has checked in";
		    		//echo $notification . " ". $device_id."\n";
		    		//exec("test.php");
		    	}else if($state == "0"){
		    		$notification = $username . " has checked out";
		    		//echo $notification . " ". $device_id."\n";
		    		//exec("test.php");
		    		
		    	}
		    	
		    }
		    sendResponse(200, '1');
		    return true;
    	}
    	sendResponse(400, '0');
    	return false;
    }

    /*
    *	setUserPicture
	*
	*	@super_global_param String: PUSH_ID, String: username, BLOB: uploadedfile
	*
	*	takes an image and assosiates it to a user in the database
	*/
    function setUserPicture(){
    	if(isset($_GET["PUSH_ID"])&&isset($_GET["username"])){
    		if(!$this->checkPushID($_GET["PUSH_ID"])){
    			sendResponse(400,'-1');
    			return false;
    		}
    		$username = stripslashes(strip_tags($_GET["username"]));
			$uploaddir = '/usr/share/nginx/html/rev/img/'; 
			$file = basename($_FILES['uploadedfile']['name']);
			$uploadfile = $uploaddir . $file;
			$stmt = $this->db->prepare("UPDATE user SET picture = ? WHERE username = ?");
			$null = NULL;
			$stmt->bind_param("bs",$null,$username);
			if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
				$stmt->send_long_data(0, file_get_contents($uploadfile));
				$stmt->execute();
				unlink($uploadfile);
				sendResponse(200,"1");
				return true;
			}
			sendResponse(400,"-1");
    		return false;
    	}
    	sendResponse(400,"0");
    	return false;
    }

    /*
    *	setBusinessPicture
	*
	*	@super_global_param String: PUSH_ID, String: username, BLOB: uploadedfile
	*
	*	takes an image and assosiates it to a user in the database
	*/
    function setBusinessPicture(){
     	if(isset($_GET["PUSH_ID"])&&isset($_GET["username"])){
    		if(!$this->checkPushID($_GET["PUSH_ID"])){
    			sendResponse(400,'-1');
    			return false;
    		}
    		$username = stripslashes(strip_tags($_GET["username"]));
			$uploaddir = '/usr/share/nginx/html/rev/img/'; 
			$file = basename($_FILES['uploadedfile']['name']);
			$uploadfile = $uploaddir . $file;
			$stmt = $this->db->prepare("UPDATE business SET picture = ? WHERE business_name = (SELECT business_name q FROM user WHERE username = ?)");
			$null = NULL;
			$stmt->bind_param("bs",$null,$username);
			if (move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $uploadfile)) {
				$stmt->send_long_data(0, file_get_contents($uploadfile));
				$stmt->execute();
				unlink($uploadfile);
				sendResponse(200,"1");
				return true;
			}
			sendResponse(400,"-2");
    		return false;
    	}
    	sendResponse(400,"0");
    	return false;
    }	

   	/*
   	*	getUserPicture
   	*
   	*	@super_global_param String: PUSH_ID, String: username
   	*
   	*	gets the picture assosiated with a user from the database
   	*/
   	function getUserPicture(){
   		if(isset($_GET["PUSH_ID"])&&isset($_GET["username"])){
    		if(!$this->checkPushID($_GET["PUSH_ID"])){
    			sendResponse(400,'-1');
    			return false;
    		}
    		$username = stripslashes(strip_tags($_GET["username"]));
    		$stmt = $this->db->prepare("SELECT picture FROM user WHERE username = ?");
    		$stmt->bind_param("s",$username);
			$stmt->execute();
			$stmt->store_result();

			$stmt->bind_result($picture);
			if($stmt->fetch()){
				header("Content-Type: image/png");
				echo $picture; 
				return true;
			}
    		sendResponse(400,"-1");
    		return false;
   		}
   		sendResponse(400,'0');
   		return false;
   	}

   	/*
   	*	getBusinessPicture
   	*
   	*	@super_global_param String: PUSH_ID, String: business_name
   	*
   	*	gets the business picture associated with a business_name	
   	*/
   	function getBusinessPicture(){
   		if(isset($_GET["PUSH_ID"])&&isset($_GET["username"])){
   			if(!$this->checkPushID($_GET["PUSH_ID"])){
  				sendResponse(400,'-1');
    			return false;
    		}
   			$username = stripslashes(strip_tags($_GET["username"]));
   			$stmt = $this->db->prepare("SELECT picture FROM business WHERE business_name = (SELECT business_name q FROM user WHERE username = ?)");
   			$stmt->bind_param("s",$username);
   			$stmt->execute();
   			$stmt->store_result();
   			$stmt->bind_result($picture);
   			if($stmt->fetch()){
				header("Content-Type: image/png");
				echo $picture; 
				return true;
   			}
   			sendResponse(400,"-1");
   			return false;
   		}
   		sendResponse(400,"0");
   		return false;
   	}

   	/*
   	*	getUserDeviceTokens
   	*
   	*
   	*
   	*	DO NOT USE FOR PRODUCTION PURPOSES - potential personal data leak
   	*/
   	function getUserDeviceTokens(){
 		$json;
	    if(isset($_GET["PUSH_ID"])){
		    if(!$this->checkPushID($_GET["PUSH_ID"])){
				sendResponse(400,json_encode($json));
				return false;   
		    }
		    $stmt = $this->db->prepare('SELECT (SELECT username FROM user WHERE id = user_device.user_id), device_id FROM user_device');
		    $stmt->execute();
			$stmt->bind_result($username,$device_id);
			/* fetch values */
			
			while ($stmt->fetch()) {
				$output[]=array("username"=>$username,"device_id"=>$device_id);
			}
		    $stmt->close();	
			// headers for not caching the results
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 2001 05:00:00 GMT');
			// headers to tell that result is JSON
			header('Content-type: application/json');
			sendResponse(200, json_encode($output));
			return true;
	    }
	    sendResponse(400, json_encode($output)); 	
	    return false;  		
   	}

   	/*
   	*	updateUserPassword
   	*
   	*	@super_global_param String: username, String: oldPassword, String newPassword
   	*
   	*	updates a user's password on the database
   	*/
   	function updateUserPassword(){
   		if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["oldPassword"])&&isset($_POST["newPasswordCheck"])&&isset($_POST["newPassword"])){
   			if(!$this->checkPushID($_POST["PUSH_ID"])){
  				sendResponse(400,'-1');
    			return false;
    		}
   			$username    	  = $this->cleanVariable($_POST["username"]);
   			$oldPassword 	  = md5($this->cleanVariable($_POST["oldPassword"]));
   			$newPasswordCheck = md5($this->cleanVariable($_POST["newPasswordCheck"]));
   			$newPassword 	  = md5($this->cleanVariable($_POST["newPassword"]));
   			if($newPasswordCheck!=$newPassword){
   				sendResponse(400,"-2");
   				return false;
   			}
   			$stmt = $this->db->prepare("UPDATE user SET password = ? WHERE username = ?");
   			$stmt->bind_param("ss",$newPassword,$username);
   			$stmt->execute();
   			sendResponse(200,"1");
   			return true;
   		}
   		sendResponse(400,"0");
   		return false;
   	}

   	/*
   	*	updateNameRolePhone
   	*
   	*	@super_global_param String:name, String: role, String: Phone
   	*
   	*	updates the name, role, and or phone in the database
   	*/
   	function updateNameRolePhone(){
   		if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["name"])&&isset($_POST["role"])&&isset($_POST["phone"])){
   			if(!$this->checkPushID($_POST["PUSH_ID"])){
  				sendResponse(400,'-1');
    			return false;
    		}
   			$username = $this->cleanVariable($_POST["username"]);
   			$name 	  = $this->cleanVariable($_POST["name"]);
   			$role 	  = $this->cleanVariable($_POST["role"]);
   			$phone 	  = $this->cleanVariable($_POST["phone"]);
   			$stmt = $this->db->prepare("UPDATE user SET name = ?, role = ?, phone = ? WHERE username = ?");
   			$stmt->bind_param("ssss",$name,$role,$phone,$username);
   			$stmt->execute();
   			sendResponse(200,"1");
   			return true;
   		}
   		sendResponse(400,"0");
   		return false;   		
   	}

   	/*
   	*	updateComanyBio
   	*
   	*	@super_global_param String: PUSH_ID, String: username, String: companyBio
   	*
   	*	updates the company bio that a user tied to in the database
   	*/
   	function updateComanyBio(){
   		if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["companyBio"])){
   			if(!$this->checkPushID($_POST["PUSH_ID"])){
  				sendResponse(400,'-1');
    			return false;
    		}
   			
   			$username   = $this->cleanVariable($_POST["username"]);
   			$companyBio = $this->cleanVariable($_POST["companyBio"]);
   			
   			$stmt = $this->db->prepare("UPDATE business SET bio = ? WHERE business_name = (SELECT business_name FROM user WHERE username = ?)");
   			$stmt->bind_param("ss",$companyBio,$username);
   			$stmt->execute();
   			sendResponse(200,"1");
   			return true;
   		}
   		sendResponse(400,"0");
   		return false;  
   	}

   	function getCompanyBio(){
	    if(isset($_GET["PUSH_ID"])&&isset($_GET["username"])){
		    if(!$this->checkPushID($_GET["PUSH_ID"])){
				sendResponse(400,"-1");
				return false;   
		    }
		    $username = $this->cleanVariable($_GET["username"]);
		    $stmt = $this->db->prepare('SELECT bio FROM business WHERE business_name = (SELECT business_name FROM user WHERE username = ?)');
		    $stmt->bind_param("s",$username);
		    $stmt->execute();
			$stmt->bind_result($companyBio);
			/* fetch values */
			if ($stmt->fetch()) {
				$output = $companyBio;
			}
		    $stmt->close();	
			// headers for not caching the results
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 2001 05:00:00 GMT');

			sendResponse(200, $output);
			return true;
	    }
	    sendResponse(400, "0"); 	
	    return false;  		
   	}

   	/*
   	*	removeUserDeviceId
   	*
   	*	@super_global_param String: $_POST["PUSH_ID"], String: $_POST["username"], String: $_POST["device_id"]
   	*
   	*	removes the a device_id token for APNS from database
   	*/
   	function removeUserDeviceId(){
   		if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["device_id"])){
   			if(!$this->checkPushID($_POST["PUSH_ID"])){
   				sendResponse(400,"-1");
   				return false;
   			}
   			$username  = $this->cleanVariable($_POST["username"]);
   			$device_id = $this->cleanVariable($_POST["device_id"]);

   			$stmt = $this->db->prepare('SELECT id FROM user WHERE username = ?');
		    $stmt->bind_param("s",$username);
		    $stmt->execute();
			$stmt->bind_result($user_id);

			/* check for result */
			if ($stmt->fetch()) {
				//sendResponse(200,"test");
				//return true;
				$stmt->close();
				$stmt = $this->db->prepare('DELETE FROM user_device WHERE device_id = ? AND user_id = (SELECT id FROM user WHERE username = ?)');
				
				$stmt->bind_param("ss",$device_id,$username);

				$stmt->execute();
				sendResponse(200,"1");
				return true;
			}
			sendResponse(400,"-2");
			return false;
   		}
   		sendResponse(400,"0");
   		return false;
   	}



    // end of RestAPI class
}
 
// This is the first thing that gets called when this page is loaded
// Creates a new instance of the RedeemAPI class and calls the redeem method
$api = new RestAPI;
if(isset($_REQUEST["call"])){
	$api->$_REQUEST["call"]();
}else{
	sendResponse(400,"call missing");
}

