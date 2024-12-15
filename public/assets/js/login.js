// Variable Declaration
const loginBtn = document.querySelector("#login"); // login btn
const registerBtn = document.querySelector("#register"); // btn register
const loginForm = document.querySelector(".login-form"); // login 
const registerForm = document.querySelector(".register-form"); // register 
const signUpLink = document.querySelector(".Sign_link a"); // Selecting the "Sign Up Here" link

// Login button function
loginBtn.addEventListener('click', () => {
    loginBtn.style.backgroundColor = "#212640";
    registerBtn.style.backgroundColor = "rgba(225,225,225,0.2)";
    
    loginForm.style.left = "50%";
    registerForm.style.left = "-50%";

    loginForm.style.opacity = 1;
    registerForm.style.opacity = 0;

    document.querySelector(".col-1").style.borderRadius = "0% 30% 20% 0";
});

// Register button function
registerBtn.addEventListener('click', () => {
    loginBtn.style.backgroundColor = "rgba(225,225,225,0.2)";
    registerBtn.style.backgroundColor = "#212640";

    loginForm.style.left = "150%";
    registerForm.style.left = "50%";

    loginForm.style.opacity = 0;
    registerForm.style.opacity = 1;

    document.querySelector(".col-1").style.borderRadius = "0 20% 30% 0";
});

// Sign Up link function (same as clicking the register button)
signUpLink.addEventListener('click', (e) => {
    e.preventDefault(); // Prevents the default anchor behavior

    loginBtn.style.backgroundColor = "rgba(225,225,225,0.2)";
    registerBtn.style.backgroundColor = "#212640";

    loginForm.style.left = "150%";
    registerForm.style.left = "50%";

    loginForm.style.opacity = 0;
    registerForm.style.opacity = 1;
    document.querySelector(".col-1").style.borderRadius = "0 20% 30% 0";
});

// Handle Login form submission
document.querySelector('.login-form .input-submit').addEventListener('click', (event) => {
    event.preventDefault(); // Prevent default form submission

    const username = document.querySelector('.login-form input[name="username"]').value;
    const password = document.querySelector('.login-form input[name="password"]').value;

    const data = { username, password };

    fetch('/STOCKUP/Backend/public/main.php?module=Auth&action=login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
        credentials: 'include', // Include cookies for session handling
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Login successful!');
            // Redirect to another page or perform any other actions
            window.location.href = '/dashboard'; // Example redirect
        } else {
            alert('Login failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});

// Handle Registration form submission
document.querySelector('.register-form .input-submit').addEventListener('click', (event) => {
    event.preventDefault(); // Prevent default form submission

    const fullName = document.querySelector('.register-form input[name="full_name"]').value;
    const username = document.querySelector('.register-form input[name="username"]').value;
    const password = document.querySelector('.register-form input[name="password"]').value;
    const confirmPassword = document.querySelector('.register-form input[name="confirm_password"]').value;

    if (password !== confirmPassword) {
        alert('Passwords do not match!');
        return;
    }

    const data = { full_name: fullName, username, password };

    fetch('/STOCKUP/Backend/public/main.php?module=Auth&action=register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
        credentials: 'include', // Include cookies for session handling
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Registration successful!');
            // Redirect to login page or automatically log in the user
            window.location.href = '/login'; // Example redirect
        } else {
            alert('Registration failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
