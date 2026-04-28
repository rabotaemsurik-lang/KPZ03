<?php

abstract class LightNode {
    abstract public function renderOuter(): string;
    abstract public function getNodeInfo(): string;
}

class LightTextNode extends LightNode {
    private $text;
    public function __construct($t) { $this->text = $t; }
    public function renderOuter(): string { return $this->text; }
    public function getNodeInfo(): string { return "TextNode: '{$this->text}'"; }
}

class LightElementNode extends LightNode {
    private $tag, $display, $single, $classes = [], $children = [];

    public function __construct($t, $d, $s = false, $c = []) {
        $this->tag = $t; $this->display = $d; $this->single = $s; $this->classes = $c;
    }

    public function addChild(LightNode $n) { $this->children[] = $n; }
    public function removeLastChild(): void {
        array_pop($this->children);
    }

    public function getChildrenCount() { return count($this->children); }
    public function getChildren(): array { return $this->children; }

    public function renderInner(): string {
        return implode('', array_map(fn($c) => $c->renderOuter(), $this->children));
    }

    public function renderOuter(): string {
        $cls = count($this->classes) ? ' class="'.implode(' ', $this->classes).'"' : '';
        if ($this->single) return "<{$this->tag}{$cls} />" . ($this->display == 'block' ? "\n" : "");
        $res = "<{$this->tag}{$cls}>" . $this->renderInner() . "</{$this->tag}>";
        return $this->display == 'block' ? $res . "\n" : $res;
    }

    public function getNodeInfo(): string { return "ElementNode: <{$this->tag}>"; }
}

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

class BreadthFirstIterator implements HtmlIterator {
    private $queue = [];
    public function __construct(LightNode $root) { $this->queue[] = $root; }
    public function hasNext(): bool { return !empty($this->queue); }
    public function next(): ?LightNode {
        if (!$this->hasNext()) return null;
        $current = array_shift($this->queue);
        if ($current instanceof LightElementNode) {
            foreach ($current->getChildren() as $child) { $this->queue[] = $child; }
        }
        return $current;
    }
}


interface Command {
    public function execute(): void;
    public function undo(): void;
}

class AddChildCommand implements Command {
    private $parent;
    private $child;

    public function __construct(LightElementNode $parent, LightNode $child) {
        $this->parent = $parent;
        $this->child = $child;
    }

    public function execute(): void {
        $this->parent->addChild($this->child);
    }

    public function undo(): void {
        echo "Скасування: видалення останнього вузла з <{$this->parent->getNodeInfo()}>\n";
        $this->parent->removeLastChild();
    }
}

class HtmlEditor {
    private $history = [];

    public function executeCommand(Command $command) {
        $command->execute();
        $this->history[] = $command;
    }

    public function undo() {
        if (!empty($this->history)) {
            $command = array_pop($this->history);
            $command->undo();
        }
    }
}



echo "\n--- Тест Команди ---\n";
$root = new LightElementNode("div", "block");
$editor = new HtmlEditor();
$editor->executeCommand(new AddChildCommand($root, new LightTextNode("Текст через команду")));
echo "Після додавання:\n" . $root->renderOuter();
$editor->undo();
echo "Після скасування (Undo):\n" . $root->renderOuter();