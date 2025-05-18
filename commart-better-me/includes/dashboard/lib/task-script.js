jQuery(document).ready(function($) {
  // Use an object to hold intervals for each task separately.
  const taskIntervals = {};

  // Start task timer: send AJAX request using global ajaxurl.
  function startTaskTimer(taskId) {
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_start_task',
        task_id: taskId
      },
      success: function(response) {
        if (response.success) {
          // Start dynamic timer update.
          startDynamicTaskTimer(taskId);
        } else {
          alert(response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.error("AJAX error:", textStatus, errorThrown);
         alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
      }
    });
  }

  // Pause task timer: send AJAX request using global ajaxurl.
  function pauseTaskTimer(taskId) {
    // Clear the interval specific to this task.
    if(taskIntervals[taskId]) {
      clearInterval(taskIntervals[taskId]);
      delete taskIntervals[taskId];
    }
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_pause_task',
        task_id: taskId
      },
      success: function(response) {
        if (response.success) {
          $('#betterme-task-timer-' + taskId)
            .text(formatTime(response.data.elapsed))
            .attr('data-elapsed', response.data.elapsed);
        } else {
          alert(response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.error("AJAX error:", textStatus, errorThrown);
         alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
      }
    });
  }

  // Stop task timer: send AJAX request using global ajaxurl.
  function stopTaskTimer(taskId, report) {
    // Clear the interval specific to this task.
    if(taskIntervals[taskId]) {
      clearInterval(taskIntervals[taskId]);
      delete taskIntervals[taskId];
    }
    $.ajax({
      url: ajaxurl,
      method: 'POST',
      data: {
        action: 'commart_stop_task',
        task_id: taskId,
        report: report
      },
      success: function(response) {
        if (response.success) {
          $('#betterme-task-timer-' + taskId)
            .text(response.data.elapsed_formatted)
            .attr('data-elapsed', response.data.elapsed);
        } else {
          alert(response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.error("AJAX error:", textStatus, errorThrown);
         alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
      }
    });
  }

  // Dynamically update task timer similar to steps.
  function startDynamicTaskTimer(taskId) {
    let $timerElem = $('#betterme-task-timer-' + taskId);
    let elapsed = parseInt($timerElem.attr('data-elapsed')) || 0;
    
    // Clear any previous interval for this task.
    if(taskIntervals[taskId]) {
      clearInterval(taskIntervals[taskId]);
    }
    
    taskIntervals[taskId] = setInterval(function() {
      elapsed++;
      $timerElem.attr('data-elapsed', elapsed);
      $timerElem.text(formatTime(elapsed));
    }, 1000);
  }

  // Format seconds to hh:mm:ss.
  function formatTime(seconds) {
    let hrs = Math.floor(seconds / 3600);
    let mins = Math.floor((seconds % 3600) / 60);
    let secs = seconds % 60;
    return ("0" + hrs).slice(-2) + ":" + ("0" + mins).slice(-2) + ":" + ("0" + secs).slice(-2);
  }

  // Event listeners for task timer control buttons.
  $('.betterme-task-start-timer').on('click', function() {
    let taskId = $(this).data('id');
    startTaskTimer(taskId);
  });

  $('.betterme-task-pause-timer').on('click', function() {
    let taskId = $(this).data('id');
    pauseTaskTimer(taskId);
  });

  $('.betterme-task-stop-timer').on('click', function() {
    let taskId = $(this).data('id');
    let report = prompt("لطفاً گزارش (اختیاری) را وارد کنید:", "");
    stopTaskTimer(taskId, report);
  });

  // Handle form submission for adding/updating task.
  $('#betterme-task-entry-form').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: ajaxurl,
      type: 'POST',
      dataType: 'json',
      data: {
        action: 'commart_add_update_task',
        form_data: formData
      },
      success: function(response) {
        if (response.success) {
          var task = response.data;
          if ($('#betterme-task-row-' + task.id).length) {
            var row = $('#betterme-task-row-' + task.id);
            row.find('td:eq(0)').text(task.project_title);
            row.find('td:eq(1)').text(task.tasks_title);
            row.find('td:eq(2)').text(task.tasks_deadline);
            row.find('td:eq(3)')
              .text(task.elapsed_formatted)
              .attr('data-elapsed', task.elapsed);
          } else {
            var newRow = "<tr id='betterme-task-row-" + task.id + "'>" +
              "<td>" + task.project_title + "</td>" +
              "<td>" + task.tasks_title + "</td>" +
              "<td>" + task.tasks_deadline + "</td>" +
              "<td class='betterme-task-timer' data-elapsed='" + task.elapsed + "' id='betterme-task-timer-" + task.id + "'>" + task.elapsed_formatted + "</td>" +
              "<td>" +
                "<label class='betterme-task-container'>" +
                  "<input type='checkbox' class='betterme-task-toggle' data-id='" + task.id + "'>" +
                  "<svg viewBox='0 0 384 512' height='1em' xmlns='http://www.w3.org/2000/svg' class='betterme-task-play'><path d='M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z'></path></svg>" +
                  "<svg viewBox='0 0 320 512' height='1em' xmlns='http://www.w3.org/2000/svg' class='betterme-task-pause'><path d='M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z'></path></svg>" +
                "</label>" +
              "</td>" +
              "<td>" +
                "<button class='betterme-task-stop' data-id='" + task.id + "'>تکمیل تسک</button>" +
              "</td>" +
              "<td>" +
                "<button class='betterme-task-report' data-id='" + task.id + "' data-report=''>گزارش</button>" +
              "</td>" +
              "<td>" +
                "<button class='betterme-task-edit' data-id='" + task.id + "' data-projects_id='" + task.projects_id + "' data-tasks_title='" + task.tasks_title + "' data-tasks_deadline='" + task.tasks_deadline + "'>ویرایش</button>" +
              "</td>" +
              "<td>" +
                "<button class='betterme-task-delete' data-id='" + task.id + "'>حذف</button>" +
              "</td>" +
            "</tr>";
            $('#betterme-task-table tbody').prepend(newRow);
          }
          $('#betterme-task-entry-form')[0].reset();
          $('#betterme-task_id').val('');
        } else {
          alert('خطا: ' + response.data);
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
         console.error("AJAX error:", textStatus, errorThrown);
         alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
      }
    });
  });

  // Handle task editing: populate form with existing task data.
  $(document).on('click', '.betterme-task-edit', function() {
    var id = $(this).data('id');
    $('#betterme-task_id').val(id);
    $('#betterme-task_projects_id').val($(this).data('projects_id'));
    $('#betterme-task_title').val($(this).data('tasks_title'));
    $('#betterme-task_deadline').val($(this).data('tasks_deadline'));
    $('html, body').animate({scrollTop: $("#betterme-task-form").offset().top}, 500);
  });

  // Handle task deletion.
  $(document).on('click', '.betterme-task-delete', function() {
    if (confirm("آیا از حذف این تسک اطمینان دارید؟")) {
      var id = $(this).data('id');
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_delete_task',
          task_id: id
        },
        success: function(response) {
          if(response.success) {
            $('#betterme-task-row-' + id).remove();
          } else {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Handle toggle for starting/pausing task timer.
  $(document).on('change', '.betterme-task-toggle', function() {
    var taskId = $(this).data('id');
    if ($(this).is(':checked')) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_start_task',
          task_id: taskId
        },
        success: function(response) {
          if (!response.success) {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    } else {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_pause_task',
          task_id: taskId
        },
        success: function(response) {
          if (!response.success) {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Handle report modal: show when "تکمیل تسک" button is clicked.
  var currentStopTaskId = null;
  $(document).on('click', '.betterme-task-stop', function() {
    currentStopTaskId = $(this).data('id');
    $('#task-report').val('');
    $('#task-report-modal').show();
  });

  // Cancel report modal.
  $('#betterme-task-cancel-report').on('click', function() {
    $('#task-report-modal').hide();
    currentStopTaskId = null;
  });

  // Submit task report and stop the timer.
  $('#betterme-task-submit-report').on('click', function() {
    var reportText = $('#task-report').val();
    if (currentStopTaskId) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_stop_task',
          task_id: currentStopTaskId,
          report: reportText
        },
        success: function(response) {
          if (response.success) {
            $('#betterme-task-row-' + currentStopTaskId).find('.betterme-task-timer')
              .text(response.data.elapsed_formatted)
              .attr('data-elapsed', response.data.elapsed);
            $('#betterme-task-row-' + currentStopTaskId).find('td:eq(6)').html(
              '<button class="betterme-task-report" data-id="'+ currentStopTaskId +'" data-report="'+ reportText +'">گزارش</button>'
            );
            $('#task-report-modal').hide();
            currentStopTaskId = null;
          } else {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Handle report viewing/editing.
  var currentTaskReportId = null;
  $(document).on('click', '.betterme-task-report', function() {
    currentTaskReportId = $(this).data('id');
    var currentReport = $(this).data('report') || '';
    $('#task-report-view').val(currentReport);
    $('#betterme-task-view-report-modal').show();
  });

  $('#betterme-task-update-report').on('click', function() {
    var updatedReport = $('#task-report-view').val();
    if (currentTaskReportId) {
      $.ajax({
        url: ajaxurl,
        type: 'POST',
        dataType: 'json',
        data: {
          action: 'commart_update_task_report',
          task_id: currentTaskReportId,
          report: updatedReport
        },
        success: function(response) {
          if (response.success) {
            $('button.betterme-task-report[data-id="'+ currentTaskReportId +'"]').data('report', updatedReport);
            $('#betterme-task-view-report-modal').hide();
            currentTaskReportId = null;
          } else {
            alert('خطا: ' + response.data);
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.error("AJAX error:", textStatus, errorThrown);
          alert('خطای AJAX رخ داده است. لطفاً کنسول را بررسی کنید.');
        }
      });
    }
  });

  // Close report modal.
  $('#betterme-task-close-report').on('click', function() {
    $('#betterme-task-view-report-modal').hide();
    currentTaskReportId = null;
  });
});