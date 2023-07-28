<?php
$error['errors'] = [];
if (isset($_POST['submit'])) {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password1 = isset($_POST['password1']) ? ($_POST['password1']) : '';
    $password2 = isset($_POST['password2']) ? ($_POST['password2']) : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';

    if (Valid::validateInput($username, '/^([a-zA-Z]+(?:(?! {2})[a-zA-Z\'\-])*[a-zA-Z]+)$|^([а-яА-ЯієїІЄЇґҐ]+(?:(?! {2})[а-яА-ЯієїІЄЇґҐ\'\-])*[а-яА-ЯієїІЄЇґҐ]+)$/', "Invalid name input.")) {
        array_push($error['errors'], "Invalid name input. <br>");
    }
    if (Valid::validateInput($email, '/^[a-zA-Z0-9\.\-_]{2,}@[a-zA-Z0-9\-_]+\.[a-z]{2,3}$/', "Invalid email input.")) {
        array_push($error['errors'], "Invalid email input.<br>");
    }

    if (Valid::validateInput($phone, '/^([+]?[\s0-9]+)?(\d{3}|[(]?[0-9]+[)])?([-]?[\s]?[0-9])+$/', "Invalid phone number input.") && $phone) {
        array_push($error['errors'], "Invalid phone number input.<br>");
    }

    if (User::emailExists($email)) {
        array_push($error['errors'], 'Email already exists.<br>');
    }

    if (Valid::validateInput($password1, '/^(?=.*[0-9])(?=.*[a-zA-Z])(?=\S+$).{7,}|(?=.*[0-9])(?=.*[а-яА-ЯієїІЄЇґҐ])(?=\S+$).{7,}$/', "Password does not meet requirements.")) {
        array_push($error['errors'], "Password does not meet requirements.<br>");
    }

    if ($password1 != $password2) {
        array_push($error['errors'], "Passwords do not match.<br>");
    }

    if (empty($error['errors'])) {
        $password_hash = password_hash($password1, PASSWORD_BCRYPT);

        User::addUser($username, $password_hash, $email, $phone);

        $successMessage = "Registration successful! You can now log in with your credentials.";

        session_start();
        $_SESSION['success_message'] = $successMessage;
        header('Location: index.php?action=login');
    } else {
        header('Location: index.php?action=registration&' . http_build_query($error));
    }
} else {

    if (isset($_GET['errors'])) {
        foreach ($_GET['errors'] as $key => $value) { ?>
            <div class="alert alert-danger">
                <?= $value . ' '; ?>
            </div>
        <?php }
    }
    ?>

    <div class="wrapper fadeInDown">
        <div id="formContent">
            <div class="fadeIn first">
                <img src="images/users_icon.png" id="icon" alt="User Icon"/>
            </div>
            <div class="preg">
                <div class="container">
                    <form action="" method="POST">
                        <div class="dws-input">
                            <input type="text" name="username" placeholder="Enter name" required>
                        </div>
                        <div class="dws-input">
                            <input type="email" name="email" placeholder="Enter email" required>
                        </div>
                        <div class="dws-input">
                            <input type="text" name="phone" placeholder="Enter phone number">
                        </div>
                        <div class="dws-input">
                            <input type="password" name="password1" placeholder="Enter password" required>
                        </div>
                        <div class="dws-input">
                            <input type="password" name="password2" placeholder="Confirm password" required>
                        </div>
                        <input class="dws-submit fadeIn fourth" type="submit" name="submit" value="Register">
                        <br>
                    </form>
                </div>

            </div>
        </div>
    </div>
<?php }
