<?php
$m = $item; // alias data
$type = $m['type_id'] ?? null; // type id
// genre by type
$genre = ($type == 1) ? ($m['genre_books'] ?? '') : (($type == 2) ? ($m['genre_movies'] ?? '') : (($type == 3) ? ($m['genre_games'] ?? '') : ''));
// cover url (fallback placeholder)
$cover = !empty($m['cover']) ? url('uploads/covers/' . $m['cover']) : url('assets/placeholder.jpg');
?>

<div class="media-detail">
  <div class="media-header">
    <h1><?= e($m['title']) ?></h1>
    <span class="badge">Disponibilité : <?= (int)$m['stock'] ?></span> <!-- dispo -->
  </div>

  <div class="media-card1">
    <img src="<?= $cover ?>" alt="Couverture de <?= e($m['title']) ?>"class="img-detail"> <!-- cover -->

    <div>
      <p class="type">
        Type :
        <?php
          // label type
          if ($type == 1) e("Livre");
          elseif ($type == 2) e("Film");
          elseif ($type == 3) e ("Jeu vidéo");
          else e("Inconnu");
        ?>
        <?php if ($genre): ?> Genre : <?= e($genre) ?><?php endif; ?> <!-- genre -->
        <?php if (!empty($m['year'])): ?> Année : <?= (int)$m['year'] ?><?php endif; ?> <!-- year -->
      </p>

      <?php if ($type == 1): // livre ?>
        <p class="auteur">
          Auteur : <?= e($m['author']) ?>
          <?php if (!empty($m['isbn'])): ?> ISBN : <?= e($m['isbn']) ?><?php endif; ?>
          <?php if (!empty($m['pages'])): ?> Pages : <?= (int)$m['pages'] ?><?php endif; ?>
        </p>
      <?php elseif ($type == 2): // film ?>
        <p>
          Réalisateur : <?= e($m['director']) ?>
          <?php if (!empty($m['duration'])): ?> Durée : <?= (int)$m['duration'] ?> min<?php endif; ?>
          <?php if (!empty($m['classification'])): ?> Classification : <?= e($m['classification']) ?><?php endif; ?>
        </p>
      <?php elseif ($type == 3): // jeu ?>
        <p>
          Éditeur : <?= e($m['publisher']) ?>
          <?php if (!empty($m['plateform'])): ?> Plateforme : <?= e($m['plateform']) ?><?php endif; ?>
          <?php if (!empty($m['min_age'])): ?> Âge min : <?= e($m['min_age']) ?><?php endif; ?>
        </p>
      <?php endif; ?>

     <?php if (!empty($m['description'])): ?>
        <p class="media-description"> Résumé : <?= e($m['description']) ?></p>
      <?php endif; ?>
    </div>
  </div>

  <?php if (is_logged_in()): // actions login only ?>
  <div class="media-actions">
    <?php if (!empty($active_loan)): // rendre ?>
      <form method="post" action="<?= url('media/return/'.$item['id']) ?>" onsubmit="return confirm('Confirmer le retour ?');">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"> <!-- csrf -->
        <button>Rendre</button>
      </form>
    <?php elseif ((int)$item['stock'] > 0): // emprunter ?>
      <form method="post" action="<?= url('media/loan/'.$item['id']) ?>" onsubmit="return confirm('Confirmer l\'emprunt ?');">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"> <!-- csrf -->
        <button>Emprunter</button>
      </form>
    <?php else: // out of stock ?>
      <span>Ce média n'est pas disponible.</span>
    <?php endif; ?>
  </div>
<?php else: ?>
  <p><em>Connecte-toi pour emprunter ce média.</em></p> <!-- invite login -->
<?php endif; ?>
