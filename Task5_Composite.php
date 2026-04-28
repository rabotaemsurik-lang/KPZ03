<?php

abstract class LightNode {
    abstract public function renderOuter(): string;
    // Додаємо абстрактний метод для зручного виводу вузла під час ітерації
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

    public function __construct(LightNode $root) {
        $this->stack[] = $root;
    }

    public function hasNext(): bool {
        return !empty($this->stack);
    }

    public function next(): ?LightNode {
        if (!$this->hasNext()) return null;

        $current = array_pop($this->stack);

        if ($current instanceof LightElementNode) {
            $children = $current->getChildren();
            // Додаємо в стек з кінця, щоб обхід йшов зліва направо
            for ($i = count($children) - 1; $i >= 0; $i--) {
                $this->stack[] = $children[$i];
            }
        }

        return $current;
    }
}

// Ітератор в ширину BFS - Breadth-First Search
// Використовує Чергу Queue - FIFO
class BreadthFirstIterator implements HtmlIterator {
    private $queue = [];

    public function __construct(LightNode $root) {
        $this->queue[] = $root;
    }

    public function hasNext(): bool {
        return !empty($this->queue);
    }

    public function next(): ?LightNode {
        if (!$this->hasNext()) return null;

        $current = array_shift($this->queue);

        if ($current instanceof LightElementNode) {
            foreach ($current->getChildren() as $child) {
                $this->queue[] = $child;
            }
        }

        return $current;
    }
}




$html = new LightElementNode("html", "block");
$body = new LightElementNode("body", "block");
$h1 = new LightElementNode("h1", "block");
$h1->addChild(new LightTextNode("Заголовок сайту"));
$ul = new LightElementNode("ul", "block");
$li1 = new LightElementNode("li", "block");
$li1->addChild(new LightTextNode("Перший пункт"));
$li2 = new LightElementNode("li", "block");
$li2->addChild(new LightTextNode("Другий пункт"));

$ul->addChild($li1);
$ul->addChild($li2);
$body->addChild($h1);
$body->addChild($ul);
$html->addChild($body);

echo "--- Обхід дерева в глибину (DFS) ---\n";
$dfs = new DepthFirstIterator($html);
while ($dfs->hasNext()) {
    echo $dfs->next()->getNodeInfo() . "\n";
}

echo "\n--- Обхід дерева в ширину (BFS) ---\n";
$bfs = new BreadthFirstIterator($html);
while ($bfs->hasNext()) {
    echo $bfs->next()->getNodeInfo() . "\n";
}