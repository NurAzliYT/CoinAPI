<?php

namespace NurAzliYT\CoinAPI\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use NurAzliYT\CoinAPI\CoinAPI;

class SetCoinCommand extends Command{
    private $plugin;

    public function __construct(CoinAPI $plugin){
        $desc = $plugin->getCommandMessage("setcoin");
        parent::__construct("setcoin", $desc["description"], $desc["usage"]);

        $this->setPermission("coinapi.command.setcoin");

        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $label, array $params): bool{
        if(!$this->plugin->isEnabled()) return false;
        if(!$this->testPermission($sender)){
            return false;
        }

        $player = array_shift($params);
        $amount = array_shift($params);

        if(!is_numeric($amount)){
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return true;
        }

        if(($p = $this->plugin->getServer()->getPlayerByPrefix($player)) instanceof Player){
            $player = $p->getName();
        }

        $result = $this->plugin->setCoin($player, $amount);
        switch($result){
            case CoinAPI::RET_INVALID:
            $sender->sendMessage($this->plugin->getMessage("setcoin-invalid-number", [$amount], $sender->getName()));
            break;
            case CoinAPI::RET_NO_ACCOUNT:
            $sender->sendMessage($this->plugin->getMessage("player-never-connected", [$player], $sender->getName()));
            break;
            case CoinAPI::RET_CANCELLED:
            $sender->sendMessage($this->plugin->getMessage("setcoin-failed", [], $sender->getName()));
            break;
            case CoinAPI::RET_SUCCESS:
            $sender->sendMessage($this->plugin->getMessage("setcoin-setcoin", [$player, $amount], $sender->getName()));

            if($p instanceof Player){
                $p->sendMessage($this->plugin->getMessage("setcoin-set", [$amount], $p->getName()));
            }
            break;
            default:
            $sender->sendMessage("...");
        }
        return true;
    }
}
