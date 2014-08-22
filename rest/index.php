<?hh
function getStatusCodeMessage($status)
{
    // these could be stored in a .ini file and loaded
    // via parse_ini_file()... however, this will suffice
    // for an example
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

    /*
    *	getBeacon
    *
    *	@PUSH_ID REST API key, @uuid the provided uuid for feting beacon credentials
    *
    *
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
		    $stmt = $this->db->prepare('SELECT username, name, role, email, phone, business_name, (SELECT timestamp FROM user_status WHERE user_id = (SELECT id FROM user WHERE username = username ) LIMIT 1), (SELECT state FROM user_status WHERE user_id = (SELECT id FROM user WHERE username = username ) LIMIT 1) FROM user');
		    $stmt->execute();
			$stmt->bind_result($username,$name,$role,$email,$phone,$business_name,$timestamp,$state);
			/* fetch values */
			while ($stmt->fetch()) {
				$output[]=array($username,$name,$role,$email,$phone,$business_name,$timestamp,$state);
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
    
    function addNewUser(){
		if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["password"])&&isset($_POST["name"])&&isset($_POST["email"])&&isset($_POST["role"])&&isset($_POST["phone"])&&isset($_POST["code"])){
		    if(!$this->checkPushID($_POST["PUSH_ID"])){
				sendResponse(400,json_encode($json));
				return false;   
		    }
		    $username = stripslashes(strip_tags($_POST["username"]));
		    $password = md5(stripslashes(strip_tags($_POST["password"])));
		    $email    = stripslashes(strip_tags($_POST["email"]));
		    $name     = stripslashes(strip_tags($_POST["name"]));
		    $phone	  = stripslashes(strip_tags($_POST["phone"]));
		    $role     = stripslashes(strip_tags($_POST["role"]));
		    $code     = stripslashes(strip_tags($_POST["code"]));
		    $business = '';

		    $stmt = $this->db->prepare("SELECT business_name FROM business WHERE business_code = ?");
		    $stmt->bind_param("i",$code);
		    $stmt->execute();
		   	$stmt->bind_result($business_name);
		   	if($stmt->fetch()){
		   		$business = $business_name;
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

		    $stmt = $this->db->prepare('INSERT INTO user_device (user_id,device_id) values((SELECT id from user where username = ?),0)');
		    $stmt->bind_param("s",$username);
		    $stmt->execute();



			sendResponse(200, '1');
			return true;
	    }
	    sendResponse(400, '0'); 	
	    return false;
    }

    function linkDeviceToUser(){
    	if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["device"])){
    		if(!$this->checkPushID($_POST["PUSH_ID"])){
				sendResponse(400,"-1");
				return false;   
		    }
		    $username = stripslashes(strip_tags($_POST["username"]));
		    $device   = stripslashes(strip_tags($_POST["device"]));
		    $stmt = $this->db->prepare("UPDATE user_device SET device_id = ? WHERE user_id = (SELECT id FROM user WHERE username = ?)");
		    $stmt->bind_param("ss",$device,$username);
		    $stmt->execute();
		    sendResponse(200, '1');
		    return true;
    	}
    	sendResponse(400, '0');
    	return false;
    }

    function updateUserState(){
     	if(isset($_POST["PUSH_ID"])&&isset($_POST["username"])&&isset($_POST["state"])){
    		if(!$this->checkPushID($_POST["PUSH_ID"])){
				sendResponse(400,"-1");
				return false;   
		    }
		    $username = stripslashes(strip_tags($_POST["username"]));
		    $state    = stripslashes(strip_tags($_POST["state"]));
		    $stmt = $this->db->prepare("UPDATE user_status SET checked_in = ? WHERE user_id = (SELECT id FROM user WHERE username = ?)");
		    $stmt->bind_param("ss",$state,$username);
		    $stmt->execute();
		    sendResponse(200, '1');
		    return true;
    	}
    	sendResponse(400, '0');
    	return false;
    }
	
    // end of RestAPI class
}
 
// This is the first thing that gets called when this page is loaded
// Creates a new instance of the RedeemAPI class and calls the redeem method
$api = new RestAPI;
$function = $_REQUEST["call"];
$api->$function();

