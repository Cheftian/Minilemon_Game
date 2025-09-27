document.getElementById("signupForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const form = document.getElementById("signupForm");
    const formData = new FormData(form);

    fetch('http://localhost:8000/users', {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error("Registration failed");
        return res.json();
    })
    .then(data => {
        alert("Registration successful");
        window.location.href = 'signin.html';
    })
    .catch(err => {
        alert("Registration failed");
        console.error(err);
    });
});