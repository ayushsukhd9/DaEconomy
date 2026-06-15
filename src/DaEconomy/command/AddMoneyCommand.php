<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class AddMoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("addmoney", "Add money to a player balance", "/addmoney <player> <amount>", []);
        $this->setPermission("daeconomy.command.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }

        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return false;
        }

        $targetName = $args[0];
        $amount = filter_var($args[1], FILTER_VALIDATE_INT);

        if ($amount === false || $amount <= 0) {
            $sender->sendMessage(TextFormat::RED . "Please enter a valid positive integer.");
            return false;
        }

        $target = $this->plugin->getServer()->getPlayerByPrefix($targetName);
        $realName = $target !== null ? $target->getName() : $targetName;

        $this->plugin->addBalance($realName, $amount);
        $sender->sendMessage(TextFormat::GREEN . "Successfully added " . $this->plugin->formatMoney($amount) . " to " . $realName);
        
        if ($target !== null) {
            $target->sendMessage(TextFormat::GREEN . "You received " . $this->plugin->formatMoney($amount) . ".");
        }
        return true;
    }
}
