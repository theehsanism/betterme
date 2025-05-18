<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Include AJAX task handlers.
require_once plugin_dir_path( __FILE__ ) . 'lib/ajax-task.php';

global $wpdb;
$current_user   = wp_get_current_user();
$tasks_table    = $wpdb->prefix . 'commart_better_me_tasks';
$projects_table = $wpdb->prefix . 'commart_better_me_projects';

// Fetch the tasks for the current user along with the project title.
$query = "SELECT t.*, p.projects_title as project_title 
          FROM $tasks_table t 
          LEFT JOIN $projects_table p ON t.projects_id = p.id 
          WHERE t.user_id = %d 
          ORDER BY t.tasks_created_at DESC";
$tasks = $wpdb->get_results( $wpdb->prepare( $query, $current_user->ID ) );

// Fetch projects for the select options in the task form.
$projects = $wpdb->get_results( 
    $wpdb->prepare( 
        "SELECT id, projects_title FROM $projects_table ORDER BY projects_title ASC", 
        $current_user->ID 
    ) 
);


/**
 * Helper function to format elapsed time as hh:mm:ss.
 */
function task_format_elapsed_time( $seconds ) {
    $h = floor( $seconds / 3600 );
    $m = floor( ($seconds % 3600) / 60 );
    $s = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}
?>
<div id="betterme-task-list">
  <h2>لیست تسک‌ها</h2>
  <table id="betterme-task-table" border="1" cellpadding="5" cellspacing="0">
    <thead>
      <tr>
        <th>پروژه</th>
        <th>عنوان تسک</th>
        <th>مهلت</th>
        <th>تایمر</th>
        <th>شروع/توقف</th>
        <th>تکمیل</th>
        <th>گزارش</th>
        <th>ویرایش</th>
        <th>حذف</th>
      </tr>
    </thead>
    <tbody>
      <?php if($tasks): ?>
        <?php foreach($tasks as $task): 
          // Calculate elapsed time; if timer is active, add running time to stored tasks_elapsed_time.
          if( !empty($task->tasks_timer_start) ){
              $start_time = strtotime( $task->tasks_timer_start );
              $now = current_time('timestamp');
              $elapsed = intval($task->tasks_elapsed_time) + ($now - $start_time);
          } else {
              $elapsed = intval($task->tasks_elapsed_time);
          }
          $formatted = task_format_elapsed_time( $elapsed );
        ?>
          <tr id="betterme-task-row-<?php echo esc_attr( $task->id ); ?>">
            <td><?php echo esc_html( $task->project_title ); ?></td>
            <td><?php echo esc_html( $task->tasks_title ); ?></td>
            <td><?php echo esc_html( $task->tasks_deadline ); ?></td>
            <td class="betterme-task-timer" data-elapsed="<?php echo esc_attr( $elapsed ); ?>" id="betterme-task-timer-<?php echo esc_attr( $task->id ); ?>">
              <?php echo esc_html( $formatted ); ?>
            </td>
            <td>
              <label class="betterme-task-container">
                <input type="checkbox" class="betterme-task-toggle" data-id="<?php echo esc_attr( $task->id ); ?>"
                  <?php echo ( !empty($task->tasks_timer_start) ? 'checked' : ''); ?>>
                <svg viewBox="0 0 384 512" height="1em" xmlns="http://www.w3.org/2000/svg" class="betterme-task-play">
                  <path d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80V432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"></path>
                </svg>
                <svg viewBox="0 0 320 512" height="1em" xmlns="http://www.w3.org/2000/svg" class="betterme-task-pause">
                  <path d="M48 64C21.5 64 0 85.5 0 112V400c0 26.5 21.5 48 48 48H80c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H48zm192 0c-26.5 0-48 21.5-48 48V400c0 26.5 21.5 48 48 48h32c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48H240z"></path>
                </svg>
              </label>
            </td>
            <td>
              <button class="betterme-task-stop" data-id="<?php echo esc_attr( $task->id ); ?>">تکمیل تسک</button>
            </td>
            <td>
              <button class="betterme-task-report" data-id="<?php echo esc_attr( $task->id ); ?>" data-report="<?php echo esc_attr( $task->tasks_report ); ?>">گزارش</button>
            </td>
            <td>
              <button class="betterme-task-edit"
                data-id="<?php echo esc_attr( $task->id ); ?>"
                data-projects_id="<?php echo esc_attr( $task->projects_id ); ?>"
                data-tasks_title="<?php echo esc_attr( $task->tasks_title ); ?>"
                data-tasks_deadline="<?php echo esc_attr( $task->tasks_deadline ); ?>">
                ویرایش
              </button>
            </td>
            <td>
              <button class="betterme-task-delete" data-id="<?php echo esc_attr( $task->id ); ?>">حذف</button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
          <tr>
            <td colspan="9">هیچ تسکی یافت نشد.</td>
          </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div id="betterme-task-form">
  <h2>افزودن / ویرایش تسک</h2>
  <form id="betterme-task-entry-form">
    <input type="hidden" name="betterme-task_id" id="betterme-task_id" value="">
    <p>
      <label for="betterme-task_projects_id">پروژه:</label>
      <select name="betterme-task_projects_id" id="betterme-task_projects_id" required>
        <option value="">انتخاب پروژه</option>
        <?php foreach($projects as $project): ?>
          <option value="<?php echo esc_attr( $project->id ); ?>">
            <?php echo esc_html( $project->projects_title ); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="betterme-task_title">عنوان تسک:</label>
      <input type="text" name="betterme-task_title" id="betterme-task_title" required>
    </p>
    <p>
      <label for="betterme-task_deadline">مهلت:</label>
      <input type="date" name="betterme-task_deadline" id="betterme-task_deadline" required>
    </p>
    <p>
      <button type="submit" id="betterme-task-submit">ثبت</button>
      <button type="reset" id="betterme-task-reset-form">بازنشانی</button>
    </p>
  </form>
</div>

<!-- Modal for entering report upon completing a task -->
<div id="betterme-task-report-modal" style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
    background:#fff; padding:20px; border:1px solid #000; z-index:9999;">
  <h3>وارد کردن گزارش</h3>
  <textarea id="betterme-task-report" rows="5" cols="40"></textarea>
  <br>
  <button id="betterme-task-submit-report">ثبت گزارش</button>
  <button id="betterme-task-cancel-report">لغو</button>
</div>

<!-- Modal for viewing/editing a task's report -->
<div id="betterme-task-view-report-modal" style="display:none; position:fixed; top:50%; left:50%; transform: translate(-50%, -50%);
    background:#fff; padding:20px; border:1px solid #000; z-index:9999;">
  <h3>گزارش تسک</h3>
  <textarea id="betterme-task-report-view" rows="5" cols="40"></textarea>
  <br>
  <button id="betterme-task-update-report">به‌روزرسانی گزارش</button>
  <button id="betterme-task-close-report">بستن</button>
</div>

<style>
.betterme-task-container {
  --color: #a5a5b0;
  --size: 30px;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  cursor: pointer;
  font-size: var(--size);
  user-select: none;
  fill: var(--color);
}
.betterme-task-container .betterme-task-play {
  position: absolute;
  animation: keyframes-fill .5s;
}
.betterme-task-container .betterme-task-pause {
  position: absolute;
  display: none;
  animation: keyframes-fill .5s;
}
.betterme-task-container input:checked ~ .betterme-task-play {
  display: none;
}
.betterme-task-container input:checked ~ .betterme-task-pause {
  display: block;
}
.betterme-task-container input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}
@keyframes keyframes-fill {
  0% { transform: rotate(-180deg) scale(0); opacity: 0; }
  50% { transform: rotate(-10deg) scale(1.2); }
}
</style>

<?php
  // IMPORTANT: Define the AJAX address for our scripts.
?>
<script type="text/javascript">
  // در صورتی که متغیر ajaxurl تعریف نشده باشد، آن را تعریف می‌کنیم.
  if (typeof ajaxurl === 'undefined') {
      var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
  }
</script>
<script src="<?php echo plugin_dir_url(__FILE__); ?>lib/task-script.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($){
  // Update live timers every second for tasks
  setInterval(function(){
    $('.betterme-task-timer').each(function(){
      var $cell = $(this);
      var elapsed = parseInt($cell.attr('data-elapsed')) || 0;
      var taskId = $cell.closest('tr').attr('id').replace('betterme-task-row-', '');
      var $toggle = $('input.betterme-task-toggle[data-id="'+taskId+'"]');
      if($toggle.length && $toggle.is(':checked')){
        elapsed++;
        $cell.attr('data-elapsed', elapsed);
        var hrs = Math.floor(elapsed / 3600);
        var mins = Math.floor((elapsed % 3600) / 60);
        var secs = elapsed % 60;
        var formatted = ("0" + hrs).slice(-2) + ":" + ("0" + mins).slice(-2) + ":" + ("0" + secs).slice(-2);
        $cell.text(formatted);
      }
    });
  }, 1000);
});
</script>