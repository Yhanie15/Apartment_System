<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile Form</title>
    <link rel="stylesheet" href="profile_form.css">
</head>
<body>

    <!-- Edit Profile Button -->
    <button id="editProfileBtn">Edit Profile</button>

    <!-- Profile Form Pop-up -->
    <div id="profileFormPopup" class="popup-form">
        <h2>Complete this form first!</h2>
        <div class="profile-pic">
            <label for="profilePic">
                <img src="default-avatar.png" alt="Upload Profile Picture">
            </label>
            <input type="file" id="profilePic" accept="image/*">
        </div>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <input type="text" name="first_name" placeholder="First name *" required>
                <input type="text" name="middle_name" placeholder="Middle name">
                <input type="text" name="last_name" placeholder="Last name *" required>
                <input type="text" name="ext_name" placeholder="Ext. name">
            </div>
            
            <div class="form-group">
                <input type="date" name="birth_date" placeholder="Birth date *" required>
                <input type="text" name="gender" placeholder="Gender *" required>
                <input type="number" name="age" placeholder="Age *" required>
            </div>

            <div class="form-group">
                <input type="text" name="address" placeholder="Address *" required>
                <input type="email" name="email" placeholder="Email *" required>
                <input type="text" name="phone_number" placeholder="Phone number *" required>
            </div>

            <div class="form-group">
                <label for="validID">Valid ID *</label>
                <input type="file" name="valid_id" id="validID" accept="image/*" required>
            </div>

            <div class="form-group">
                <input type="text" name="emergency_name" placeholder="Emergency contact name *" required>
                <input type="text" name="relationship" placeholder="Relationship *" required>
                <input type="text" name="emergency_contact" placeholder="Emergency contact number *" required>
            </div>

            <button type="submit" name="save" class="save-btn">Save</button>
        </form>
    </div>

    <script>
        // JavaScript for handling the pop-up form
        const editProfileBtn = document.getElementById('editProfileBtn');
        const profileFormPopup = document.getElementById('profileFormPopup');

        // Show the form when the edit button is clicked
        editProfileBtn.addEventListener('click', () => {
            profileFormPopup.classList.toggle('active');
        });
    </script>

    <?php
    // PHP Code for form submission handling
    if (isset($_POST['save'])) {
        $firstName = $_POST['first_name'];
        $middleName = $_POST['middle_name'];
        $lastName = $_POST['last_name'];
        $extName = $_POST['ext_name'];
        $birthDate = $_POST['birth_date'];
        $gender = $_POST['gender'];
        $age = $_POST['age'];
        $address = $_POST['address'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phone_number'];
        $emergencyName = $_POST['emergency_name'];
        $relationship = $_POST['relationship'];
        $emergencyContact = $_POST['emergency_contact'];

        // Example of saving uploaded files (profile picture and valid ID)
        $profilePic = $_FILES['profilePic']['name'];
        $validID = $_FILES['valid_id']['name'];
        $uploadDir = 'uploads/';

        // Move uploaded files to the 'uploads' directory
        move_uploaded_file($_FILES['profilePic']['tmp_name'], $uploadDir . $profilePic);
        move_uploaded_file($_FILES['valid_id']['tmp_name'], $uploadDir . $validID);

        // Display a success message (You can replace this with database insertion code)
        echo "<script>alert('Profile information saved successfully!');</script>";
    }
    ?>
</body>
</html>
