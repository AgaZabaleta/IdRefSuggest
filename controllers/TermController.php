<?php
require_once(dirname(dirname(__FILE__))."/helpers/HandleAccents.php");

class IdRefSuggest_TermController extends Omeka_Controller_AbstractActionController {
	public function searchAction() {
		$db = $this->_helper->db;
		$request = $this->getRequest();

		$suggest_type = $request->getParam('suggest_type');
		$term = strtolower(HandleAccents::remove_accents(trim($request->getParam('term'))));

		$term = preg_replace('/\s+/', '|', $term);

		switch ($suggest_type) {
			case "persname":
				$terms_array = explode('|', $term, 3);
				$suggest_type .= "_s";

				if (count($terms_array) < 2) {
					$url = "https://www.idref.fr/Sru/Solr?q="
						.$suggest_type.":".$terms_array[0]."*"
						."&wt=json&sort=score%20desc&start=0&rows=30&fl=affcourt_z";
				} else {
					$url = "https://www.idref.fr/Sru/Solr?q="
						.$suggest_type.":".$terms_array[0]."*"
						."&fq=prenom_s:".$terms_array[1]."*"
						."&wt=json&sort=score%20desc&start=0&rows=30&fl=affcourt_z";
				}

				break;

			case "subjectheading":
				$terms_array = explode('|', $term, 3);
				$suggest_type .= "_s";

				if (count($terms_array) < 2) {
					$url = "https://www.idref.fr/Sru/Solr?q="
						.$suggest_type.":(".$terms_array[0]."*) AND recordtype_z:r"
						."&sort=score%20desc&start=0&rows=30&fl=affcourt_z&wt=json";
				} else {
					$query = $terms_array[0]."*";
					for($i = 1; $i < count($terms_array); ++$i) {
						$query .= " AND " . $terms_array[$i]."*";
					}

					$url = "https://www.idref.fr/Sru/Solr?q="
						.$suggest_type.":(".$query.") AND recordtype_z:r"
						."&wt=json&sort=score%20desc&start=0&rows=30&fl=affcourt_z";
				}

				$url = preg_replace('/\s+/', '%20', $url);

				break;
		}

		$return = array();
		$data = file_get_contents($url);
		$results = json_decode($data);

		foreach($results->response->docs as $result) {
			$return[] = $result->affcourt_z;
		}

		$this->_helper->json($return);
	}
}