<?php

// Базовий логер (цільовий інтерфейс)
class Logger {
    public function log($message) {
        echo "\033[32m[ЛОГ]: $message\033[0m\n"; // Зелений
    }
    public function error($message) {
        echo "\033[31m[ПОМИЛКА]: $message\033[0m\n"; // Червоний
    }
    public function warn($message) {
        echo "\033[33m[ПОПЕРЕДЖЕННЯ]: $message\033[0m\n"; // Жовтий
    }
}

// Клас, який ми адаптуємо
class FileWriter {
    public function write($text) {
        echo "Запис тексту у файл: $text";
    }
    public function writeLine($text) {
        echo "Запис рядка у лог-файл: $text" . PHP_EOL;
    }
}

// Адаптер
class FileLoggerAdapter extends Logger {
    private $fileWriter;

    public function __construct(FileWriter $fileWriter) {
        $this->fileWriter = $fileWriter;
    }

    public function log($message) {
        $this->fileWriter->writeLine("[ЛОГ] " . date('H:i:s') . ": $message");
    }

    public function error($message) {
        $this->fileWriter->writeLine("[ПОМИЛКА] " . date('H:i:s') . ": $message");
    }

    public function warn($message) {
        $this->fileWriter->writeLine("[ПОПЕРЕДЖЕННЯ] " . date('H:i:s') . ": $message");
    }
}

echo "--- Завдання 1: Адаптер ---\n";
$writer = new FileWriter();
$adapter = new FileLoggerAdapter($writer);

$adapter->log("Система запущена.");
$adapter->warn("Мало вільного місця.");
$adapter->error("Помилка бази даних.");