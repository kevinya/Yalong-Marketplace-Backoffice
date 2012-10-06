<?php
class OffersController extends Controller {
	public function add() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			$Request = $this->Offer->Request->find('first', array(
				"conditions" => array(
					"Request.id" => $this->request->data['request_id']
				)
			));
			if (!$Request) {
				throw new Exception("Demande inexistante", 500);
			}
			
			$user = $this->Offer->ServiceProvider->User->find('first', array(
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
			$this->request->data['service_provider_id'] = $user['ServiceProvider']['id'];
			
			if (!$this->Offer->save($this->request->data)) {
				$responseMessage = "Impossible d'enregistrer l'offre";
				$responseNumber = 500;
			}
			
			$responseMessage = "Offre enregistrée";
			$responseNumber = 200;
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function accept() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			$offer = $this->Offer->find('first', array(
				"recursive" => 2,
				"conditions" => array(
					"Offer.id" => $this->request->data['offer_id']
				)
			));
			if (!$offer) {
				throw new Exception("Offre inexistante", 500);
			}
			if ($offer['Request']['User']['token'] !=  $this->request->data['userToken']) {
				throw new Exception("La demande ne vous appartient pas", 500);
			}
			
			$acceptedOffersNumber = $this->Offer->find('count', array(
				"conditions" => array(
					"request_id" => $offer['Request']['id'],
					"accepted" => 1
				)
			));
			if ($acceptedOffersNumber != 0) {
				throw new Exception("Cette demande a déjà une offre acceptée", 500);
			}
			
			$this->Offer->read(null, $offer['Offer']['id']);
			$this->Offer->set("accepted", 1);
			$this->Offer->save();
			
			$offer['Request']['status'] = 'accepted';
			$this->Offer->Request->save($offer['Request']);
			
			$responseMessage = "Offre acceptée";
			$responseNumber = 200;
		} catch (Exception $ex) {
			$responseMessage = $ex->getMessage();
			$responseNumber = $ex->getCode();
		}
		
		$response = array("responseMessage" => $responseMessage, "responseNumber" => "".$responseNumber);
		$this->set("data", $response);
		$this->render("/Layouts/json/default", "ajax");
	}
	
	public function decline() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			$offer = $this->Offer->find('first', array(
				"recursive" => 2,
				"conditions" => array(
					"Offer.id" => $this->request->data['offer_id']
				)
			));
			if (!$offer) {
				throw new Exception("Offre inexistante", 500);
			}
			if ($offer['Request']['User']['token'] !=  $this->request->data['userToken']) {
				throw new Exception("La demande ne vous appartient pas", 500);
			}
			
			$acceptedOffersNumber = $this->Offer->find('count', array(
				"conditions" => array(
					"request_id" => $offer['Request']['id'],
					"accepted" => 1
				)
			));
			if ($acceptedOffersNumber != 0) {
				throw new Exception("Cette demande a déjà une offre acceptée", 500);
			}
			
			$this->Offer->read(null, $offer['Offer']['id']);
			$this->Offer->set("accepted", -1);
			$this->Offer->save();
			
			$responseMessage = "Offre refusée";
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