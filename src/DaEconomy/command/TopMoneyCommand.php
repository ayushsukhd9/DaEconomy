<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat as TF;

class TopMoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("topmoney", "View the richest players on the server", "/topmoney", ["balancetop", "baltop"]);
        $this->setPermission("daeconomy.command.topmoney");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        // Grab the entire filing cabinet of every account on the server
        $allBalances = $this->plugin->getProvider()->getAll();

        // If the server is brand new and nobody has money yet
        if (empty($allBalances)) {
            $sender->sendMessage(TF::RED . "There are no bank accounts on the server yet!");
            return false;
        }

        // arsort() automatically sorts the list from highest number to lowest number
        arsort($allBalances);

        // array_slice cuts the massive list down to just the top 10
        $topTen = array_slice($allBalances, 0, 10, true);

        $sender->sendMessage(TF::GREEN . "=== " . TF::YELLOW . "Richest Players" . TF::GREEN . " ===");

        $rank = 1;
        foreach ($topTen as $xuid => $balance) {
            // Because we save XUIDs instead of names for security, we display the XUID and the balance.
            // (In a massive network, you would link this to a Name-History database!)
            $sender->sendMessage(TF::AQUA . "#" . $rank . TF::GRAY . " - ID: " . substr($xuid, 0, 6) . "... " . TF::WHITE . "-> " . TF::GREEN . "$" . number_format((float)$balance));
            $rank++;
        }

        $sender->sendMessage(TF::GREEN . "======================");

        return true;
    }
}
