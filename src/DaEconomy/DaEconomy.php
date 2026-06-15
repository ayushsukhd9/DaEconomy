<?php

declare(strict_types=1);

namespace DaEconomy;

use DaEconomy\command\AddMoneyCommand;
use DaEconomy\command\MoneyCommand;
use DaEconomy\command\RemoveMoneyCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class DaEconomy extends PluginBase {

    private static self $instance;
    private Config $database;
    /** @var array<string, int> */
    private array $balances = [];

    protected function onEnable(): void {
        self::$instance = $this;
        $this->saveDefaultConfig();

        $this->database = new Config($this->getDataFolder() . "players.yml", Config::YAML);
        $this->balances = array_change_key_case($this->database->getAll(), CASE_LOWER);

        $commandMap = $this->getServer()->getCommandMap();
        $commandMap->registerAll("daeconomy", [
            new MoneyCommand($this),
            new AddMoneyCommand($this),
            new RemoveMoneyCommand($this)
        ]);
    }

    protected function onDisable(): void {
        $this->database->setAll($this->balances);
        $this->database->save();
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getBalance(string $player): int {
        $player = strtolower($player);
        return $this->balances[$player] ?? (int)$this->getConfig()->get("starting-money", 1000);
    }

    public function setBalance(string $player, int $amount): void {
        $player = strtolower($player);
        $this->balances[$player] = max(0, $amount);
    }

    public function addBalance(string $player, int $amount): void {
        if ($amount <= 0) return;
        $this->setBalance($player, $this->getBalance($player) + $amount);
    }

    public function removeBalance(string $player, int $amount): void {
        if ($amount <= 0) return;
        $this->setBalance($player, $this->getBalance($player) - $amount);
    }

    public function formatMoney(int $amount): string {
        $symbol = $this->getConfig()->get("currency-symbol", "$");
        return $symbol . number_format($amount);
    }
}
