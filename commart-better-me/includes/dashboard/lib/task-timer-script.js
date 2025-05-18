jQuery(document).ready(function($) {
  $('.toggle-task').on('change', function() {
    var taskId = $(this).data('id');
    if ($(this).is(':checked')) {
      $.post(ajaxurl, {
        action: 'commart_start_task',
        task_id: taskId
      }, function(response) {
        if (!response.success) alert(response.data);
      });
    } else {
      $.post(ajaxurl, {
        action: 'commart_pause_task',
        task_id: taskId
      }, function(response) {
        if (!response.success) alert(response.data);
      });
    }
  });

  $('.stop-task').on('click', function() {
    var taskId = $(this).data('id');
    $.post(ajaxurl, {
      action: 'commart_stop_task',
      task_id: taskId,
      report: prompt('Enter report (optional):', '')
    }, function(response) {
      if (!response.success) alert(response.data);
    });
  });
});