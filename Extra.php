<?php
/*
__PocketMine Plugin__
name=Extra
version=0.0.1
description=Plugin to add extra essential commands to PocketMine
author=linuxboytoo
class=Extra
apiversion=10
*/


class Extra implements Plugin{
        private $api, $path, $config;
        public function __construct(ServerAPI $api, $server = false){
                $this->api = $api;
		$this->config['pluginname'] = get_class($this);
		$this->config['pluginpath'] = $this->path.'plugins/'.$this->config['pluginname'].'/';

		load('commandjson'); 

		if(!file_exists($this->config['pluginpath'])) { mkdir($this->config['pluginpath'],755,true); }
        }

        public function init() {
                $this->api->console->register("heal", "Heal a User", array($this, "heal"));
                $this->api->console->register
        }



	function loadsave($file,$action)
	{
		$filename = $this->config['pluginpath'].'/'.$file.'.json';
		if($action=='load') { $fs = file_get_contents($filename); $this->config[$file] = json_decode($fs); }
		if($action=='save') { file_put_contents($filename, json_encode($this->config[$file])); }
		return true;
	}

	function save($file) { return loadsave($file, 'save'); }
	function load($file) { return loadsave($file, 'load'); }



        public function __destruct(){
        }

	public function heal($cmd, $params, $issuer, $alias) {
                
		if(!($issuer instanceof Player)){ return "Please run this command in-game.\n"; }                	
		
		$username = $issuer->username;
		if($this->api->ban->isOp($username)) {
			if(intval($params[1])<1) { $params[1]=20; }		

			$player = $this->api->player->get($params[0]);
	                if(!($player instanceof Player)){ return "Invalid Player."; }

			$player->entity->heal($params[1], $username, true);

			$this->api->chat->sendTo(false, '[Medic] '.$username.' healed you of '.$params[1].' health!', $params[1]);
			
			return 'You healed '.$player->username;
		}
	}
}
