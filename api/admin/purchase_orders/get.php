<?php
require_once __DIR__ . '/../../../app/controllers/PurchaseOrderController.php';

$controller = new PurchaseOrderController();
$id = $_GET['id'] ?? '';
$controller->getPurchaseOrder($id);
