<?php
namespace Jibix\AntiInternalKick\utils;
use Jibix\AntiInternalKick\Main;
use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\EncapsulatedPacket;
use pocketmine\network\mcpe\RakLibInterface;
use pocketmine\Player;


/**
 * Class Main
 * @package Jibix\AntiInternalKick\utils
 * @author Jibix
 * @date 29.08.2021 - 17:37
 * @project AntiInternalKick
 */
class ModifiedRakLib extends RakLibInterface{

    public function handleEncapsulated(string $identifier, EncapsulatedPacket $packet, int $flags) : void{
        if(isset($this->players[$identifier])){
            //get this now for blocking in case the player was closed before the exception was raised
            $player = $this->players[$identifier];
            $address = $player->getAddress();
            try{
                if($packet->buffer !== ""){
                    $pk = new BatchPacket($packet->buffer);
                    $player->handleDataPacket($pk);
                }
            }catch(\Throwable $e){
                $config = Main::getInstance()->getConfig()->getAll();

                if ($config['throw-error']) {
                    $logger = $this->server->getLogger();
                    $logger->debug("Packet " . (isset($pk) ? get_class($pk) : "unknown") . ": " . base64_encode($packet->buffer));
                    $logger->logException($e);
                }

                if ($player instanceof Player) {
                    if ($config['send-message'])
                        $player->sendMessage($config['message']);
                    if ($config['send-title'])
                        $player->sendMessage($config['title']);
                }
                //$this->interface->blockAddress($address, 5);
            }
        }
    }
    
}
