<?
namespace Plcy{

    trait  Object {

        public function getPlcyId(){
            if( !property_exists( $this, "plcy_id" ) || NULL == $this-> plcy_id ){
                throw new  Exception\Implementation("Missing policy plcy_id");
            }
            return $this->plcy_id;
        }
    }
}