<?php
/*
__PocketMine Plugin__
name=TeleportRequest
version=0.0.1
description=Plugin to allow users to teleport to other users via a request system
author=linuxboytoo
class=TeleportRequest
apiversion=10
*/

class TeleportRequest implements Plugin{
        private $api, $path, $config;
        public function __construct(ServerAPI $api, $server = false){
                $this->api = $api;
		$this->config['pluginname'] = get_class($this);
		$this->config['pluginpath'] = $this->path.'plugins/'.$this->config['pluginname'];

		if(!file_exists($this->config['pluginpath'])) { mkdir($this->config['pluginpath'],755,true); }
        }

	public function __destruct() {}

        public function init() {
		$this->loadJSON('requests');
		$this->api->console->register("testing","",array($this, "testing"));
		$this->api->console->register("tpa", "Request to be teleported to another user", array($this, "handler"));
		$this->api->console->register("tphere", "Request to teleport a user to you", array($this, "handler"));
		$this->api->console->register("tpaccept", "Accept a teleport request", array($this, "handler"));
		$this->api->console->register("tpdeny", "Deny a teleport request", array($this, "handler"));

		$this->config['lang']['command']['tpa'] = 'teleport to';
		$this->config['lang']['command']['tphere'] = 'summon';	
	}

	public function testing($cmd,$params,$issuer,$alias) { $player = $this->api->player->get("linuxboytoo"); $player->teleport($player->username,$issuer->username); }

	public function handler($cmd, $params, $issuer, $alias) {

		$this->loadJSON('requests');
		$user["issuer"] = $issuer->username;
		$user["target"] = $params[0];
		
		switch($cmd) {
			case "tpa":
			case "tphere":
				$request = $this->logTPARequest($user['issuer'],$user['target'],$cmd);
				if($request===true) { $this->api->chat->sendTo(false, "TPA> ".$user['issuer']." wants to ".$this->config['lang']['command'][$cmd]." you.\n- /tpaccept to accept\n- /tpdeny to deny.", $user['target']); }
				else { return $request; }
				break;
			case "tpaccept":
                                if(!$this->api->player->get($this->config['requests'][$user['issuer']]['requester']) instanceof Player) { return "Requester no longer exists!"; }
                                $this->api->chat->sendTo(false, "TPA> ".$user['issuer']." has accepted your request.",$this->config['requests'][$user['issuer']]['requester']);	
				
				$player = $this->api->player->get($this->config['requests'][$user['issuer']]['requester']);
				if($this->config['requests'][$user['issuer']]['cmd']=='tpa') 	{ $this->api->player->teleport($player->username,$user['issuer']); }
				if($this->config['requests'][$user['issuer']]['cmd']=='tphere') { $this->api->player->teleport($user['issuer'],$player->username); }
							
				break;
			case "tpdeny":
				if(!$this->api->player->get($this->config['requests'][$user['issuer']]['requester']) instanceof Player) { return "Requester no longer exists!"; }
				$this->api->chat->sendTo(false, "TPA> ".$user['issuer']." has rejected your request.",$this->config['requests'][$user['issuer']]['requester']);
				break;
			default:
				return "[TPA] Invalid Command";
				break;
		}	
		$this->saveJSON('requests');
	}

	public function logTPARequest($requester,$acceptor,$cmd)
	{
		if(!$this->api->player->get($acceptor) instanceof Player) 	{ return "Invalid Player"; }

		$this->loadJSON('requests');
		$this->config['requests'][$acceptor] = Array("requester" => $requester,"cmd" => $cmd);
		$this->saveJSON('requests');

		console('[TPA] Logged TPA Request - '.$requester.' '.$this->config['lang']['command'][$cmd].' '.$acceptor);

		return true;
	}

	public function loadJSON($file) { $this->fileJSON($file,'load'); }
	public function saveJSON($file)	{ $this->fileJSON($file,'save'); }

	public function fileJSON($file,$action)
	{
		$requestfile = $this->config['pluginpath'].'/'.$file.'.json';

		if($action=='load') 
		{
			if(!file_exists($requestfile)) { $this->config[$file] = []; }
			else
			{
				$this->config[$file] = json_decode(file_get_contents($requestfile),true);
			}
			return $this->config[$file];
		}

		if($action=='save')
		{
			file_put_contents($requestfile,json_encode($this->config[$file]));
			return true;
		}
	}
}
