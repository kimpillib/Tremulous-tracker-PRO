<?php

function explode_player($player_string)
{
        $parts = explode(' ', $player_string);
	
	// recompose the name, that may have spaces
	$name = '';
	for ($i=2; $i < count($parts); $i++)
	{
		if ($i > 2)
			$name .= ' ';
		$name .= $parts[$i];
	}
	// trim quotes
	$name = trim($name, '"');

	$result = array();
	$result['score'] = $parts[0];
	$result['ping'] = $parts[1];
	$result['name'] = $name;
	
	return $result;
}

class Q3Master
{
	private $_socket;
	private $_port = '8000';
	private $_host;
   
	public function __construct($masterHost)
	{
		$this->_socket = fsockopen('udp://'.$masterHost,8000);
		stream_set_blocking($this->_socket,0);
	}
   
	public function master_listServers($timeout = '10')
	{
		fputs($this->_socket,str_repeat(chr(255),4).'getservers 69 empty full demo'."\n");
	   
		$time=time()+$timeout;
		$return = '';
		while($time > time() && strpos($return,'EOT') === FALSE)
		{
			echo "time=".$time." time()=".time()." \"".$return."\"\n";
			$return .= fgets($this->_socket);
		}
		$return = explode('\\',$return);
		unset($return[0]);
		unset($return[count($return)]);
		$iplist = array();
		foreach($return as $server)
		{
			for($i = 0;$i < 4;$i++)
					$addr[] = ord($server[$i]);
				   
			for($i = 4;$i < 6;$i++)
					$port .= dechex(ord($server[$i]));
			$port = hexdec($port);
			$iplist[] = array('ip' => join('.',$addr),'port' => $port);
			unset($addr);
			unset($port);
		}
		return $iplist;
	}
   
	function server_getInfo($adresse, $port)
	{
		if($port != 0)
		{
			$cmd = "\xFF\xFF\xFF\xFFgetstatus";
			$f = fsockopen('udp://'.$adresse, $port);
		   
			socket_set_timeout ($f, 1);
			fwrite ($f, $cmd);
			$data = fread ($f, 10000);
			fclose ($f);
			   
			if($data)
			{
				$lines = explode("\x0a",$data);
				$list3 = explode("\\",substr($lines[1],1,strlen($lines[1])));
				for ($i = 0;$i <= count($list3);$i = $i + 2)
				{
				   $list[@$list3[$i]] = @$list3[$i + 1];
				}
				array_pop($list);
			   
				$players = array();
				foreach($lines as $id => $player)
				{
				    if($id != 0 AND $id != 1)
				    {
						if ($player != '')
							$players[] = explode_player($player);
				    }
				}
				array_pop($players);
			   
				$infos = array();
				$infos['server_info'] = $list;
				$infos['players'] = $players;
			   
				return $infos;
			}
			else
				return FALSE;
		}
		else
			return FALSE;
	}
}

$master = new Q3Master('75.126.181.231');

//$serverList = $master->master_listServers();
//$serverInfo = $master->server_getInfo($serverList[0]['ip'], $serverList[0]['port']);

// W server address: 75.126.181.231:8000 
$serverInfo = $master->server_getInfo('75.126.181.231', 8000);
if ($serverInfo)
{
	print_r($serverInfo);
}
else
{
	echo "Error getting server status! Please retry.\n";
}

?>