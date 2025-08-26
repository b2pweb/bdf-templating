<?php
/** @var \Bdf\Templating\PhpEngine&\Bdf\Templating\Extensions\Translation&\Bdf\Templating\Extensions\Url&\Bdf\Templating\Bundle\_files\MyExtension&object{name: string} $this */
$this->extend(self::LAYOUT);
?>
<h1><?= $this->_('Home page') ?></h1>
<p><?= $this->_('Hello %s!', $this->e($this->name)) ?></p>
<p><?= $this->myHelper()->test() ?></p>
<p><?= $this->foo() ?></p>
