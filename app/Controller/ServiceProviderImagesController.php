<?php
class ServiceProviderImagesController extends Controller {

	public function view($id) {
		$this->viewClass = 'Media';
		$file = $this->ServiceProviderImage->findById($id);
		$fileinfo = explode('.', $file['ServiceProviderImage']['name']);
        $params = array(
            'id'        => $file['ServiceProviderImage']['name'],
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
			
			$user = $this->ServiceProviderImage->ServiceProvider->User->find('first', array(
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
			
			$image = $this->ServiceProviderImage->find('first', array(
				'conditions' => array(
					'ServiceProviderImage.id' => $this->request->data['id']
				)
			));
			if (!$image) {
				throw new Exception("Image introuvable", 500);
			}
			if ($image['ServiceProvider']['id'] != $user['ServiceProvider']['id']) {
				throw new Exception("Cette image ne vous appartient pas", 500);
			}
			$this->ServiceProviderImage->delete($this->request->data['id']);
			
			$responseMessage = "Image supprimée";
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