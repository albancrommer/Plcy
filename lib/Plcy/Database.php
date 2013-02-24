<?php
namespace Plcy{

    interface  Database{
        public function fetch( $sourceLabel,$targetLabel,$action );

    }
}