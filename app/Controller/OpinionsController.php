<?php

class OpinionsController extends Controller {
	public function add() {
		try {
			if (!$this->request->is("post")) {
				throw new Exception("Requête invalide", 500);
			}
			
			$offer = $this->Opinion->Offer->find('first', array(
				'recursive' => 2,
				'conditions' => array(
					'Offer.id' => $this->request->data['offer_id'],
					'accepted' => 1
				)
			));
			if (!$offer) {
				throw new Exception("Offre inexistante", 500);
			}
			if ($offer['Request']['User']['token'] !=  $this->request->data['userToken']) {
				throw new Exception("La demande ne vous appartient pas", 500);
			}
			
			$this->request->data['notation'] = round($this->request->data['notation']);
			
			if (!$this->Opinion->save($this->request->data)) {
				$responseMessage = "Opinion not created";
				$responseNumber = 500;
			}
			
			$offer['Request']['status'] = 'finished';
			$this->Opinion->Offer->Request->save($offer['Request']);
			
			$responseMessage = "Opinion created with success";
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