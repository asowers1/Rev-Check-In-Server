<?hh // strict
include("db.php");
$database = new db;
echo "hello<br>";
$database->checkIfUserExists("asow92","asow123@gmail.com");