document.addEventListener("DOMContentLoaded", function() {
  const fileInput = document.getElementById('file');
  const progressBar = document.getElementById('progressBar');
  const checkingLoader = document.getElementById('checkingLoader');
  const uploadLoader = document.getElementById('uploadLoader');
  const fileTableBody = document.querySelector('#fileTable tbody');

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length === 0) return;

    // نمایش لودر بررسی فایل
    checkingLoader.style.display = 'block';

    // شبیه‌سازی تاخیر بررسی (برای مثال اعتبارسنجی فایل)
    setTimeout(() => {
      checkingLoader.style.display = 'none';
      // نمایش لودر آپلود
      uploadLoader.style.display = 'block';
      uploadFile(fileInput.files[0]);
    }, 1000);
  });

  function uploadFile(file) {
    const formData = new FormData();
    formData.append('file', file);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', '', true);

    // به‌روزرسانی نوار پیشرفت بر اساس درصد آپلود
    xhr.upload.onprogress = function(event) {
      if (event.lengthComputable) {
        const percentComplete = Math.round((event.loaded / event.total) * 100);
        progressBar.style.width = percentComplete + '%';
        progressBar.innerText = percentComplete + '%'; // نمایش درصد داخل نوار
      }
    };

    xhr.onload = function() {
      if (xhr.status === 200) {
        const response = JSON.parse(xhr.responseText);
        if (response.status === "success") {
          progressBar.style.width = '100%';
          setTimeout(() => {
            uploadLoader.style.display = 'none';
            addFileRow(response);
          }, 500);
        } else {
          alert(response.message);
          uploadLoader.style.display = 'none';
        }
      } else {
        alert('خطا در برقراری ارتباط با سرور.');
        uploadLoader.style.display = 'none';
      }
    };

    xhr.send(formData);
  }

  function addFileRow(fileData) {
    const row = document.createElement('tr');
    // لینک دانلود براساس نام فایل (در صورت تغییر مسیر ممکن است نیاز به بروزرسانی داشته باشد)
    const downloadLink = 'uploads/' + fileData.filename;
    row.innerHTML = `
      <td>${fileData.filename}</td>
      <td>${fileData.mimeType}</td>
      <td>${fileData.uploadTime}</td>
      <td>${fileData.modifyTime}</td>
      <td>${fileData.uniqueId}</td>
      <td><button class="download-btn" data-link="${downloadLink}">Download</button></td>
    `;
    fileTableBody.appendChild(row);
    document.getElementById('uploadForm').reset();
  }

  // سایر کدهای مربوط به مدیریت دانلود و مدال...
});