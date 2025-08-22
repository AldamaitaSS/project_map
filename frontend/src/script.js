document.addEventListener('DOMContentLoaded', function() {
    showLogin();
});

function showLogin() {
    document.querySelector('.login-container').style.display = 'block';
    document.querySelector('.dashboard-container').style.display = 'none';
}

function showDashboard() {
    document.querySelector('.login-container').style.display = 'none';
    document.querySelector('.dashboard-container').style.display = 'block';
}

function showProject() {
    document.querySelector('.login-container').style.display = 'none';
    document.querySelector('.dashboard-container').style.display = 'none';
    document.querySelector('.project-container').style.display = 'block';
}

// Event listener untuk login button
document.querySelector('.btn-primary').addEventListener('click', function(e) {
    e.preventDefault();
    showDashboard();
});