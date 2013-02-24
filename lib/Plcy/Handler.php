<?php 
namespace Plcy{

    class Handler{

        protected $_db;
        protected $_policies;
        protected $_response;

        function setDatabase($db) {
            if( !is_a($db,"\Plcy\Database")){
                throw new  Exception\Implementation("Invalid Database class");
            }
            $this->_db              = $db;
        }

        function getDatabase() {
            if( null == $this->_db){
                throw new  Exception\Implementation("Missing Database instance");
            }
            return $this->_db;
        }


        function setPolicies($policies) {
            if( !is_a($policies,"\Plcy\Policies")){
                throw new  Exception\Implementation("Invalid Policies class");
            }
            $this->_policies        = $policies;

        }
        function getPolicies() {
            if( null == $this->_policies){
                throw new  Exception\Implementation("Missing Policies instance");
            }
            return $this->_policies;

        }


        function check($sourceObject, $targetObject, $action, $request = null) {

            try{
                $sourceLabel        = $sourceObject->getPlcyId();
                $targetLabel        = $targetObject->getPlcyId();
                $rowset             = $this->getDatabase()->fetch($sourceLabel,$targetLabel,$action);
                $this->getPolicies()->setProperties(array(
                        "sources"       => $sourceObject, 
                        "targets"       => $targetObject, 
                        "request"       => $request)
                        );
                foreach( $rowset as $record ){
                    // Checks conditions for record if exist and are met
                    $conditionsStr  = $record["conditions"];
                    if( null != $conditionsStr){
                        $conditions = explode(",",$conditionsStr);
                        $conditions_ok = true;;
                        foreach( $conditions as $theCondition ){
                            if( ! $this->getPolicies()->run($theCondition)){
                                $conditions_ok = false;;
                            }
                        }
                        if( ! $conditions_ok ){
                            continue;
                        }
                    }
                    // Checks rules in the record
                    $policies   = explode(",", $record["policies"]);
                    foreach( $policies as $thePolicy ){
                        $this->getPolicies()->run($thePolicy);
                    }
                }
            }catch ( Exception\Implementation $e ){
                $this->_response = $e->getResponse();
                return false;
            }catch ( Exception\Illegal $e ){
                $this->_response = $e->getResponse();
                return false;
            }catch ( Exception\Invalid $e ){
                $this->_response = $e->getResponse();
                return false;
            }catch ( \Exception $e){
                throw $e;
            }
            // Fine, move along
            return true;
        }

        function getMessage() {
            return $this->_response["message"];

        }

        protected function _testCondition($condition) {
            return true;
        }
    }
}