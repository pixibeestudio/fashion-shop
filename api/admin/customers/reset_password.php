<?php
require_once __DIR__ . '/../../../app/controllers/CustomerController.php';

$controller = new CustomerController();
$controller->resetPassword();
