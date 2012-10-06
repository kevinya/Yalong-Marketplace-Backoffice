<?php

class Offer extends Model {
	public $belongsTo = array("Request", "ServiceProvider");
}