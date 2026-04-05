document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    if (roleSelect) {
        roleSelect.addEventListener('change', function() {
            const donorFields = document.getElementById('donorFields');
            const hospitalFields = document.getElementById('hospitalFields');
            if (this.value === 'donor') {
                donorFields.classList.remove('hidden');
                hospitalFields.classList.add('hidden');
            } else if (this.value === 'hospital') {
                donorFields.classList.add('hidden');
                hospitalFields.classList.remove('hidden');
            } else {
                donorFields.classList.add('hidden');
                hospitalFields.classList.add('hidden');
            }
        });
    }
});

function validateRegister() {
    let role = document.getElementById('role').value;
    let email = document.getElementById('email').value;
    let password = document.getElementById('password').value;

    if (!role || !email || !password || password.length < 6) {
        alert('Please fill all required fields and ensure password is at least 6 characters.');
        return false;
    }

    if (role === 'donor') {
        let name = document.getElementById('donor_name').value;
        let blood_group = document.getElementById('blood_group').value;
        let age = document.getElementById('age').value;
        let location = document.getElementById('location').value;
        let phone = document.getElementById('phone').value;

        if (!name || !blood_group || !age || !location || !phone) {
            alert('Please fill all donor fields.');
            return false;
        }
    }

    if (role === 'hospital') {
        let hospital_name = document.getElementById('hospital_name').value;
        let hospital_location = document.getElementById('hospital_location').value;
        let hospital_phone = document.getElementById('hospital_phone').value;

        if (!hospital_name || !hospital_location || !hospital_phone) {
            alert('Please fill all hospital fields.');
            return false;
        }
    }

    return true;
}

function validateLogin() {
    let email = document.getElementById('login_email').value;
    let password = document.getElementById('login_password').value;

    if (!email || !password) {
        alert('Please enter both email and password.');
        return false;
    }
    return true;
}
