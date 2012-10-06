<?php

class ServicesController extends Controller {

	public function getAll() {
		try {
			$services = $this->Service->find('all', array(
				'fields' => 'label'
			));
			if (!$services) {
				throw new Exception("Service list not found", 500);
			}
			
			$responseMessage = "Services found";
			$responseNumber = 200;
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber, "data" => $services);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
}