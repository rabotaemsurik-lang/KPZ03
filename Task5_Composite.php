<?php

//ПАТЕРН СТАН
interface NodeState {
    public function render(LightNode $node): string;
}

class ActiveState implements NodeState {
    public function render(LightNode $node): string {
        return $node->defaultRender();
    }
}

class HiddenState implements NodeState {
    public function render(LightNode $node): string {
        return "";
    }
}

// БАЗОВИЙ КЛАС
abstract class LightNode {
    protected $state;

    public function __construct() {
        $this->state = new ActiveState();
    }

    public function setState(NodeState $state) {
        $this->state = $state;
    }

    public function renderOuter(): string {
        return $this->state->render($this);
    }

    abstract public function defaultRender(): string;
    abstract public function getNodeInfo(): string;
}

// КЛАСИ ВУЗЛІВ
class LightTextNode extends LightNode {
    private $text;
    public function __construct($t) {
        parent::__construct();
        $this->text = $t;
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

    public function addChild(LightNode $n) { $this->children[] = $n; }
    public function removeLastChild(): void { array_pop($this->children); }
    public function getChildren(): array { return $this->children; }

    public function defaultRender(): string {
        $cls = count($this->classes) ? ' class="'.implode(' ', $this->classes).'"' : '';
        if ($this->single) return "<{$this->tag}{$cls} />" . ($this->display == 'block' ? "\n" : "");

        $inner = implode('', array_map(fn($c) => $c->renderOuter(), $this->children));
        $res = "<{$this->tag}{$cls}>" . $inner . "</{$this->tag}>";
        return $this->display == 'block' ? $res . "\n" : $res;
    }

    public function getNodeInfo(): string { return "ElementNode: <{$this->tag}>"; }
}

// ПАТЕРН ІТЕРАТОР
interface HtmlIterator {
    public function hasNext(): bool;
    public function next(): ?LightNode;
}

class DepthFirstIterator implements HtmlIterator {
    private $stack = [];
    public function __construct(LightNode $root) { $this->stack[] = $root; }
    public function hasNext(): bool { return !empty($this->stack); }
    public function next(): ?LightNode {
        if (!$this->hasNext()) return null;
        $current = array_pop($this->stack);
        if ($current instanceof LightElementNode) {
            $children = $current->getChildren();
            for ($i = count($children) - 1; $i >= 0; $i--) { $this->stack[] = $children[$i]; }
        }
        return $current;
    }
}

// ПАТЕРН КОМАНДА
interface Command {
    public function execute(): void;
    public function undo(): void;
}

class AddChildCommand implements Command {
    private $parent, $child;
    public function __construct(LightElementNode $parent, LightNode $child) {
        $this->parent = $parent; $this->child = $child;
    }
    public function execute(): void { $this->parent->addChild($this->child); }
    public function undo(): void { $this->parent->removeLastChild(); }
}

class HtmlEditor {
    private $history = [];
    public function executeCommand(Command $command) {
        $command->execute();
        $this->history[] = $command;
    }
    public function undo() {
        if (!empty($this->history)) { $command = array_pop($this->history); $command->undo(); }
    }
}


echo "--- Тест патерна State ---\n";
$root = new LightElementNode("div", "block", false, ["container"]);
$text = new LightTextNode("Цей текст можна приховати");
$root->addChild($text);

echo "1. Стан за замовчуванням (Active):\n";
echo $root->renderOuter();

echo "\n2. Змінюємо стан тексту на Hidden:\n";
$text->setState(new HiddenState());
echo $root->renderOuter();