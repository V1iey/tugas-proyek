<?php require 'config.php'; redirect(empty($_SESSION['user']) ? 'login.php' : 'dashboard.php');
