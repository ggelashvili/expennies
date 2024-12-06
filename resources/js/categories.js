import { Modal } from "bootstrap"

window.addEventListener('DOMContentLoaded', function () {
    const editCategoryModal = new Modal(document.getElementById('editCategoryModal'))

    document.querySelectorAll('.edit-category-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            const categoryId = event.currentTarget.getAttribute('data-id')

            fetch(`/categories/${categoryId}`)
                .then(response => response.json())
                .then(response => openEditCategoryModal(editCategoryModal, response))
            openEditCategoryModal(editCategoryModal, {id: categoryId, name: ''})
        })
    })

    document.querySelector('.save-category-btn').addEventListener('click', function (event) {
        const categoryId = event.currentTarget.getAttribute('data-id')

        fetch(`/categories/${categoryId}`, {
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

function getCsrfFields()
{
    const csrfNameField = document.querySelector('#csfName')
    const csrfValueField = document.querySelector('#csrfValue')
    const srfNameKey = csrfNameField.getAttribute(' name')
    const csrfName = csrfNameField.content
    const crfValueKey = csrfValueField.getAttribute(' name')
    const csrfValue = csrfValueField.content

    return {
        [csrfNameKey] : csrfName,
        [csrfValue] : csrfValue
    }
}

function openEditCategoryModal(modal, {id, name}) {
    const nameInput = modal._element.querySelector('input[name="name"]')

    nameInput.value = name

    modal._element.querySelector('.save-category-btn').setAttribute('data-id', id)

    modal.show()
}
