<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class MoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        // This tells the server the name of the command, the description, and the usage
        parent::__construct("money", "Check your bank balance", "/money", ["balance", "bal"]);
        $this->setPermission("daeconomy.command.money");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        // Security Check: Make sure a real player typed this, not the server console!
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Only players in the game can check their balance.");
            return false;
        }

        // Grab their permanent Xbox ID
        $xuid = $sender->getXuid();

        // If they don't have an account yet, create one for them instantly
        if (!$this->plugin->getProvider()->accountExists($xuid)) {
            $this->plugin->getProvider()->createAccount($xuid, 1000);
        }

        // Fetch their exact balance from our database engine
        $balance = $this->plugin->getProvider()->getMoney($xuid);

        // Send them the beautifully formatted message
        $sender->sendMessage(TF::GREEN . "Your Balance: " . TF::YELLOW . "$" . number_format((float)$balance));
        return true;
    }
}
