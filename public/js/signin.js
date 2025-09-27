function signIn() {
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const errorDiv = document.getElementById("error-message");

    if (!email || !password) {
        errorDiv.textContent = "Email dan password wajib diisi.";
        return;
    }

    fetch('http://localhost:8000/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ Email: email, Password: password })
    })
    .then(res => {
        if (!res.ok) throw new Error("Login gagal");
        return res.json();
    })
    .then(data => {
        // Simpan seluruh data user dan token ke localStorage
        localStorage.setItem('user_id', data.user.User_ID);
        localStorage.setItem('user_image_profile', data.user.Profile_Image);
        localStorage.setItem('user_characters', data.user.Character_ID);
        localStorage.setItem('username', data.user.Username);
        localStorage.setItem('email', data.user.Email);
        localStorage.setItem('token', data.token); // JWT API key

        window.location.href = 'home.html';
    })
    .catch(() => {
        errorDiv.textContent = "Login gagal. Pastikan email dan password benar.";
    });
}