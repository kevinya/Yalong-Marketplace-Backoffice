<?php
class ServiceProviderImage extends Model {
	public $belongsTo = 'ServiceProvider';
	public $actsAs = array(
		'FileUpload.FileUpload' => array(
			'uploadDir' => 'upload',
			'allowedTypes' => array(
				'jpg' => array('image/jpeg', 'image/pjpeg'),
				'jpeg' => array('image/jpeg', 'image/pjpeg'),
				'gif' => array('image/gif'),
				'png' => array('image/png','image/x-png')
			)
		)
	);
}