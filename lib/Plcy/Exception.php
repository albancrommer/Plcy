<?php
namespace Plcy{

    class Exception extends \Exception{
        
        function getResponse(){
            return array(
                "message" => $this->getSignature()."::".$this->getMessage(),
            );
        }
        function getSignature(){
            $sig    = (null == $this->_signature ? "DEFAULT":$this->_signature);
            return $sig;
        }
    }
}