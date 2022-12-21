import { Modal } from "bootstrap"

window.addEventListener('DOMContentLoaded', function () {
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))

    document.querySelectorAll('.edit-category-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            const categoryId = event.currentTarget.getAttribute('data-id')

            fetch(`/categories/${ categoryId }`)
                .then(response => response.json())
                .then(response => openEditCategoryModal(editCategoryModal, response))
        })
    })

    document.querySelector('.save-category-btn').addEventListener('click', function (event) {
        const categoryId = event.currentTarget.getAttribute('data-id')

        fetch(`/categories/${ categoryId }`, {
            method: 'POST',
            body: JSON.stringify({
                name: editCategoryModal._element.querySelector('input[name="name"]').value,
                ...getCsrfFields()
            }),
            headers: {
                'Content-Type': 'application/json'
            }
        }).then(response => response.json())
          .then(response => {
            console.log(response)
        })
    })
})

function getCsrfFields() {
    const csrfNameField  = document.querySelector('#csrfName')
    const csrfValueField = document.querySelector('#csrfValue')
    const csrfNameKey    = csrfNameField.getAttribute('name')
    const csrfName       = csrfNameField.content
    const csrfValueKey   = csrfValueField.getAttribute('name')
    const csrfValue      = csrfValueField.content

    return {
        [csrfNameKey]: csrfName,
        [csrfValueKey]: csrfValue
    }
}

function openEditCategoryModal(modal, {id, name}) {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)

    modal.show()
}
