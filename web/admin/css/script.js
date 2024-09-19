const fileInput1 = document.getElementById('fileInput1');
const fileInput2 = document.getElementById('fileInput2');
const preview1 = document.getElementById('preview1');
const preview2 = document.getElementById('preview2');
const form = document.getElementById('uploadForm');

function checkAndSubmitForm() {
    if (fileInput1.files.length > 0 && fileInput2.files.length > 0) {
        form.submit();
    }
}

function previewFile(input, preview) {
    const file = input.files[0];
    const reader = new FileReader();

    reader.addEventListener("load", function () {
        preview.src = reader.result;
        preview.style.display = 'block';
    }, false);

    if (file) {
        reader.readAsDataURL(file);
    }
}

fileInput1.addEventListener('change', function() {
    previewFile(fileInput1, preview1);
    checkAndSubmitForm();
});

fileInput2.addEventListener('change', function() {
    previewFile(fileInput2, preview2);
    checkAndSubmitForm();
});