<?php

class ServiceProvider extends Model {
	public $belongsTo = array("User", "Service");
	public $hasMany = array("Opinion", "ServiceProviderImage", "ServiceProviderVideo");
}