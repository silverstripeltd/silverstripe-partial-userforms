const duplicateFields = (event) => {
    event.preventDefault();
    const repeatButton = event.target;
    const hiddenInput = repeatButton.parentNode.querySelector('input[type=hidden]');
    hiddenInput.value = hiddenInput.value ? parseInt(hiddenInput.value) + 1 : 1;
    toggleRepeatedFields(repeatButton);
};

const toggleRepeatedFields = (repeatButton) => {
    const buttonContainer = repeatButton.parentNode;
    const mainContainer = buttonContainer.parentNode;
    const destination = mainContainer.querySelector('.repeat-destination');
    const hiddenInput = buttonContainer.querySelector('input[type=hidden]');
    const hiddenData = JSON.parse(repeatButton.getAttribute('data'));
    if (!hiddenData) {
        return;
    }
    Object.keys(hiddenData).forEach(function (index) {
        for (var i = 1; i <= parseInt(hiddenData[index]); i++) {
            var fieldName = '#' + index + '__' + i;
            var clonedField = destination.querySelector(fieldName);
            if (i <= parseInt(hiddenInput.value)) {
                clonedField.style.display = 'block';
            } else {
                clonedField.style.display = 'none';
            }
            if (parseInt(hiddenInput.value) >= parseInt(hiddenData[index])) {
                buttonContainer.style.display = 'none';
            }
        }
    });
};

window.addEventListener('DOMContentLoaded', (event) => {
    const form = document.body.querySelector('form.userform');
    const repeatButton = form.querySelectorAll('button.btn-add-more');

    if (repeatButton.length) {
        for (let index = 0; index < repeatButton.length; index++) {
            repeatButton[index].addEventListener('click', duplicateFields);
            toggleRepeatedFields(repeatButton[index]);
        }
    }
});