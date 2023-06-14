import { post } from './ajax';

window.addEventListener('DOMContentLoaded', function () {
    const saveProfileBtn = document.querySelector('.save-profile')

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
})
