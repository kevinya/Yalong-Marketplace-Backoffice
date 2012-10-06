<?php

class User extends AppModel {
	public $hasOne = array("ServiceProvider");
	public $hasMany = array("Request");
}
?>