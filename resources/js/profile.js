import { post } from './ajax';

window.addEventListener('DOMContentLoaded', function () {
    const saveProfileBtn = document.querySelector('.save-profile')
    const updatePasswordBtn = document.querySelector('.update-password')

    saveProfileBtn.addEventListener('click', function () {
        const form     = this.closest('form')
        const formData = new FormData(form);
        const data     = Object.fromEntries(formData.entries());

        saveProfileBtn.classList.add('disabled')

        post('/profile', data, form).then(response => {
            saveProfileBtn.classList.remove('disabled')

            if (response.ok) {
                alert('Profile has been updated.');
            }
        }).catch(() => {
            saveProfileBtn.classList.remove('disabled')
        })
    })

    updatePasswordBtn.addEventListener('click', function () {
        const form     = document.getElementById('passwordForm')
        const formData = new FormData(form);
        const data     = Object.fromEntries(formData.entries());

        updatePasswordBtn.classList.add('disabled')

        post('/profile/update-password', data, form).then(response => {
            updatePasswordBtn.classList.remove('disabled')

            if (response.ok) {
                alert('Password has been updated.');
            }
        }).catch(() => {
            updatePasswordBtn.classList.remove('disabled')
        })
    })
})
