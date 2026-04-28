<?php

// 1. СТАН
interface NodeState { public function render(LightNode $node): string; }
class ActiveState implements NodeState { public function render(LightNode $node): string { return $node->defaultRender(); } }
class HiddenState implements NodeState { public function render(LightNode $node): string { return ""; } }

// 2. ВІДВІДУВАЧ
interface NodeVisitor {
    public function visitElement(LightElementNode $node): void;
    public function visitText(LightTextNode $node): void;
}

// 3. БАЗОВИЙ КЛАС
abstract class LightNode {
    protected $state;
    public function __construct() { $this->state = new ActiveState(); }
    public function setState(NodeState $state) { $this->state = $state; }

    // ШАБЛОННИЙ МЕТОД
    public final function renderOuter(): string {
        $this->onBeforeRender();
        $result = $this->state->render($this);
        $this->onAfterRender();
        return $result;
    }

    protected function onBeforeRender(): void {}
    protected function onAfterRender(): void {}

    abstract public function accept(NodeVisitor $visitor): void;
    abstract public function defaultRender(): string;
    abstract public function getNodeInfo(): string;
}

// 4. КЛАСИ ВУЗЛІВ
class LightTextNode extends LightNode {
    private $text;
    public function __construct($t) { parent::__construct(); $this->text = $t; }

    public function accept(NodeVisitor $visitor): void {
        $visitor->visitText($this);
    }

    public function defaultRender(): string { return $this->text; }
    public function getNodeInfo(): string { return "TextNode: '{$this->text}'"; }
}

class LightElementNode extends LightNode {
    private $tag, $display, $single, $classes = [], $children = [];

    public function __construct($t, $d, $s = false, $c = []) {
        parent::__construct();
        $this->tag = $t; $this->display = $d; $this->single = $s; $this->classes = $c;
    }

    public function getTag(): string { return $this->tag; }

    public function accept(NodeVisitor $visitor): void {
        $visitor->visitElement($this);
        foreach ($this->children as $child) {
            $child->accept($visitor);
        }
    }

    public function addChild(LightNode $n) { $this->children[] = $n; }
    public function removeLastChild(): void { array_pop($this->children); }
    public function getChildren(): array { return $this->children; }

    public function defaultRender(): string {
        $cls = count($this->classes) ? ' class="'.implode(' ', $this->classes).'"' : '';
        if ($this->single) return "<{$this->tag}{$cls} />" . ($this->display == 'block' ? "\n" : "");
        $inner = implode('', array_map(fn($c) => $c->renderOuter(), $this->children));
        return "<{$this->tag}{$cls}>{$inner}</{$this->tag}>" . ($this->display == 'block' ? "\n" : "");
    }

    public function getNodeInfo(): string { return "ElementNode: <{$this->tag}>"; }
}


// 5. КОНКРЕТНИЙ ВІДВІДУВАЧ
class ElementCounterVisitor implements NodeVisitor {
    private $counts = [];
    public function visitElement(LightElementNode $node): void {
        $tag = $node->getTag();
        $this->counts[$tag] = ($this->counts[$tag] ?? 0) + 1;
    }
    public function visitText(LightTextNode $node): void {
        $this->counts['text_nodes'] = ($this->counts['text_nodes'] ?? 0) + 1;
    }
    public function getReport(): string {
        $res = "Статистика:\n";
        foreach ($this->counts as $tag => $count) $res .= "- {$tag}: {$count}\n";
        return $res;
    }
}


echo "--- Тест патерна Visitor ---\n";
$root = new LightElementNode("div", "block");
$root->addChild(new LightElementNode("p", "block"));
$root->addChild(new LightTextNode("Привіт"));
$root->addChild(new LightTextNode("Світ"));

$visitor = new ElementCounterVisitor();
$root->accept($visitor);
echo $visitor->getReport();