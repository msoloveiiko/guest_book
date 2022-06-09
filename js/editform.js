const editBtns = document.querySelectorAll('.guest-edit');
const modalEdit = document.querySelector('.guest-form-edit');
const cancelEditBtn = document.querySelector('.form-cancel-edit');
const idItemEdit = document.querySelector('.form-id-item-edit');
const nameItems = document.querySelectorAll('.your-name');
const emailItems = document.querySelectorAll('.your-email-link');
const numberItems = document.querySelectorAll('.mobile-number');
const messageItems = document.querySelectorAll('.message');
const nameEdit = document.querySelector('.name-form-edit');
const emailEdit = document.querySelector('.email-form-edit');
const numberEdit = document.querySelector('.number-form-edit');
const messageEdit = document.querySelector('.message-form-edit');


editBtns.forEach(editBtn => {
  editBtn.addEventListener('click', () => {
    modalEdit.classList.add('modalEdit_active');
    idItemEdit.value = editBtn.dataset.itemid;
    nameItems.forEach(name => {
      if(name.dataset.itemid === editBtn.dataset.itemid){
        nameEdit.value = name.textContent;
      }
    });
    emailItems.forEach(email => {
      if(email.dataset.itemid === editBtn.dataset.itemid){
        emailEdit.value = email.outerText;
      }
    });
    numberItems.forEach(numberItem => {
      if(numberItem.dataset.itemid === editBtn.dataset.itemid){
        numberEdit.value = numberItem.outerText;
      }
    });
    messageItems.forEach(messageItem => {
      if(messageItem.dataset.itemid === editBtn.dataset.itemid){
        messageEdit.value = messageItem.outerText;
      }
    });
  })
});

cancelEditBtn.addEventListener('click', (e) => {
  e.preventDefault();
  modalEdit.classList.remove('modalEdit_active');
});
