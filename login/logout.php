<?php
session_start();
session_destroy();
header("Location: ../login/login.php"); // Adjusted path to redirect to login.php
exit();
