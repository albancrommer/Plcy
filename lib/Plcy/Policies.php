<?php
namespace Plcy{

    abstract class Policies{
        
        protected $request;
        protected $sources; // This is a bad name, should find something short and clear
        protected $targets;
        public function run($method_name){
            if( !method_exists($this, $method_name)){
                throw new  Exception\Implementation("Missing rule by name: ".$method_name);
            }
            return $this->$method_name();
        }
        
        public function setProperties( $options ){
            foreach($options as $k => $v){
                $this->$k = $v;
            }
        }
    }
}