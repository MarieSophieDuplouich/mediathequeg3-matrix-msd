<?php
$labelType = function ($type_id) {
  switch ((int)$type_id) {
    case 1: return 'Livre';
    case 2: return 'Film';
    case 3: return 'Jeu';
    default: return 'Inconnu';
  }
};
?>

<div class="profile-container">

  <h1 class="profile-title">Mon profil</h1>

  <?php if (has_flash_messages()): ?>
    <?php foreach (get_flash_messages() as $type => $msgs): ?>
      <?php foreach ($msgs as $msg): ?>
        <p class="profile-flash"><?= e($msg) ?></p>
      <?php endforeach; ?>
    <?php endforeach; ?>
  <?php endif; ?>

  <h2 class="profile-section-title">Mes emprunts en cours</h2>

  <?php if (empty($active_loans)): ?>
    <p class="profile-empty">Aucun emprunt en cours.</p>
  <?php else: ?>
    <ul class="profile-loan-list">
      <?php foreach ($active_loans as $l): ?>
        <li class="profile-loan-card">
          <img class="profile-loan-cover"
               src="<?= !empty($l['cover']) ? url('uploads/covers/' . $l['cover']) : url('assets/placeholder.jpg') ?>"
               alt="cover">

          <div class="profile-loan-info">
            <p class="profile-loan-title">
              <?= e($l['title']) ?>
              <span class="profile-loan-type">(<?= e($labelType($l['type_id'] ?? 0)) ?>)</span>
            </p>
            <p class="profile-loan-dates">
              Emprunté le : <?= e(date('d/m/Y', strtotime($l['loan_date']))) ?> —
              Retour attendu : <?= e(date('d/m/Y', strtotime($l['expected_return_date']))) ?>
            </p>

            <div class="profile-loan-actions">
              <form method="post" action="<?= url('home/return/'.$l['loan_id']) ?>" onsubmit="return confirm('Confirmer le retour ?');">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <button class="rendre-btn">Rendre</button>
              </form>
              <a class="media-link" href="<?= url('media/detail/'.$l['media_id']) ?>">Voir la fiche média</a>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <h2 class="profile-section-title">Historique</h2>

  <?php if (empty($history)): ?>
    <p class="profile-empty">Pas d'historique pour le moment.</p>
  <?php else: ?>
    <ul class="profile-loan-list">
      <?php foreach ($history as $h): ?>
        <li class="profile-loan-card profile-loan-card--history">
          <img class="profile-loan-cover"
               src="<?= url($h['cover'] ?: 'uploads/covers/default.jpg') ?>"
               alt="cover">

          <div class="profile-loan-info">
            <p class="profile-loan-title">
              <?= e($h['title']) ?>
              <span class="profile-loan-type">(<?= e($labelType($h['type_id'] ?? 0)) ?>)</span>
            </p>
            <p class="profile-loan-dates">
              Emprunté le : <?= e(date('d/m/Y', strtotime($h['loan_date']))) ?> —
              Attendu le : <?= e(date('d/m/Y', strtotime($h['expected_return_date']))) ?> —
              Rendu le : <?= e(date('d/m/Y', strtotime($h['actual_return_date']))) ?>
            </p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

</div>