<?php
$this->extend('layout_assets');
$this->css()->add('page.css');
$this->js()->append('page.js');
?>
<main><?= $this->fragment($this->controller(\Bdf\Templating\_files\TestKernel::class.'::renderTemplate', ['template' => 'fragment_assets'])) ?></main>
