<?php
$labelType = function ($type_id) {
  switch ((int)$type_id) { // map type_id -> label
    case 1: return 'Livre'; // book
    case 2: return 'Film'; // movie
    case 3: return 'Jeu'; // game
    default: return 'Inconnu'; // fallback
  }
};
?>

<h1>Mon profil</h1>

<?php if (has_flash_messages()): // flash zone ?>
  <?php foreach (get_flash_messages() as $type => $msgs): ?>
    <?php foreach ($msgs as $msg): ?>
      <p><?= e($msg) ?></p> <!-- safe output -->
    <?php endforeach; ?>
  <?php endforeach; ?>
<?php endif; ?>

<h2>Mes emprunts en cours</h2>
<?php if (empty($active_loans)): ?>
  <p>Aucun emprunt en cours.</p> <!-- rien -->
<?php else: ?>
  <ul>
    <?php foreach ($active_loans as $l): ?>
      <li>
        <div>
          <!-- cover (fallback default) -->
          <img src="<?= url($l['cover'] ?: 'uploads/covers/default.jpg') ?>" alt="cover">
          <!-- titre + type -->
          <?= e($l['title']) ?>
          <span>(<?= e($labelType($l['type_id'] ?? 0)) ?>)</span><br>
          Emprunté le : <?= e(date('d/m/Y', strtotime($l['loan_date']))) ?> — 
          Retour attendu : <?= e(date('d/m/Y', strtotime($l['expected_return_date']))) ?>
        </div>

        <!-- action: rendre depuis profil -->
        <form method="post" action="<?= url('home/return/'.$l['loan_id']) ?>" onsubmit="return confirm('Confirmer le retour ?');">
          <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"> <!-- csrf ok -->
          <button>Rendre</button>
        </form>

        <!-- lien fiche media -->
        <p><a class="media-link" href="<?= url('media/detail/'.$l['media_id']) ?>">Voir la fiche média</a></p>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<h2>Historique</h2>
<?php if (empty($history)): ?>
  <p>Pas d’historique pour le moment.</p> <!-- rien -->
<?php else: ?>
  <ul>
    <?php foreach ($history as $h): ?>
      <li>
        <div>
          <!-- cover small -->
          <img src="<?= url($h['cover'] ?: 'uploads/covers/default.jpg') ?>" alt="cover">
          <!-- titre + type -->
          <?= e($h['title']) ?>
          <span>(<?= e($labelType($h['type_id'] ?? 0)) ?>)</span><br>
          <!-- dates loan / due / returned -->
          Emprunté le : <?= e(date('d/m/Y', strtotime($h['loan_date']))) ?> — 
          Attendu le : <?= e(date('d/m/Y', strtotime($h['expected_return_date']))) ?> — 
          Rendu le : <?= e(date('d/m/Y', strtotime($h['actual_return_date']))) ?>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>
