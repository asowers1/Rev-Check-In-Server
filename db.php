<?hh

class db {
  private $database;

  public function __construct() {
	  $this->database = new mysqli("localhost","root","titan","rev");
  }
  
  public function checkIfUserExists(string $email, string $username){
	 $result = $this->database->query("SELECT email FROM user");
	 echo $result->num_rows;
	 
  }
}


