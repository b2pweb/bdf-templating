<?php
/** @var \Bdf\Templating\PhpEngine $this */
$this->css()->add('style.css');
$this->js()->append('app.js');
?>
<head><?= $this->css()->render() ?></head>
<body><?= $this->js()->render() ?></body>
<img src="<?= $this->asset()->getUrl('logo.png') ?>" />
<script src="<?= $this->asset('js')->getUrl('other.js') ?>"></script>
<link href="<?= $this->asset('css')->getUrl('other.css') ?>" />
