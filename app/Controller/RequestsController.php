<?php

class RequestsController extends Controller {

	public function add() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
				"conditions" => array(
					"token" => $this->request->data['Request']['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			$this->request->data['Request']['user_id'] = $user['User']['id'];
			
			$service = $this->Request->Service->find('first', array(
				"conditions" => array(
					"label" => $this->request->data['Request']['service_id']
				)
			));
			if (!$service) {
				$service = array("label" => $this->request->data['Request']['service_id']);
				$service = $this->Request->Service->save($service);
			}
			
			$this->request->data['Request']['service_id'] = $service['Service']['id'];
			
			if (isset($this->request->data['Request']['address'])) {
				$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=true&address=";
				$get = str_replace(" ", "+", utf8_encode($this->request->data['Request']['address']));
				$response = file_get_contents($url.$get);
				$json = json_decode($response, true);
				
				if ($json['status'] != "OK") {
					throw new Exception("Impossible de trouver l'adresse", 500);
				}
				if (count($json['results']) == 1) {
					$this->request->data['Request']["address"] = $json['results'][0]['formatted_address'];
				}
			}
			
			$this->request->data['Request']['status'] = "waiting";
			$this->request->data['Request']['description'] = utf8_encode($this->request->data['Request']['description']);
			
			if (!$this->Request->saveAll($this->request->data)) {
				throw new Exception("Unable to create Request", 500);
			}
			
			$responseMessage = "Request created with success.";
			$responseNumber = 200;
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getAll() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
					"conditions" => array(
						"token" => $this->request->data['userToken']
					)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'Request.user_id' => $user['User']['id']
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				$newRequest = $request['Request'];
				$newRequest['service'] = $request['Service']['label'];
				$newRequests[]['Request'] = $newRequest;
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getWaiting() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
				'conditions' => array(
					'token' => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'Request.user_id' => $user['User']['id'],
					'status' => 'waiting'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'ServiceProvider',
						'conditions' => array(
							'accepted' => 0
						)
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				$newRequest = $request['Request'];
				$newRequest['Offer'] = array();
				foreach ($request['Offer'] as $offer) {
					$newOffer = $offer;
					unset($newOffer['ServiceProvider']);
					$newOffer['service_provider_name'] = $offer['ServiceProvider']['company_name'];
					$newOffer['service_provider_notation'] = $this->requestAction("/serviceProviders/getNotation/" . $newOffer['service_provider_id']);
					$newRequest['Offer'][] = $newOffer;
				}
				$newRequest['service'] = $request['Service']['label'];
				$newRequest['RequestImage'] = $request['RequestImage'];
				$newRequest['RequestVideo'] = $request['RequestVideo'];
				$newRequests[]['Request'] = $newRequest;
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getAccepted() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
				'conditions' => array(
					'token' => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'Request.user_id' => $user['User']['id'],
					'status' => 'accepted'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'ServiceProvider',
						'conditions' => array(
							'accepted' => 1
						)
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				$newRequest = $request['Request'];
				$newRequest['Offer'] = array();
				foreach ($request['Offer'] as $offer) {
					$newOffer = $offer;
					unset($newOffer['ServiceProvider']);
					$newOffer['service_provider_name'] = $offer['ServiceProvider']['company_name'];
					$newOffer['service_provider_notation'] = $this->requestAction("/serviceProviders/getNotation/" . $newOffer['service_provider_id']);
					$newRequest['Offer'][] = $newOffer;
				}
				$newRequest['service'] = $request['Service']['label'];
				$newRequest['RequestImage'] = $request['RequestImage'];
				$newRequest['RequestVideo'] = $request['RequestVideo'];
				$newRequests[]['Request'] = $newRequest;
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getFinished() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
				'conditions' => array(
					'token' => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'Request.user_id' => $user['User']['id'],
					'status' => 'finished'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'ServiceProvider',
						'conditions' => array(
							'accepted' => 1
						)
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				$newRequest = $request['Request'];
				$newRequest['Offer'] = array();
				foreach ($request['Offer'] as $offer) {
					$newOffer = $offer;
					unset($newOffer['ServiceProvider']);
					$newOffer['service_provider_name'] = $offer['ServiceProvider']['company_name'];
					$newOffer['service_provider_notation'] = $this->requestAction("/serviceProviders/getNotation/" . $newOffer['service_provider_id']);
					$newRequest['Offer'][] = $newOffer;
				}
				$newRequest['service'] = $request['Service']['label'];
				$newRequest['RequestImage'] = $request['RequestImage'];
				$newRequest['RequestVideo'] = $request['RequestVideo'];
				$newRequests[]['Request'] = $newRequest;
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getServiceProviderMatching() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
				"conditions" => array(
					"token" => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			if (!$user['ServiceProvider']['id']) {
				throw new Exception("User is not a service provider", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'service_id' => $user['ServiceProvider']['service_id'],
					'status' => 'waiting'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'conditions' => array(
							'service_provider_id' => $user['ServiceProvider']['id']
						),
						'order' => 'Offer.id DESC',
						'limit' => 1
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				if (!isset($request['Offer'][0]) || intval($request['Offer'][0]['accepted']) == -1) {
					$origins = str_replace(" ", "+", $user['ServiceProvider']['address']);
					$destinations = str_replace(" ", "+", $request['Request']['address']);
					$url = "http://maps.googleapis.com/maps/api/distancematrix/json?sensor=true&origins=$origins&destinations=$destinations";
					$response = file_get_contents($url);
					$json = json_decode($response, true);
					$this->log(serialize($json));
					if ($json['status'] != "OK" || intval($json['rows'][0]['elements'][0]['distance']['value']) < 30000) {
						$newRequest = $request['Request'];
						$newRequest['service'] = $request['Service']['label'];
						$newRequest['RequestImage'] = $request['RequestImage'];
						$newRequest['RequestVideo'] = $request['RequestVideo'];
						$newRequests[]['Request'] = $newRequest;
					}
				}
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
			
	}
	
	public function getServiceProviderWaiting() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
					"conditions" => array(
						"token" => $this->request->data['userToken']
					)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			if (!$user['ServiceProvider']['id']) {
				throw new Exception("User is not a service provider", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'service_id' => $user['ServiceProvider']['service_id'],
					'status' => 'waiting'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'conditions' => array(
							'service_provider_id' => $user['ServiceProvider']['id']
						),
						'order' => 'Offer.id DESC',
						'limit' => 1
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				if (isset($request['Offer'][0]) && intval($request['Offer'][0]['accepted']) == 0) {
					$newRequest = $request['Request'];
					$newRequest['Offer'] = array();
					foreach ($request['Offer'] as $offer) {
						$newOffer = $offer;
						unset($newOffer['ServiceProvider']);
						$newOffer['service_provider_name'] = $offer['ServiceProvider']['company_name'];
						$newOffer['service_provider_notation'] = $this->requestAction("/serviceProviders/getNotation/" . $newOffer['service_provider_id']);
						$newRequest['Offer'][] = $newOffer;
					}
					$newRequest['service'] = $request['Service']['label'];
					$newRequest['RequestImage'] = $request['RequestImage'];
					$newRequest['RequestVideo'] = $request['RequestVideo'];
					$newRequests[]['Request'] = $newRequest;
				}
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getServiceProviderAccepted() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
					"conditions" => array(
						"token" => $this->request->data['userToken']
					)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			if (!$user['ServiceProvider']['id']) {
				throw new Exception("User is not a service provider", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'service_id' => $user['ServiceProvider']['service_id'],
					'status' => 'accepted'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'conditions' => array(
							'service_provider_id' => $user['ServiceProvider']['id'],
							'accepted' => 1
						),
						'order' => 'Offer.id DESC',
						'limit' => 1
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				if (intval($request['Offer'][0]['accepted']) == 1) {
					$newRequest = $request['Request'];
					$newRequest['Offer'] = array();
					foreach ($request['Offer'] as $offer) {
						$newOffer = $offer;
						unset($newOffer['ServiceProvider']);
						$newOffer['service_provider_name'] = $offer['ServiceProvider']['company_name'];
						$newOffer['service_provider_notation'] = $this->requestAction("/serviceProviders/getNotation/" . $newOffer['service_provider_id']);
						$newRequest['Offer'][] = $newOffer;
					}
					$newRequest['service'] = $request['Service']['label'];
					$newRequest['RequestImage'] = $request['RequestImage'];
					$newRequest['RequestVideo'] = $request['RequestVideo'];
					$newRequests[]['Request'] = $newRequest;
				}
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function getServiceProviderFinished() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Unvalid request", 500);
			}
			
			$user = $this->Request->User->find('first', array(
					"conditions" => array(
						"token" => $this->request->data['userToken']
					)
			));
			if (!$user) {
				throw new Exception("User not found", 500);
			}
			if (!$user['ServiceProvider']['id']) {
				throw new Exception("User is not a service provider", 500);
			}
			
			$requests = $this->Request->find('all', array(
				'conditions' => array(
					'service_id' => $user['ServiceProvider']['service_id'],
					'status' => 'finished'
				),
				'contain' => array(
					'Service',
					'RequestImage',
					'RequestVideo',
					'Offer' => array(
						'conditions' => array(
							'service_provider_id' => $user['ServiceProvider']['id'],
							'accepted' => 1
						),
						'order' => 'Offer.id DESC',
						'limit' => 1
					)
				)
			));
			
			$newRequests = array();
			foreach($requests as $request) {
				if (intval($request['Offer'][0]['accepted']) == 1) {
					$newRequest = $request['Request'];
					$newRequest['Offer'] = array();
					foreach ($request['Offer'] as $offer) {
						$newOffer = $offer;
						unset($newOffer['ServiceProvider']);
						$newOffer['service_provider_name'] = $offer['ServiceProvider']['company_name'];
						$newOffer['service_provider_notation'] = $this->requestAction("/serviceProviders/getNotation/" . $newOffer['service_provider_id']);
						$newRequest['Offer'][] = $newOffer;
					}
					$newRequest['service'] = $request['Service']['label'];
					$newRequest['RequestImage'] = $request['RequestImage'];
					$newRequest['RequestVideo'] = $request['RequestVideo'];
					$newRequests[]['Request'] = $newRequest;
				}
			}
			
			$response = array("responseMessage" => "Requests found", "responseNumber" => "200", "data" => $newRequests);
			
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
}