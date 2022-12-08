window.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.edit-category-btn').forEach(button => {
        button.addEventListener('click', function (event) {
            const categoryId = event.currentTarget.getAttribute('data-id')

            // TODO
            console.log(categoryId)
        })
    })
})
