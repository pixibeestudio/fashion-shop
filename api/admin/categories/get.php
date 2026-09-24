<?php
require_once __DIR__ . '/../../../app/controllers/CategoryController.php';

$controller = new CategoryController();
$id = $_GET['id'] ?? '';
$controller->getCategory($id);
