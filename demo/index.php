<ul>
    <li><a href="?action=create&users_id=666&doc_size=150">Create : ok</a></li>
    <li><a href="?action=create&users_id=667&doc_size=150">Create : bad id</a></li>
    <li><a href="?action=create&users_id=666&doc_size=1500">Create : bad size</a></li>
    <li><a href="?action=delete&is_manager=1">Delete : ok</a></li>
    <li><a href="?action=delete&is_manager=0">Delete : not a manager</a></li>
    <li><a href="?action=read&users_id=666">Read not locked : ok</a></li>
    <li><a href="?action=read&users_id=667">Read not locked : bad id</a></li>
    <li><a href="?action=read&is_locked=1&users_id=666&is_manager=1">Read locked : ok</a></li>
    <li><a href="?action=read&is_locked=1&users_id=667&is_manager=1">Read locked : bad id </a></li>
    <li><a href="?action=read&is_locked=1&users_id=666&is_manager=0">Read locked : not a manager</a></li>
</ul>
<style>
    ul,li{
        list-style: none outside none;
        padding: 0;        
    }
    a{
        display:inline-block;border:1px solid #ccc;background: #eee;padding:4px 8;margin-bottom: 5px;border-radius:10px;text-decoration: none;box-shadow: 0px 2px 1px #eee;color:#333;font-family:sans-serif;font-size:10px;
    }
    a:hover{
        background:#ddd;
    }
</style>

<?php

/*
 * TODO : rename sources / targets
 * TODO : convention : test are IsStuff (IsLocked), policies are HasThing (HasAdminRights...)
 */
// Test url
// 
// plcy.local/demo/?users_id=666&doc_size=150
// 
/*
CREATE TABLE `policies` (
  `policies_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sources` varchar(45) DEFAULT NULL,
  `targets` varchar(45) DEFAULT NULL,
  `policies` varchar(45) DEFAULT NULL,
  `conditions` varchar(255) DEFAULT NULL,
  `actions` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`policies_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1

INSERT INTO `policies` (`policies_id`,`sources`,`targets`,`policies`,`conditions`,`actions`) VALUES (1,'users','docs ','ownerId,userQuota',NULL,'create');

*/
error_reporting(E_ERROR);

// Auto include
set_include_path(get_include_path().":../lib");
spl_autoload_register(function ($className) {

        $ds = DIRECTORY_SEPARATOR;
        $dir = __DIR__."/../lib";
        $className = strtr($className, '\\', $ds);
        $file = "{$dir}{$ds}{$className}.php";
        if (is_readable($file)){
            require_once $file;
        }
});


// THIS is the BEAST: Plcy Handler is the interface to query the system
//
$policyHandler  = new Plcy\Handler();

// the BEAST will need to eat
$policyDb       = new Plcy\Database\Mysql(array(
    "database"  => "plcy",
    "host"      => "localhost",
    "user"      => "plcyuser",
    "table"     => "policies",
    "password"  => "plcyuserpassword"
));
$policyHandler->setDatabase($policyDb);

// The BEAST needs Rules 
class myPolicies extends \Plcy\Policies{
    
    /*
     * Checks if the user trying to access a resource owns it
     */
    function ownerId(){
        
        // Some routine to attempt to load stuff here
        if(array_key_exists("users_id", $this->request)){
            $this->sources->users_id = $_GET["users_id"];
        }
        
        if( $this->sources->users_id != $this->targets->users_id) {
            throw new \Plcy\Exception\Illegal( "Not your resource.");
        }
        
    }

    /*
     * Checks if the user trying to add a resource has space for it
     */
    function userQuota(){
        // Some routine to attempt to load stuff here
        if(array_key_exists("doc_size", $_GET)){
            $this->targets->doc_size = $_GET["doc_size"];
        }
        
        if( $this->sources->users_quota < $this->targets->doc_size ) {
            throw new \Plcy\Exception\Invalid( "Not enough quota");
        }
    }
    
    /*
     * 
     */
    function isManager(){
        
        // Some routine to attempt to load stuff here
        if(array_key_exists("is_manager", $_GET)){
            $this->sources->is_manager = $_GET["is_manager"];
        }
        if( ! $this->sources->is_manager){
            throw new \Plcy\Exception\Illegal( "Not an admin.");
        }
    }
    
    function targetLocked(){
               
        // Some routine to attempt to load stuff here
        if(array_key_exists("is_locked", $_GET)){
            $this->targets->is_locked = $_GET["is_locked"];
        }

        if( $this->targets->is_locked){
            return true;
        } 
        return false;
    }
}
$myPolicies     = new myPolicies();
$policyHandler->setPolicies($myPolicies);

// Some more application here, config, routing, yada yada

// Random objects
class User {
    use  Plcy\Object;
    public $plcy_id = "users";
    var $users_quota = 666;
}
$user            = new User();

class Document {
    use  Plcy\Object;
    public $plcy_id = "docs";
    var $users_id = 666;

}
$doc             = new Document();

// AAAAAND now we can check if that's allowed
if( ! $policyHandler->check($user,$doc,$_GET["action"],$_REQUEST)){
    $message    = $policyHandler->getMessage();
    // do stuff : redirect, die, alert...
    die( $message );
}
echo "OK, go on\n ";