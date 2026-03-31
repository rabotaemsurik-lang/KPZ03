<?php

abstract class LightNode { abstract public function renderOuter(): string; }

class LightTextNode extends LightNode {
    private $text;
    public function __construct($t) { $this->text = $t; }
    public function renderOuter(): string { return $this->text; }
}

class LightElementNode extends LightNode {
    private $tag, $display, $single, $classes = [], $children = [];

    public function __construct($t, $d, $s = false, $c = []) {
        $this->tag = $t; $this->display = $d; $this->single = $s; $this->classes = $c;
    }

    public function addChild(LightNode $n) { $this->children[] = $n; }
    public function getChildrenCount() { return count($this->children); }

    public function renderInner(): string {
        return implode('', array_map(fn($c) => $c->renderOuter(), $this->children));
    }

    public function renderOuter(): string {
        $cls = count($this->classes) ? ' class="'.implode(' ', $this->classes).'"' : '';
        if ($this->single) return "<{$this->tag}{$cls} />" . ($this->display == 'block' ? "\n" : "");
        $res = "<{$this->tag}{$cls}>" . $this->renderInner() . "</{$this->tag}>";
        return $this->display == 'block' ? $res . "\n" : $res;
    }
}

echo "--- Завдання 5: Компонувальник ---\n";
$ul = new LightElementNode("ul", "block");
$li = new LightElementNode("li", "block");
$li->addChild(new LightTextNode("Елемент списку"));
$ul->addChild($li);
echo "Дітей у списку: " . $ul->getChildrenCount() . "\n";
echo $ul->renderOuter();