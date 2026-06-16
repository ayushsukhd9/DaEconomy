<?php

declare(strict_types=1);

namespace DaEconomy\provider;

interface Provider {
    public function open(): void;
    
    // Add these two new lines!
    public function loadAccount(string $xuid): void;
    public function unloadAccount(string $xuid): void;

    public function accountExists(string $xuid): bool;
    public function createAccount(string $xuid, int $defaultMoney = 1000): bool;
    public function removeAccount(string $xuid): bool;
    public function getMoney(string $xuid): int|bool;
    public function setMoney(string $xuid, int $amount): bool;
    public function addMoney(string $xuid, int $amount): bool;
    public function reduceMoney(string $xuid, int $amount): bool;
    public function getAll(): array;
    public function getName(): string;
    public function save(): void;
    public function close(): void;
}
