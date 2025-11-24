<?php
session_start();
require_once 'vendor/autoload.php';

$id = $_GET['id'] ?? null;
$videoclub = unserialize($_SESSION['videoclub']);

foreach ($videoclub->socios as $i => $cliente) {
    if ($cliente->getNumero() == $id) {
        unset($videoclub->socios[$i]);
        $videoclub->socios = array_values($videoclub->socios);
        break;
    }
}
$_SESSION['videoclub'] = serialize($videoclub);

header('Location: mainAdmin.php');
exit;