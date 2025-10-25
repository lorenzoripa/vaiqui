<?php
session_start();

// Distruggi la sessione
session_destroy();

// Reindirizza alla pagina di login
header('Location: index.php');
exit();
?>
