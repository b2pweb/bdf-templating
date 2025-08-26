<?php
$this->extend(self::LAYOUT);

echo $this->render('with-layout', ['name' => 'John']);
echo ' ';
echo $this->name;