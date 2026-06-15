<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;

class AddMoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("addmoney", "Add money to a player balance", "/addmoney <player> <amount>", []);
        $this->setPermission("daeconomy.command.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (count($args) < 2) {
            $sender->sendMessage(TF::RED . "Usage: " . $this->getUsage());
            return false;
        }

        $amount = (int) $args[1];
        
        if ($amount <= 0) {
            $sender->sendMessage(TF::RED . "Please enter a valid amount greater than zero.");
            return false;
        }

        $target = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
        $realName = $target !== null ? $target->getName() : $args[0];

        $this->plugin->addBalance($realName, $amount);
        $sender->sendMessage(TF::GREEN . "Successfully added " . $this->plugin->formatMoney($amount) . " to " . $realName);
        
        $target?->sendMessage(TF::GREEN . "You received " . $this->plugin->formatMoney($amount) . ".");
        
        return true;
    }
}
