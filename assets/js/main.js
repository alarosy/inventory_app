// تأكيد قبل إرسال أي نموذج فيه الخاصية data-confirm (مثل الحذف)
document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
        if (!confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

// معاينة الصورة قبل رفعها، والتحقق في الخادم يبقى هو الأساس
document.querySelectorAll('input[type="file"][data-preview]').forEach(function (input) {
    var preview = document.getElementById(input.dataset.preview);
    if (!preview) {
        return;
    }
    var originalSrc = preview.src;

    input.addEventListener('change', function () {
        if (input.files && input.files[0]) {
            preview.src = URL.createObjectURL(input.files[0]);
        } else {
            preview.src = originalSrc;
        }
    });
});
