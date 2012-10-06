<?php

class UsersController extends AppController {
	
	public function create() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			if (!isset($this->request->data["id"]) || (isset($this->request->data["id"]) && isset($this->request->data["password"]))) {
				$this->request->data["password"] = sha1($this->request->data['password']);
			}
			
			if (isset($this->request->data['address'])) {
				$url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=true&address=";
				$get = str_replace(" ", "+", $this->request->data["address"]);
				$response = file_get_contents($url.$get);
				$json = json_decode($response, true);
				
				if ($json['status'] != "OK") {
					throw new Exception("Impossible de trouver l'adresse", 500);
				}
				if (count($json['results']) == 1) {
					$this->request->data["address"] = $json['results'][0]['formatted_address'];
				}
			}
			
			$user = $this->User->save($this->request->data);
			if (!$user) {
				throw new Exception("Impossible de créer votre compte", 500);
			}
			
			$responseMessage = "Compte crée avec succès";
			$responseNumber = 200;
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function view() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			$user = $this->User->find('first', array(
				'recursive' => -1,
				"conditions" => array(
					"token" => $this->request->data['userToken']
				)
			));
			if (!$user) {
				throw new Exception("Utilisateur introuvable", 500);
			}
			
			unset($user['User']['token']);
			unset($user['User']['password']);
			
			$responseMessage = "Profil trouvé";
			$responseNumber = 200;
			
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber, "data" => $user);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function login() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			$user = $this->User->find('first', array(
				"conditions" => array(
					"email" => $this->request->data['email'],
					"password" => sha1($this->request->data['password'])
				)
			));
			if (!$user) {
				throw new Exception("Email ou mot de passe incorrect", 500);
			}
			
			$userToken = sha1(rand().$user['User']['id']);
			$this->User->read(null, $user['User']['id']);
			$this->User->set('token', $userToken);
			$this->User->saveField('token', $userToken);
			
			$responseMessage = "Authentifié avec succès";
			$responseNumber = 200;
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber, "userToken" => $userToken);
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
			$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		}
		
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
}