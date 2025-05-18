<?php

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Function to generate a unique 12-character security ID
function generateRandomId($length = 12) {
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

// Handle AJAX file upload request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $response = [];
    $file = $_FILES['file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $originalName = basename($file['name']);
        $targetFile = $uploadDir . '/' . $originalName;
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Generate security ID and store in a sidecar file
            $secFile = $targetFile . '.sec';
            $uniqueId = generateRandomId();
            file_put_contents($secFile, $uniqueId);
            // Prepare file details for response
            $mimeType = mime_content_type($targetFile);
            $uploadTime = date("Y-m-d H:i:s", filectime($targetFile));
            $modifyTime = date("Y-m-d H:i:s", filemtime($targetFile));
            $response = [
                "status" => "success",
                "filename" => $originalName,
                "mimeType" => $mimeType,
                "uploadTime" => $uploadTime,
                "modifyTime" => $modifyTime,
                "uniqueId" => $uniqueId
            ];
        } else {
            $response = ["status" => "error", "message" => "خطا در آپلود فایل."];
        }
    } else {
        $response = ["status" => "error", "message" => "خطای آپلود: " . $file['error']];
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>File Upload</title>
  <link rel="stylesheet" href="/commart-better-me/includes/css/filemanager.css">
  <style>
    /* Modal style */
    .modal {
      display: none; /* Hidden by default */
      position: fixed; 
      z-index: 1000; 
      left: 0;
      top: 0;
      width: 100%; 
      height: 100%;
      overflow: auto; 
      background-color: rgba(0,0,0,0.5); /* Gray background with opacity */
    }
    .modal-content {
      background-color: #fefefe;
      margin: 10% auto; 
      padding: 20px;
      border: 1px solid #888;
      width: 300px;
      border-radius: 5px;
      position: relative;
    }
    .close {
      color: #aaa;
      position: absolute;
      right: 10px;
      top: 5px;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    .close:hover,
    .close:focus {
      color: black;
      text-decoration: none;
      cursor: pointer;
    }
    /* Button styling for modal buttons */
    #copyLinkButton, #downloadButton {
      padding: 8px 12px;
      margin: 5px;
      cursor: pointer;
    }
    
    /* Existing styling adjustments */
    .file-upload-form {
      margin: 10px 0;
    }
  </style>
</head>
<body>
 <div id="uploadspace" class="uploadspace">
  <form class="file-upload-form" id="uploadForm">
    <label for="file" class="file-upload-label">
      <div class="file-upload-design">
        <svg viewBox="0 0 640 512" height="1em">
          <path d="M144 480C64.5 480 0 415.5 0 336c0-62.8 40.2-116.2 96.2-135.9c-.1-2.7-.2-5.4-.2-8.1c0-88.4 71.6-160 160-160c59.3 0 111 32.2 138.7 80.2C409.9 102 428.3 96 448 96c53 0 96 43 96 96c0 12.2-2.3 23.8-6.4 34.6C596 238.4 640 290.1 640 352c0 70.7-57.3 128-128 128H144zm79-217c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l39-39V392c0 13.3 10.7 24 24 24s24-10.7 24-24V257.9l39 39c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-80-80c-9.4-9.4-24.6-9.4-33.9 0l-80 80z"></path>
        </svg>
        <p>Drag and Drop</p>
        <p>or</p>
        <span class="browse-button">Browse file</span>
      </div>
      <input id="file" type="file" name="file"/>
    </label>
  </form>
  
  
  
  <form class="file-upload-form" id="idfield">
  <label for="file" class="file-upload-field">
    <div class="file-upload-design">
      <svg  viewBox="0 0 24 24" height="1em" >
<path fill-rule="evenodd" clip-rule="evenodd" d="M3.46447 20.5355C4.92893 22 7.28595 22 12 22C16.714 22 19.0711 22 20.5355 20.5355C22 19.0711 22 16.714 22 12C22 7.28595 22 4.92893 20.5355 3.46447C19.0711 2 16.714 2 12 2C7.28595 2 4.92893 2 3.46447 3.46447C2 4.92893 2 7.28595 2 12C2 16.714 2 19.0711 3.46447 20.5355ZM9.5 8.75C7.70507 8.75 6.25 10.2051 6.25 12C6.25 13.7949 7.70507 15.25 9.5 15.25C11.2949 15.25 12.75 13.7949 12.75 12C12.75 11.5858 13.0858 11.25 13.5 11.25C13.9142 11.25 14.25 11.5858 14.25 12C14.25 14.6234 12.1234 16.75 9.5 16.75C6.87665 16.75 4.75 14.6234 4.75 12C4.75 9.37665 6.87665 7.25 9.5 7.25C9.91421 7.25 10.25 7.58579 10.25 8C10.25 8.41421 9.91421 8.75 9.5 8.75ZM17.75 12C17.75 13.7949 16.2949 15.25 14.5 15.25C14.0858 15.25 13.75 15.5858 13.75 16C13.75 16.4142 14.0858 16.75 14.5 16.75C17.1234 16.75 19.25 14.6234 19.25 12C19.25 9.37665 17.1234 7.25 14.5 7.25C11.8766 7.25 9.75 9.37665 9.75 12C9.75 12.4142 10.0858 12.75 10.5 12.75C10.9142 12.75 11.25 12.4142 11.25 12C11.25 10.2051 12.7051 8.75 14.5 8.75C16.2949 8.75 17.75 10.2051 17.75 12Z"/>
</svg>
      <p>Enter the 12-digit ID.</p>
      <input type="text" style="width:80%; background-color:#666; margin-top: 12px; font-size:12px; border-radius:10px; color:#eee; transition:all 0.3s; border:none; padding:5px; text-align:center;" placeholder="12-digit ID" />
 
    </div>
  </label>
</form>
  
  
  
 </div>
 <div id="loaderbar" class="loaderbar">
  <!-- Loader for file checking -->
  <div id="checkingLoader" class="lds-ellipsis" style="display:none;">
    <div></div><div></div><div></div><div></div>
  </div>

  <!-- Progress loader for upload -->
  <div id="uploadLoader" class="upload-loader" style="display:none;">
    <div class="progress-container">
      <div id="progressBar" class="progress" data-percentage="100%"></div>
    </div>
 </div>
 </div>
 
  <!-- Table for file details -->
  <table id="fileTable" border="1" cellspacing="0" cellpadding="5" style="margin-top:20px;">
    <thead>
      <tr>
        <th>Filename</th>
        <th>File Type</th>
        <th>Upload Time</th>
        <th>Modify Time</th>
        <th>Security ID</th>
        <th>Download</th>
      </tr>
    </thead>
    <tbody>
      <!-- File rows will appear here -->
    </tbody>
  </table>

  <!-- Download Modal -->
  <div id="downloadModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <div id="downloadLinkContainer" style="background: #ccc; padding: 10px; margin-bottom: 15px; word-break: break-all;"></div>
      <div style="text-align: center;">
         <button id="copyLinkButton">Copy Link</button>
         <button id="downloadButton">Download</button>
      </div>
    </div>
  </div>
<script src="<?php echo plugins_url( 'lib/filemanager-script.js', __FILE__ ); ?>"></script>
  <script>
    const fileInput = document.getElementById('file');
    const checkingLoader = document.getElementById('checkingLoader');
    const uploadLoader = document.getElementById('uploadLoader');
    const progressBar = document.getElementById('progressBar');
    const fileTableBody = document.querySelector('#fileTable tbody');
    const downloadModal = document.getElementById('downloadModal');
    const downloadLinkContainer = document.getElementById('downloadLinkContainer');
    const copyLinkButton = document.getElementById('copyLinkButton');
    const downloadButton = document.getElementById('downloadButton');
    const modalClose = document.querySelector('.close');

    // Current download link placeholder
    let currentDownloadLink = '';

    fileInput.addEventListener('change', () => {
      if (fileInput.files.length === 0) return;
      
      // Show file checking loader
      checkingLoader.style.display = 'block';

      // Simulate file checking delay (e.g., validation)
      setTimeout(() => {
        checkingLoader.style.display = 'none';
        // Begin upload: show progress loader
        uploadLoader.style.display = 'block';
        simulateUpload(fileInput.files[0]);
      }, 1000); // 1 second delay for checking
    });

    function simulateUpload(file) {
      const formData = new FormData();
      formData.append('file', file);
      
      // Create an AJAX request for uploading file
      const xhr = new XMLHttpRequest();
      xhr.open('POST', '', true);

      // Update progress simulation
      xhr.upload.onprogress = function(event) {
        if (event.lengthComputable) {
          const percentComplete = Math.round((event.loaded / event.total) * 100);
          progressBar.style.width = percentComplete + '%';
        }
      };

      xhr.onload = function() {
        if (xhr.status === 200) {
          const response = JSON.parse(xhr.responseText);
          if (response.status === "success") {
            // Reset progress bar
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
      // Compute download link (assuming file is served from uploads directory)
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
      // Reset file input
      document.getElementById('uploadForm').reset();
    }

    // Event delegation for download buttons in the table
    fileTableBody.addEventListener('click', function(event) {
      if (event.target.classList.contains('download-btn')) {
        const link = event.target.getAttribute('data-link');
        showDownloadModal(link);
      }
    });

    function showDownloadModal(link) {
      currentDownloadLink = link;
      downloadLinkContainer.innerText = link;
      downloadModal.style.display = 'block';
    }

    // Copy link functionality
    copyLinkButton.addEventListener('click', () => {
      navigator.clipboard.writeText(currentDownloadLink).then(() => {
        alert('Link copied to clipboard!');
      }).catch(err => {
        alert('Failed to copy text: ' + err);
      });
    });

    // Download button functionality
    downloadButton.addEventListener('click', () => {
      window.location.href = currentDownloadLink;
    });

    // When the user clicks on <span> (x), close the modal
    modalClose.addEventListener('click', () => {
      downloadModal.style.display = 'none';
    });

    // Close modal if user clicks outside modal-content
    window.addEventListener('click', event => {
      if (event.target == downloadModal) {
        downloadModal.style.display = 'none';
      }
    });
  </script>
</body>
</html>
