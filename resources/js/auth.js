import "../css/auth.scss"
import { post }  from './ajax';
import { Modal } from 'bootstrap';

window.addEventListener('DOMContentLoaded', function () {
    const twoFactorAuthModal = new Modal(document.getElementById('twoFactorAuthModal'))

    document.querySelector('.log-in-btn').addEventListener('click', function (event) {
        const form     = this.closest('form')
        const formData = new FormData(form);
        const inputs   = Object.fromEntries(formData.entries());

        post(form.action, inputs, form).then(response => response.json()).then(response => {
            if (response.two_factor) {
                twoFactorAuthModal.show()
            } else {
                window.location = '/'
            }
        })
    })

    document.querySelector('.log-in-two-factor').addEventListener('click', function (event) {
        const code  = twoFactorAuthModal._element.querySelector('input[name="code"]').value
        const email = document.querySelector('.login-form input[name="email"]').value

        post('/login/two-factor', {email, code}, twoFactorAuthModal._element).then(response => {
            if (response.ok) {
                window.location = '/'
            }
        })
    })
})
