<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
<title>Send SMS </title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
<link rel="stylesheet" href="styles.css"> <!-- Make sure styles.css is updated to include nav styles -->
<link rel="stylesheet" href="JRSLCSS/sms.css">
<body> 
<fieldset>
<!-- Sidebar navigation -->
<?php include 'sidebar.php'; ?>
<legend>Send SMS</legend>
<form action="send.php" method="POST">

<div>
<input type="text" name="phoneNumber" required> 
<span>Phone Number</span>
</div>
<div>
<input type="text" name="message" required>
<span>Message</span>
</div>
<button class="btnSend">Send</button>
</form>
</fieldset>
</body>
</html>