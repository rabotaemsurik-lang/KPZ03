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

    public function __construct(protected Hero $hero) {}
    public function getDescription(): string {
        return $this->hero->getDescription();
    }

    public function getPower(): int {
        return $this->hero->getPower();
    }
}

class SwordDecorator extends InventoryDecorator {
    public function getDescription(): string {
        return parent::getDescription() . " + Меч";
    }
    public function getPower(): int {
        return parent::getPower() + 10;
    }
}

class ArmorDecorator extends InventoryDecorator {
    public function getDescription(): string {
        return parent::getDescription() . " + Броня";
    }
    public function getPower(): int {
        return parent::getPower() + 20;
    }
}


echo "--- Завдання 2: Декоратор ---\n";

$hero = new Palladin();                // Базовий Паладин (12)
$hero = new ArmorDecorator($hero);     // Паладин в Броні (12 + 20 = 32)
$hero = new SwordDecorator($hero);     // Паладин в Броні та з Мечем (32 + 10 = 42)

echo "Герой: " . $hero->getDescription() . " (Сила: " . $hero->getPower() . ")\n";