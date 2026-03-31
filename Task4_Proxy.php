<?php

interface TextReader { public function read(string $path): array; }

class SmartTextReader implements TextReader {
    public function read(string $path): array {
        $lines = explode(PHP_EOL, file_get_contents($path));
        return array_map('str_split', $lines);
    }
}

class SmartTextChecker implements TextReader {
    private $reader;
    public function __construct(TextReader $r) { $this->reader = $r; }
    public function read($path): array {
        echo "Відкриття $path...\n";
        $res = $this->reader->read($path);
        echo "Прочитано. Рядків: ".count($res).", Символів: ".array_sum(array_map('count', $res))."\n";
        return $res;
    }
}

class SmartTextReaderLocker implements TextReader {
    private $reader; private $regex;
    public function __construct($r, $re) { $this->reader = $r; $this->regex = $re; }
    public function read($path): array {
        if (preg_match($this->regex, $path)) { echo "Access denied!\n"; return []; }
        return $this->reader->read($path);
    }
}

echo "--- Завдання 4: Проксі ---\n";
file_put_contents("test.txt", "Hello\nWorld");
$proxy = new SmartTextReaderLocker(new SmartTextChecker(new SmartTextReader()), '/secret/i');
$proxy->read("test.txt");