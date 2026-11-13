<?php
$this->js()->appendTag('error();');
throw new \RuntimeException('Error in template');
