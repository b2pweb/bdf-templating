<?php

use Laminas\Escaper\Escaper;

// Create alias on Zend Escaper for compatibility
/**
 * @deprecated Use Laminas\Escaper\Escaper instead
 */
class_alias(Escaper::class, 'Zend\Escaper\Escaper');
