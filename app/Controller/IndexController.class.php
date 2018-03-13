<?php
class IndexController{
    public function index($argv){
    	echo "home Page";
    }
    public function home($argv){
        echo "user_home";
        var_dump($argv);
    }
}
?>