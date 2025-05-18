// Check if wpPluploadSettings is defined; if not, log error and exit.
if (typeof wpPluploadSettings === 'undefined') {
  console.error("wpPluploadSettings is not defined. Please ensure that the Plupload scripts (e.g., 'plupload-all' and 'plupload-handlers') are properly enqueued.");
  return;
}

// Create a container element for Plupload progress if it doesn't exist.
if ($('#upload-progress').length === 0) {
  $('body').append('<div id="upload-progress" style="position:fixed;top:10px;right:10px;z-index:10000;background:#fff;padding:10px;border:1px solid #ccc;"></div>');
}

// Merge our custom settings with WordPress's default Plupload settings.
var settings = $.extend(true, {}, wpPluploadSettings, {
  browse_button: 'fm-file-input', // ID of the hidden file input element.
  // 'container' can be defined if you have a specific container element. It can be omitted if not needed.
  container: 'upload-container',   // Optional; ensure you have an element with this ID if you require it.
  // Updating URL to use our custom AJAX action for file uploads.
  url: ajaxurl + '?action=fm_upload_file',
  multipart_params: {
    _wpnonce: fm_filemanager.nonce
  }
});

// Initialize the uploader.
var uploader = new plupload.Uploader(settings);

uploader.bind('Init', function(up, info) {
  console.log("Plupload initialized with: " + info.runtime);
});

uploader.bind('FilesAdded', function(up, files) {
  // Create progress UI elements for each file.
  $.each(files, function(i, file) {
    $('#upload-progress').append(
      '<div id="' + file.id + '" style="margin-bottom:5px;">' +
        '<strong>' + file.name + '</strong> (' + plupload.formatSize(file.size) + ') ' +
        '<span class="progress">0%</span>' +
      '</div>'
    );
  });
  // Automatically start the upload.
  up.start();
});

uploader.bind('UploadProgress', function(up, file) {
  // Update the progress for the file.
  $('#' + file.id + ' .progress').text(file.percent + "%");
});

uploader.bind('FileUploaded', function(up, file, response) {
  console.log('File uploaded: ' + file.name);
  // Optionally, refresh your file list (e.g., trigger an AJAX reload of the "My Files" tab).
});

uploader.bind('Error', function(up, err) {
  console.error("Error: " + err.message);
  $('#upload-progress').append('<div style="color:red;">Error: ' + err.message + '</div>');
});

uploader.init();