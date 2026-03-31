<?php

interface Hero {
    public function getDescription(): string;
    public function getPower(): int;
}

class Warrior implements Hero {
    public function getDescription(): string { return "Воїн"; }
    public function getPower(): int { return 15; }
}

class Mage implements Hero {
    public function getDescription(): string { return "Маг"; }
    public function getPower(): int { return 5; }
}

class Palladin implements Hero {
    public function getDescription(): string { return "Паладин"; }
    public function getPower(): int { return 12; }
}

abstract class InventoryDecorator implements Hero {
    protected $hero;
    public function __construct(Hero $hero) { $this->hero = $hero; }
}

class SwordDecorator extends InventoryDecorator {
    public function getDescription(): string { return $this->hero->getDescription() . " + Меч"; }
    public function getPower(): int { return $this->hero->getPower() + 10; }
}

class ArmorDecorator extends InventoryDecorator {
    public function getDescription(): string { return $this->hero->getDescription() . " + Броня"; }
    public function getPower(): int { return $this->hero->getPower() + 20; }
}

echo "--- Завдання 2: Декоратор ---\n";
$hero = new Palladin();
$hero = new ArmorDecorator($hero);
$hero = new SwordDecorator($hero);
echo "Герой: " . $hero->getDescription() . " (Сила: " . $hero->getPower() . ")\n";