<?php

declare(strict_types=1);

namespace DaEconomy\listener;

use DaEconomy\DaEconomy;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerListener implements Listener {

    public function __construct(private DaEconomy $plugin) {}

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();
        $xuid = $player->getXuid();
        $name = $player->getName();

        // Save the player's name linked to their XUID in our cache
        $this->plugin->getNameCache()->set($xuid, $name);
    }
}
