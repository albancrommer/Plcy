<?php 
namespace Plcy\Database{

    /*
     * Very plain and simple, doesn't receive a PDO connection namely
     */
    class Mysql implements \Plcy\Database  {

        public function __construct($options = null ) {

            $this->_database        = $options["database"];
            $this->_table           = $options["table"];
            $this->_host            = $options["host"];
            $this->_user            = $options["user"];
            $this->_password        = $options["password"];
            if( null == $this->_database)
                {throw new  Exception\Implementation("Missing _database configuration parameter");}
            if( null == $this->_host)
                {throw new  Exception\Implementation("Missing _host configuration parameter");}
            if( null == $this->_table)
                {throw new  Exception\Implementation("Missing _table configuration parameter");}
            if( null == $this->_user)
                {throw new  Exception\Implementation("Missing _user configuration parameter");}
            if( null == $this->_password)
                {throw new  Exception\Implementation("Missing _password configuration parameter");}

        }
        function fetch($sourceLabel,$targetLabel,$action) {

            $link_identifier    = mysql_connect($this->_host, $this->_user, $this->_password);
            if (!$link_identifier) {
                throw new  \Plcy\Exception\Implementation("Couldn't connect to mysql.");
            }
            if (!mysql_select_db($this->_database, $link_identifier)) {
                throw new  \Plcy\Exception\Implementation("Failed connecting to db.");
            }     
            $query              = "SELECT * FROM `".$this->_table."` ";
            $query              .= " WHERE `sources`='".$sourceLabel."'";
            $query              .= " AND `targets`='".$targetLabel."'";
            $query              .= " AND `actions`='".$action."'";
            $result             = mysql_query($query, $link_identifier);
            if(mysql_errno($link_identifier)){
                throw new  \Plcy\Exception\Implementation("Query failed : ".  mysql_error($link_identifier));
            }
            $rows               = [];
            while ($row         = mysql_fetch_assoc($result)) {
                $rows[]         = $row;
            }
            return $rows;
        }

    }
}