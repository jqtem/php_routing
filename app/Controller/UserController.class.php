<?php
class UserController{
    public function index($argv){
    	echo "index";
        var_dump($argv);
    }
    public function home($argv){
        echo "user_home";

    }
}
?>