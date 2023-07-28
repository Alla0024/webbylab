<?php
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_POST['submit'])) {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $user = User::getUserByEmail($email);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['Logged'] = 1;
        $_SESSION['email'] = $email;
        $_SESSION["id"] = $user['id'];
        header('Location: index.php?action=movies');
        exit();
    } else {
        $errorMessage = "Incorrectly entered password or email.";
    }
}

if (isset($successMessage)) { ?>
    <div class="alert alert-success">
        <?php echo $successMessage; ?>
    </div>
<?php } elseif (isset($errorMessage)) { ?>
    <div class="alert alert-danger">
        <?php echo $errorMessage; ?>
    </div>
<?php } ?>

<div class="wrapper fadeInDown">
    <div id="formContent">
        <div class="fadeIn first">
            <img src="images/users_icon.png" id="icon" alt="User Icon" />
        </div>
        <form action="" method="POST">
            <input type="text" id="email" class="fadeIn second" name="email" placeholder="Enter email">
            <input type="password" id="password" class="fadeIn third" name="password" placeholder="Enter password">
            <input type="submit" class="fadeIn fourth" name="submit" value="Log In">
        </form>
        <div id="formFooter">
            <a class="underlineHover" href="index.php?action=registration">Registration</a>
        </div>
    </div>
</div>
