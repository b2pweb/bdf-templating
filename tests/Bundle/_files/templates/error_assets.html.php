<?php
$this->extend('layout_assets');
$this->js()->append('error.js');
throw new \RuntimeException('Error in template');
