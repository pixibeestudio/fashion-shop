<?php
require_once __DIR__ . '/../../../app/controllers/OrderController.php';

$controller = new OrderController();
$controller->updateStatus();
