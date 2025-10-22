<?php
include 'notiffunction.php';
$conn = new mysqli("localhost", "root", "", "taho");
if ($conn->connect_error) die("DB error");

$res = $conn->query("SELECT * FROM notifications ORDER BY date DESC");
$notifications = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$unread_count = $conn->query("SELECT COUNT(*) AS cnt FROM notifications WHERE readstatus='unread'")->fetch_assoc()['cnt'];
?>

<div class="modal fade" id="notificationModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-bell me-2"></i>Notifications</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if (empty($notifications)): ?>
          <p class="text-center text-muted">No notifications yet.</p>
        <?php else: ?>
          <ul class="list-group">
            <?php foreach ($notifications as $n): ?>
              <li class="list-group-item d-flex justify-content-between align-items-start <?= $n['readstatus'] == 'unread' ? 'bg-light' : '' ?>" data-id="<?= $n['notificationid'] ?>">
                <div>
                  <strong><?= htmlspecialchars($n['username']) ?></strong> <?= htmlspecialchars($n['action']) ?><br>
                  <small class="text-muted"><?= date('M d, Y H:i', strtotime($n['date'])) ?></small>
                </div>
                <?php if ($n['readstatus'] == 'unread'): ?>
                  <span class="badge bg-danger">New</span>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<script>
$(document).on('click', '.list-group-item', function(){
  let id = $(this).data('id');
  let item = $(this);
  $.post('notifications.php', {notificationid: id}, function(res){
    if(res.success){
      item.removeClass('bg-light');
      item.find('.badge').remove();
    }
  }, 'json');
});
</script>