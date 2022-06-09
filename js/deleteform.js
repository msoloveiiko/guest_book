const deleteBtns = document.querySelectorAll('.guest-delete');
const modalDelete = document.querySelector('.form-delete');
const cancelBtn = document.querySelector('.form-cancel');
const idItem = document.querySelector('.form-id-item');
deleteBtns.forEach(deleteBtn => {
  deleteBtn.addEventListener('click', () => {
    modalDelete.classList.add('modalDelete_active');
    idItem.value = deleteBtn.dataset.itemid;
  })
});

cancelBtn.addEventListener('click', (e) => {
  e.preventDefault();
  modalDelete.classList.remove('modalDelete_active');
});
