<?php

die("");

require_once('func.inc.php');

class fake_user
{
	public $isset = true;

	public function get_login()
	{
		return "picque_j";
	}

	public function get_passwd($type)
	{
		return "";
	}
}

class EpitechSSH
{

	CONST EPITECH_SSH_ADDR = "sshd.mikkl.fr";
	CONST ERR_USER_NOTSET = -42;
	CONST ERR_NOT_SET = -41;
	CONST ERR_SSH_CONNECT_FAILED = -40;
	CONST ERR_SSH_AUTH_FAILED = -39;
	CONST ERR_SSH_EXEC_FAILED = -38;

	public $isset = false;
	public $error = NULL;
	private $user = NULL;
	private $connection = NULL;

	public function __construct($user)
	{
		if ($user->isset === false)
		{
			$this->isset = false;
			$this->error = EpitechSSH::ERR_USER_NOTSET;
		}
		if (!$this->connection = ssh2_connect(EpitechSSH::EPITECH_SSH_ADDR, 22))
		{
			$this->isset = false;
			$this->error = EpitechSSH::ERR_SSH_CONNECT_FAILED;
		}
		if (!ssh2_auth_password($this->connection, $user->get_login(), $user->get_passwd('unix')))
		{
			$this->isset = false;
			$this->error = EpitechSSH::ERR_SSH_AUTH_FAILED;
		}
		$this->isset = true;
		return ($this);
	}

	public function exec($cmd) {
		if (!$this->isset)
			return ($this->error = EpitechSSH::ERR_NOT_SET);
		if (!($stream = ssh2_exec($this->connection, $cmd)))
			return ($this->error = EpitechSSH::ERR_SSH_EXEC_FAILED);
		stream_set_blocking($stream, true);
		$data = "";
		while ($buf = fread($stream, 4096))
			$data .= $buf;
		fclose($stream);
		return ($data);
	}

}
$user = new fake_user();
$ssh = new EpitechSSH($user);
echo "<pre>";
rec_tree($ssh, "/", 0);
?>