<?php $this->extend(self::LAYOUT); ?>
<?= $this->fragment($this->controller(\Bdf\Templating\_files\TestKernel::class.'::renderTemplate', ['template' => 'hello'], ['name' => 'Bob'])) ?>
<?= $this->fragment('/render/hello?name=Alice') ?>
