<?php
class RequestVideosController extends Controller {

	public function view($id) {
		$this->viewClass = 'Media';
		$file = $this->RequestVideo->findById($id);
		$fileinfo = explode('.', $file['RequestVideo']['name']);
        $params = array(
            'id'        => $file['RequestVideo']['name'],
            'name'      => $fileinfo[0],
            'download'  => true,
            'extension' => $fileinfo[1],
            'path'      => APP . 'webroot' . DS . 'upload' . DS
        );
        $this->set($params);
	}
	
}