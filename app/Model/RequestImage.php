<?php
class RequestImage extends Model {
	public $belongsTo = 'Request';
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