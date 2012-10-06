<?php

class ServiceProvidersController extends Controller {
	public function create() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->ServiceProvider->User->find('first', array(
				"conditions" => array(
					"token" => $this->request->data['ServiceProvider']['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			$this->request->data['ServiceProvider']['user_id'] = $user['User']['id'];
			
			$service = $this->ServiceProvider->Service->find('first', array(
				"conditions" => array(
					"label" => $this->request->data['ServiceProvider']['service_id']
				)
			));
			if (!$service) {
				$service = array("label" => $this->request->data['ServiceProvider']['service_id']);
				$service = $this->ServiceProvider->Service->save($service);
			}
			$this->request->data['ServiceProvider']['service_id'] = $service['Service']['id'];
			
			if (isset($this->request->data['ServiceProvider']['address'])) {
				$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=true&address=";
				$get = str_replace(" ", "+", utf8_encode($this->request->data['ServiceProvider']['address']));
				$response = file_get_contents($url.$get);
				$json = json_decode($response, true);
				
				if ($json['status'] != "OK") {
					throw new Exception("Impossible de trouver l'adresse", 500);
				}
				if (count($json['results']) == 1) {
					$this->request->data['ServiceProvider']["address"] = $json['results'][0]['formatted_address'];
				}
			}
			
			if (!$this->ServiceProvider->saveAll($this->request->data)) {
				throw new Exception("Unable to create service provider", 500);
			}
		
			$responseMessage = "Service created with success";
			$responseNumber = 200;
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function isServiceProvider() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->ServiceProvider->User->find('first', array(
				"conditions" => array(
					"token" => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			
			if ($user['ServiceProvider']['id']) {
				$data['service_provider_id'] = $user['ServiceProvider']['id'];
			}
			
			$responseMessage = "Service Provider found";
			$responseNumber = 200;
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
	
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber, "data" => $data);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function view($id) {
		try {
			$serviceProvider = $this->ServiceProvider->find('first', array(
				'conditions' => array(
					'ServiceProvider.id' => $id
				)
			));
			if (!$serviceProvider) {
				throw new Exception('Prestataire introuvable', 500);
			}
			$serviceProvider['ServiceProvider']['service'] = $serviceProvider['Service']['label'];
			$serviceProvider['ServiceProvider']['notation'] = $this->requestAction(array('controller' => 'serviceProviders', 'action' => 'getNotation', $id));
			$serviceProvider['ServiceProvider']['ServiceProviderImage'] = $serviceProvider['ServiceProviderImage'];
			$serviceProvider['ServiceProvider']['ServiceProviderVideo'] = $serviceProvider['ServiceProviderVideo'];
			unset($serviceProvider['Service']);
			unset($serviceProvider['User']);
			unset($serviceProvider['Opinion']);
			unset($serviceProvider['ServiceProviderImage']);
			unset($serviceProvider['ServiceProviderVideo']);
			
			$responseMessage = "Prestataire trouvé";
			$responseNumber = 200;
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber, "data" => $serviceProvider);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getNotation($id) {
		$notation = $this->ServiceProvider->Opinion->find('first', array(
			'fields' => array('AVG(notation) as notation'),
			'conditions' => array(
				'Opinion.service_provider_id' => $id
			)
		));
		if (!$notation[0]['notation']) {
			return 0;
		}
		return $notation[0]['notation'];
	}
}