<?php

class ElementType {
    public $tag, $display;
    public function __construct($t, $d) { $this->tag = $t; $this->display = $d; }
}

class FlyweightFactory {
    private static $types = [];
    public static function getType($t, $d) {
        $key = $t.$d;
        return self::$types[$key] ??= new ElementType($t, $d);
    }
}

class LightElement {
    private $type, $content;
    public function __construct($t, $d, $c) {
        $this->type = FlyweightFactory::getType($t, $d);
        $this->content = $c;
    }
    public function render() { return "<{$this->type->tag}>{$this->content}</{$this->type->tag}>\n"; }
}

echo "--- Завдання 6: Легковаговик ---\n";
$lines = ["Назва Книги", "Короткий рядок", " З відступом", "Звичайний довгий текст"];
$start = memory_get_usage();
$tree = [];
foreach ($lines as $i => $l) {
    if ($i == 0) $tree[] = new LightElement("h1", "block", $l);
    elseif (mb_strlen($l) < 20) $tree[] = new LightElement("h2", "block", $l);
    elseif ($l[0] === ' ') $tree[] = new LightElement("blockquote", "block", trim($l));
    else $tree[] = new LightElement("p", "block", $l);
}
foreach ($tree as $n) echo $n->render();
echo "Пам'ять: " . (memory_get_usage() - $start) . " байт\n";