<?php
class ServiceProviderVideosController extends Controller {

	public function view($id) {
		$this->viewClass = 'Media';
		$file = $this->ServiceProviderVideo->findById($id);
		$fileinfo = explode('.', $file['ServiceProviderVideo']['name']);
        $params = array(
            'id'        => $file['ServiceProviderVideo']['name'],
            'name'      => $fileinfo[0],
            'download'  => true,
            'extension' => $fileinfo[1],
            'path'      => APP . 'webroot' . DS . 'upload' . DS
        );
        $this->set($params);
	}
	
	public function delete() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->ServiceProviderVideo->ServiceProvider->User->find('first', array(
				"conditions" => array(
					"token" => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			if (!$user['ServiceProvider']['id']) {
				throw new Exception("Vous n'êtes pas prestataire", 500);
			}
			
			$video = $this->ServiceProviderVideo->find('first', array(
				'conditions' => array(
					'ServiceProviderVideo.id' => $this->request->data['id']
				)
			));
			if (!$video) {
				throw new Exception("Video introuvable", 500);
			}
			if ($video['ServiceProvider']['id'] != $user['ServiceProvider']['id']) {
				throw new Exception("Cette video ne vous appartient pas", 500);
			}
			$this->ServiceProviderVideo->delete($this->request->data['id']);
			
			$responseMessage = "Video supprimée";
			$responseNumber = 200;
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
}