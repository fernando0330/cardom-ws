<?php
require_once "models/model.php";
use Model\Model;

class EmailNotification extends Model{
	
	public $id;
	public $emailFrom;
	public $user;
	public $receivers;
	public $ccs;
	public $bccs;
	public $repliesTo;
	public $subject;
	public $body;

	/**
	 * Method to insert the email notification element
	 */
	public function add(){
		$query = "INSERT INTO {PREF_TABLE}EMAIL_NOTIFICATION(EMAIL_FROM,USER_ID,RECEIVERS,CCS,BCCS,REPLIES_TO,SUBJECT,BODY) VALUES(?,?,?,?,?,?,?,?);";	
		$query = EmailNotification::formatQuery($query);
		
		if (!$result = self::$dbManager->query($query)) return null;
		
		$result->bind_param("sissssss",$this->emailFrom,$this->user->id,$this->receivers,$this->ccs,$this->bccs,$this->repliesTo,$this->subject,$this->body);
		if (!self::$dbManager->executeSql($result)) return null;
		return $result->affected_rows > 0;
	}
}
