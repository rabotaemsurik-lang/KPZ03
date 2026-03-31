<?php

interface Renderer {
    public function renderShape(string $name);
}

class VectorRenderer implements Renderer {
    public function renderShape($name) { echo "Drawing $name as vectors\n"; }
}

class RasterRenderer implements Renderer {
    public function renderShape($name) { echo "Drawing $name as pixels\n"; }
}

abstract class Shape {
    protected $renderer;
    public function __construct(Renderer $r) { $this->renderer = $r; }
    abstract public function draw();
}

class Circle extends Shape { public function draw() { $this->renderer->renderShape("Circle"); } }
class Square extends Shape { public function draw() { $this->renderer->renderShape("Square"); } }
class Triangle extends Shape { public function draw() { $this->renderer->renderShape("Triangle"); } }

echo "--- Завдання 3: Міст ---\n";
(new Circle(new VectorRenderer()))->draw();
(new Triangle(new RasterRenderer()))->draw();