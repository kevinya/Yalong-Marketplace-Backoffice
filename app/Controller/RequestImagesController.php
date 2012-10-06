<?php
class RequestImagesController extends Controller {

	public function view($id) {
		$this->viewClass = 'Media';
		$file = $this->RequestImage->findById($id);
		$fileinfo = explode('.', $file['RequestImage']['name']);
        $params = array(
            'id'        => $file['RequestImage']['name'],
            'name'      => $fileinfo[0],
            'download'  => true,
            'extension' => $fileinfo[1],
            'path'      => APP . 'webroot' . DS . 'upload' . DS
        );
        $this->set($params);
	}
	
}