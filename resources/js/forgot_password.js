import { post } from './ajax';

window.addEventListener('DOMContentLoaded', function () {
    const forgotPasswordBtn = document.querySelector('.forgot-password-btn')
    const resetPasswordBtn  = document.querySelector('.reset-password-btn')

    if (forgotPasswordBtn) {
        forgotPasswordBtn.addEventListener('click', function () {
            const form  = document.querySelector('.forgot-password-form')
            const email = form.querySelector('input[name="email"]').value

            post('/forgot-password', {email}, form).then(response => {
                if (response.ok) {
                    alert('An email with instructions to reset your password has been sent.');

                    window.location = '/login'
                }
            })
        })
    }

    if (resetPasswordBtn) {
        resetPasswordBtn.addEventListener('click', function () {
            const form     = this.closest('form')
            const formData = new FormData(form);
            const data     = Object.fromEntries(formData.entries());

            post(form.action, data, form).then(response => {
                if (response.ok) {
                    alert('Password has been updated successfully.');

                    window.location = '/login'
                }
            })
        })
    }
})
