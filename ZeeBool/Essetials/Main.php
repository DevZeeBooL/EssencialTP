<?php

namespace ZeeBool\Essetials;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\Server;
use ZeeBool\Essetials\Task\Task;

class Main extends PluginBase implements Listener{
  
  /** @var array $homes [] */
  public $homes;
  /** @var array $cfg [] */
  public $cfg;
  /** @var array $warps [] */
  public $warps;
  /** @var array $publicas [] */
  public $publicas;
  /** @var array $tpa [] */
  public $tpa;
  /** @var array $tpc [] */
  public $tpc;
  /** @var array $cod [] */
  public $cod = [];
  
  public function onEnable(){

@mkdir($this->getDataFolder()."players");

$this->publicas = new Config($this->getDataFolder()."Publicas.yml", Config::YAML);
$this->warps = new Config($this->getDataFolder()."Warps.yml", Config::YAML);
$this->cfg = new Config($this->getDataFolder()."Messages.yml", Config::YAML, [
  "msg" => [
    "home-existente" => "§4Essa Home ja existe!",
    "home-setada" => "§aHome sentada com sucesso",
    "publica-setada" => "§publica sentada com sucesso",
    "home-apagada" => "§aHome apagada com sucesso",
    "home-nao-existe" => "§4essa Home nao existe",
    "publica-nao-existe" => "§4essa publica nao existe",
    "publica-apagada" => "§ePublica apagada",
     ]
  ]);
  
$this->cfg->save();     
$this->warps->save();
$this->publicas->save();

    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	
      }
      
  public function onJoin(PlayerJoinEvent $ev){
		$player = $ev->getPlayer();
		$n = $player->getName();
		
if(!is_file($this->getDataFolder()."players/".$player->getName().".yml")){
  
$this->homes[$player->getName()] = new Config($this->getDataFolder()."players/".$player->getName().".yml", Config::YAML);
  
}else {
  $this->homes[$player->getName()] = new Config($this->getDataFolder()."players/".$player->getName().".yml", Config::YAML);
}
        		}
        		
  
  public function onDeath(PlayerDeathEvent $ev){
    $player = $ev->getPlayer();
    
    $x = $player->getX();
    $y = $player->getY();
    $z = $player->getZ();
    $lvl = $player->getLevel();
    
    $this->homes[$player->getName()]->set(
    "back", [
      
       "x" => $x,
       "y" => $y,
       "z" => $z,
       "mundo" => $lvl,
      ]);
  }

public function onCommand(CommandSender $player, Command $cmd, string $label, array $args): bool{
  switch ($cmd->getName()) {
    case 'home':
      
if (isset($args[0])) {
  
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();

$time = 3;

     if ($this->homes[$player->getName()]->exists($args[0])) {   
       
    $data = $this->homes[$player->getName()]->get($args[0]);
    
		$level = $this->getServer()->getLevelByName($data["mundo"]);
		
	/*	$player->teleport(new Position($data["x"], $data["y"], $data["z"], $level));*/
$player->sendMessage("§aTeletransportado...");
$task = $this->getScheduler()->scheduleRepeatingTask(new Task($this, $player, $time, $data, $level), 20);
	
			
     }else {
      $player->sendMessage($this->cfg->get("msg")["home-nao-existe"]);
      if ($this->homes[$player->getName()]->exists("homes")) {
      $player->sendMessage("§eSuas Homes:  ".$this->homes[$player->getName()]->get("homes"));
      }
    }

}else {
  $player->sendMessage("use /home < nome >");
}      
      
      break;
          
     case 'sethome':
      
if (isset($args[0])) {
  
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();

if (!$this->homes[$player->getName()]->exists($args[0])) {
  $this->homes[$player->getName()]->set(
    $args[0], [
      
       "x" => $x,
       "y" => $y,
       "z" => $z,
       "mundo" => $lvl->getName()
      ]);
      
  if (!$this->homes[$player->getName()]->exists("homes")) {
$this->homes[$player->getName()]->set("homes", $args[0]);
  }else {
$this->homes[$player->getName()]->set("homes", $this->homes[$player->getName()]->get("homes")." ".$args[0]);
  }
  
  $this->homes[$player->getName()]->save();
  $player->sendMessage($this->cfg->get("msg")["home-setada"]);
}else {
  
  $player->sendMessage($this->cfg->get("msg")["home-existente"]);
  
}

}else {
  $player->sendMessage("use /sethome < nome >");
}      
      
      break; 
      
     case 'delhome':
       
      if (isset($args[0])) {
  
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();

if ($this->homes[$player->getName()]->exists($args[0])) {
  
  $this->homes[$player->getName()]->remove($args[0]);
 
 $ex = explode($args[0], $this->homes[$player->getName()]->get("homes"));
 
$v2 = false;
for ($i = 0; $i < 50; $i++) {
   if (isset($ex[$i])) {
    if ($v2 == false) {
    $this->homes[$player->getName()]->set("homes", $ex[$i]);
   $v2 = true;
      }else {
       $this->homes[$player->getName()]->set("homes", $this->homes[$player->getName()]->get("homes")." ".$ex[$i]);
      }
   }
}  
  $this->homes[$player->getName()]->save();
  $player->sendMessage($this->cfg->get("msg")["home-apagada"]);
}else {
  
  $player->sendMessage($this->cfg->get("msg")["home-nao-existe"]);
  
}

}else {
  $player->sendMessage("use /delhome < nome >");
}      
      break; 
      
     case 'publica':
      
 if (isset($args[0])) {
   
   if ($this->publicas->exists($args[0])) {
     
    $data = $this->publicas->get($args[0]);
    
		$level = $this->getServer()->getLevelByName($data["mundo"]);
	
     $player->sendMessage("§aTeletransportado para ".$args[0]);
     $player->teleport(new Position($data["x"], $data["y"], $data["z"], $level));
     
   }else {
           $player->sendMessage("§eHomes Publicas:  ".$this->publicas->get("homes"));
  
   }
   
 }else {
   $player->sendMessage("use /publica < nome >");
 }     
      
      break;
      
     case 'setpublica':
if (isset($args[0])) {


if ($this->homes[$player->getName()]->exists($args[0])) {
 
 $data = $this->homes[$player->getName()];
 
$x = $data->get($args[0])["x"];
$y = $data->get($args[0])["y"];
$z = $data->get($args[0])["z"];
$lvl = $data->get($args[0])["mundo"];

if(!$this->publicas->exists($args[0])){
  
  $this->publicas->set(
    $args[0], [
      
       "x" => $x,
       "y" => $y,
       "z" => $z,
       "mundo" => $lvl,
       "dono" => $player->getName()
      ]);
  
  
    if (!$this->publicas->exists("homes")) {
$this->publicas->set("homes", $args[0]);
  }else {
$this->publicas->set("homes", $this->publicas->get("homes")." ".$args[0]);
  }
  
  $this->publicas->save();
  
  $player->sendMessage($this->cfg->get("msg")["publica-setada"]);
}else {
  $player->sendMessage("§4Ja existe uma home publica com esse nome");
}
}else {
  $player->sendMessage($this->cfg->get("msg")["publica-nao-existe"]);
}
}else {
  $player->sendMessage("use /setpublica < home >");
}  
      break; 
      
     case 'delpublica':
      
       if (isset($args[0])) {
  
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();

if ($this->publicas->exists($args[0])) {
  
 if($this->publicas->get($args[0])["dono"] === $player->getName()){ 
  
  $this->publicas->remove($args[0]);
  
 $ex = explode($args[0], $this->publicas->get("homes"));

$v2 = false;
for ($i = 0; $i < 50; $i++) {
   if (isset($ex[$i])) {
    if ($v2 == false) {
    $this->publicas->set("homes", $ex[$i]);
   $v2 = true;
      }else {
       $this->publicas->set("homes", $this->publicas->get("homes")." ".$ex[$i]);
      }
   }
}  
  $this->publicas->save();
  $player->sendMessage($this->cfg->get("msg")["publica-apagada"]);
  
}else {
  $player->sendMessage("§4Você só pode apagar uma publica que seja sua");
}

}else {
  
  $player->sendMessage($this->cfg->get("msg")["publica-nao-existe"]);
  
}

}else {
  $player->sendMessage("use /delpublica < nome >");
}           
      
      
      break;
      
     case 'setwarp':
      
if ($player->hasPermission("setwa.use")) {
  
      if (isset($args[0])) {
      
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();

if (!$this->warps->exists($args[0])) {
  $this->warps->set(
    $args[0], [
      
       "x" => $x,
       "y" => $y,
       "z" => $z,
       "mundo" => $lvl->getName()
      ]);
  $this->warps->save();
  $player->sendMessage("§4Warp setada com sucesso!");
}else {
  
  $player->sendMessage("§4Essa Warp ja existe!");
  
}
   
    }else{
      $player->sendMessage("§4Coloque o nome da warp");
    }    
  
} else {
  $player->sendMessage("§4Você não tem permissão para usar este comando");
}
      
      
      break; 
      
     case 'delwarp':
      
   if($player->hasPermission("delw.cmd")){ 
          
  if (isset($args[0])) {
    
          
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();

    if ($this->warps->exists($args[0])) {
      
      $this->warps->remove($args[0]);
      $player->sendMessage("§4Warp Removida com sucesso");
      $this->warps->save();
    }else {
      $player->sendMessage("§4essa Warp não existe");
    }
    
  }else {
    $player->sendMessage("§4digite o nome da warp");
  }        
 }         
      
      
      break; 
      
     case 'warp':
  
        if (isset($args[0])) {

$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$lvl = $player->getLevel();
 
     if ($this->warps->exists($args[0])) {   
       
    $data = $this->warps->get($args[0]);
    
		$level = $this->getServer()->getLevelByName($data["mundo"]);
		
		$player->teleport(new Position($data["x"], $data["y"], $data["z"], $level));

			
     }else {
      $player->sendMessage("§4essa Warp não existe");
    }
          
        }else {
    $player->sendMessage("§4digite o nome da warp");
  }       
      
      break; 
      
     case 'tpa':
       
 if (isset($args[0])) {
 
$this->tpa = $player->getName();
$this->tpc = $args[0]; 
 
if ($this->getServer()->getPlayer($this->tpc) instanceof Player) {
  
if (!isset($this->cod[$player->getName()])) {
$this->cod[$player->getName()] = time() + 5;
$this->getServer()->getPlayer($this->tpc)->sendMessage("§eO Jogador ".$player->getName()." te Mandou uma solicitação de tpa");  
$this->getServer()->getPlayer($this->tpc)->sendMessage("§ause /aceitar para aceitar");
$this->getServer()->getPlayer($this->tpc)->sendMessage("§4use /negar para negar");

}else {
  if (time() < $this->cod[$player->getName()]) {
    $time = $this->cod[$player->getName()] - time();
    $player->sendMessage("§aAguarde ".$time);
  }else{
    unset($this->cod[$player->getName()]);
  }
}

}else {
  $player->sendMessage("§4Não a nenhum player online com este nome");
}
 }else {
   $player->sendMessage("use /tpa <player>");
 }
      break; 
      
     case 'aceitar':
       
       if (isset($this->cod[$this->tpa])) {

if ($this->getServer()->getPlayer($this->tpa) instanceof Player) {
$x = $player->getX();
$y = $player->getY();
$z = $player->getZ();
$level = $player->getLevel();

	$this->getServer()->getPlayer($this->tpa)->teleport(new Position($x, $y, $z, $level));
	unset($this->cod[$this->tpa]);
}

       }else {
         $player->sendMessage("§esolicitação expirada");
       }
      
      break;
      
    case 'negar':
	unset($this->cod[$this->tpa]);
      break;
      
    case 'tpahere':
 if (isset($args[0])) {
 
$this->tpa = $player->getName();
$this->tpc = $args[0]; 
 
if ($this->getServer()->getPlayer($this->tpc) instanceof Player) {
  
if (!isset($this->cod[$player->getName()])) {
$this->cod[$player->getName()] = time() + 5;
$this->getServer()->getPlayer($this->tpc)->sendMessage("§eO Jogador ".$player->getName()." te Mandou uma solicitação de tpahere");  
$this->getServer()->getPlayer($this->tpc)->sendMessage("§ause /here para aceitar");
$this->getServer()->getPlayer($this->tpc)->sendMessage("§aexpira em 5s");

}else {
  if (time() < $this->cod[$player->getName()]) {
    $time = $this->cod[$player->getName()] - time();
    $player->sendMessage("§aAguarde ".$time);
  }else{
    unset($this->cod[$player->getName()]);
  }
}

}else {
  $player->sendMessage("§4Não a nenhum player online com este nome");
}
 }else {
   $player->sendMessage("use /tpahere <player>");
 }
      break;
    case 'here':
       if (isset($this->cod[$this->tpa])) {

if ($this->getServer()->getPlayer($this->tpa) instanceof Player) {
$p = $this->getServer()->getPlayer($this->tpa);
$x = $p->getX();
$y = $p->getY();
$z = $p->getZ();
$level = $p->getLevel();

$player->teleport(new Position($x, $y, $z, $level));
	unset($this->cod[$this->tpa]);
}

       }else {
         $player->sendMessage("§esolicitação expirada");
       }
      break;
    
    case 'back':
      
   if($player->hasPermission("back.cmd")){
if ($this->homes[$player->getName()]->exists("back")) {
$data = $this->homes[$player->getName()]->get("back");
$x = $data["x"];
$y = $data["y"];
$z = $data["z"];
$lvl = $data["mundo"];

$player->teleport(new Position($x, $y, $z, $lvl));
$player->sendMessage("§ateletransportado com sucesso");
}     
   }else {
     $player->sendMessage("§avocê não tem permissão paar usar este commando");
   }
      break;
        }
  return true;
    }
}
