<?php

require_once("user.class.php");

class EpitechExporter
{
	CONST ERR_NOT_SET = -42;
	private $user = NULL;
	public $isset = false;
	public $error = NULL;

	public function __construct($user = NULL)
	{
		if (!isset($user) || $user->isset === false)
		{
			$this->error = EpitechExporter::ERR_NOT_SET;
			return ($this);
		}
		$this->user = $user;
		$this->isset = true;
		return ($this);
	}

	public function export_day($date = NULL)
	{
		$ret = $this->user->get_week($date);
		echo json_encode($ret);
	}
}

?>