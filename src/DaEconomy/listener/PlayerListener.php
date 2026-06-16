<?php

declare(strict_types=1);

namespace DaEconomy\listener;

use DaEconomy\DaEconomy;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

class PlayerListener implements Listener {

    public function __construct(private DaEconomy $plugin) {}

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $xuid = $player->getXuid();
        $name = $player->getName();

        // 1. Save their real name to our cache for the Leaderboard
        $this->plugin->getNameCache()->set($xuid, $name);

        // 2. Safely fetch their bank account from the MySQL database into RAM
        $this->plugin->getProvider()->loadAccount($xuid);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();
        $xuid = $player->getXuid();

        // 3. Delete their bank account from RAM to prevent memory leaks!
        $this->plugin->getProvider()->unloadAccount($xuid);
    }
}
