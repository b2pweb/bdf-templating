<?php
$this->extend('with-sidemenu');

echo $this->name;

$this->parts()->open('sidemenu');
echo 'item1 - item2';
$this->parts()->close();
