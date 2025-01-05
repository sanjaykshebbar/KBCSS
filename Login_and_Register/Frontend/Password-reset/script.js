// Show the email input popup
function showEmailPrompt() {
    document.getElementById('emailPopup').style.display = 'block';
}

// Close the email popup
function closeEmailPopup() {
    document.getElementById('emailPopup').style.display = 'none';
}

// Show the OTP popup
function showOtpPopup() {
    document.getElementById('otpPopup').style.display = 'block';
}

// Close the OTP popup
function closeOtpPopup() {
    document.getElementById('otpPopup').style.display = 'none';
}

// Validate email and check in the database
function checkEmail() {
    const email = document.getElementById('emailInput').value;
    const emailError = document.getElementById('emailError');

    if (email === "") {
        emailError.textContent = "Please enter your email.";
        emailError.style.display = "block";
        return;
    }

    // Send the email to the server for verification
    fetch('check_email.php', {
        method: 'POST',
        body: JSON.stringify({ email: email }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.exists) {
            // Email exists, show OTP popup
            showOtpPopup();
        } else {
            emailError.textContent = "Email not found in the database.";
            emailError.style.display = "block";
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Verify OTP entered by the user
function verifyOtp() {
    const otp = document.getElementById('otpInput').value;
    const otpError = document.getElementById('otpError');

    if (otp === "") {
        otpError.textContent = "Please enter the OTP.";
        otpError.style.display = "block";
        return;
    }

    // Send OTP to the server for validation
    fetch('verify_otp.php', {
        method: 'POST',
        body: JSON.stringify({ otp: otp }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            // OTP valid, show password reset form
            document.getElementById('passwordResetContainer').style.display = 'block';
            closeOtpPopup();
        } else {
            otpError.textContent = "Invalid OTP.";
            otpError.style.display = "block";
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

// Reset the password
function resetPassword() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const passwordError = document.getElementById('passwordError');

    if (newPassword !== confirmPassword) {
        passwordError.textContent = "Passwords do not match.";
        passwordError.style.display = "block";
        return;
    }

    // Send the new password to the server to update
    fetch('reset_password.php', {
        method: 'POST',
        body: JSON.stringify({ newPassword: newPassword }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirect to login page
            window.location.href = 'login.php';
        } else {
            passwordError.textContent = "Error resetting password.";
            passwordError.style.display = "block";
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
