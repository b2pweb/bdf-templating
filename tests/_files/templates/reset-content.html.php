<?php
$this->extend('reset-layout');
$this->js()->appendTag('content();');
echo $this->render('reset-sub');
