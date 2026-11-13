<p>User: <?= $this->e($this->session()->get('user')) ?></p>
<?php foreach ($this->flash()->get('success') as $message): ?>
<p class="success"><?= $this->e($message) ?></p>
<?php endforeach ?>
