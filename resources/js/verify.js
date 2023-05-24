import { post } from './ajax';

window.addEventListener('DOMContentLoaded', function () {
    document.querySelector('.resend-verify').addEventListener('click', function (event) {
        post(`/verify`).then(response => {
            if (response.ok) {
                alert('A new email verification has been sent')
            }
        })
    })
})
