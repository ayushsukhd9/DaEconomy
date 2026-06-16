<?php

declare(strict_types=1);

namespace DaEconomy\provider;

use pocketmine\plugin\PluginBase;
use poggit\libasynql\libasynql;
use poggit\libasynql\DataConnector;

class MySQLProvider implements Provider {

    private DataConnector $database;
    /** @var array<string, int> */
    private array $balances = [];

    public function __construct(private PluginBase $plugin) {}

    public function open(): void {
        $this->database = libasynql::create($this->plugin, $this->plugin->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);

        $this->database->executeGeneric("economy.init");
        
        // BOOM. Memory bomb removed. We no longer load 50,000 offline players into RAM on startup.
    }

    // Called by the PlayerJoinEvent to load ONLY online players into RAM
    public function loadAccount(string $xuid): void {
        $this->database->executeSelect("economy.get", ["xuid" => $xuid], function(array $rows) use ($xuid): void {
            if (empty($rows)) {
                $this->createAccount($xuid);
            } else {
                $this->balances[$xuid] = (int) $rows[0]["balance"];
            }
        });
    }

    // Called by the PlayerQuitEvent to free up RAM
    public function unloadAccount(string $xuid): void {
        unset($this->balances[$xuid]);
    }

    public function accountExists(string $xuid): bool {
        return isset($this->balances[$xuid]);
    }

    public function createAccount(string $xuid, int $defaultMoney = 1000): bool {
        if ($this->accountExists($xuid)) return false;
        
        $this->balances[$xuid] = $defaultMoney;
        $this->database->executeInsert("economy.create", [
            "xuid" => $xuid,
            "balance" => $defaultMoney
        ]);
        return true;
    }

    public function removeAccount(string $xuid): bool {
        if (!$this->accountExists($xuid)) return false;
        
        unset($this->balances[$xuid]);
        $this->database->executeGeneric("economy.delete", ["xuid" => $xuid]);
        return true;
    }

    public function getMoney(string $xuid): int|bool {
        return $this->balances[$xuid] ?? false;
    }

    public function setMoney(string $xuid, int $amount): bool {
        if (!$this->accountExists($xuid)) return false;
        
        $this->balances[$xuid] = max(0, $amount);
        $this->database->executeChange("economy.set", [
            "xuid" => $xuid,
            "balance" => $this->balances[$xuid]
        ]);
        return true;
    }

    public function addMoney(string $xuid, int $amount): bool {
        if (!$this->accountExists($xuid) || $amount <= 0) return false;
        
        $this->balances[$xuid] += $amount;
        // DELTA QUERY: Safely adds money directly inside the SQL engine to prevent cross-server overrides
        $this->database->executeChange("economy.add", [
            "xuid" => $xuid,
            "amount" => $amount
        ]);
        return true;
    }

    public function loadAccount(string $xuid): void {
        // Not needed for YAML
    }

    public function unloadAccount(string $xuid): void {
        // Not needed for YAML
    }


    public function reduceMoney(string $xuid, int $amount): bool {
        if (!$this->accountExists($xuid) || $amount <= 0) return false;
        if ($this->balances[$xuid] < $amount) return false;
        
        $this->balances[$xuid] -= $amount;
        // DELTA QUERY: Safely reduces money directly inside the SQL engine
        $this->database->executeChange("economy.reduce", [
            "xuid" => $xuid,
            "amount" => $amount
        ]);
        return true;
    }

    public function getAll(): array {
        return $this->balances;
    }

    public function getName(): string {
        return "MySQL (libasynql - Delta Cached)";
    }

    public function save(): void {
        $this->database->waitAll();
    }

    public function close(): void {
        $this->database->close();
    }
}
