php
<?php

// Display error message if it exists
if (isset($error)) {
    echo "<div class='alert alert-danger' role='alert'>$error</div>";
}

// Display success message if it exists
if (isset($success)) {
    echo "<div class='alert alert-success' role='alert'>$success</div>";
}
?>

<div class="container mt-5">
    <div class="row justify-content-center"> <!-- Center the content -->
        <div class="col-md-6">  <!-- Set column width -->
            <div class="card"> <!-- Set card -->
                <div class="card-header">  <!-- Title -->
                    <h3 class="text-center">Reset Password</h3> 
                </div>
                <div class="card-body"> <!-- Form body -->
                    <!-- Form for reset password -->
                    <form action="" method="post">
                        <div class="mb-3">
                            <!-- New password input -->
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <!-- Confirm new password input -->
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                         <!-- Submit button -->
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>