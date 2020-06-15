<?php
class ModelExtensionModuleZemezNewsletter extends Model
{
	public function addNewsletter($data){
		$this->db->query("INSERT INTO " . DB_PREFIX . "zemez_newsletter SET zemez_newsletter_email = '" . $data ."'");
	}

	public function deleteNewsletter($data){
		$this->db->query("DELETE FROM `" . DB_PREFIX . "zemez_newsletter` WHERE zemez_newsletter_email = '" . $data . "'");
	}

	public function getNewsletters(){
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zemez_newsletter");
		return $query->rows;
	}

	public function getNewsletterByEmail($email) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zemez_newsletter WHERE zemez_newsletter_email = '" . $email . "'");

		return $query->row;
	}

}