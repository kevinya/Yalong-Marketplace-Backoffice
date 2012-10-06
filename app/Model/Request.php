<?php

class Request extends AppModel {
	public $actsAs = array("Containable");
	public $belongsTo = array("User", "Service");
	public $hasMany = array("Offer", "RequestImage", "RequestVideo");
}
