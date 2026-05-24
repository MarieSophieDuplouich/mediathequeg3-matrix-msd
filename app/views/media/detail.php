<?php
$m = $item ?? []; // alias data + protection si item null
$type = $m['type_id'] ?? null; // type id
// genre by type
$genre = ($type == 1) ? ($m['genre_books'] ?? '') : (($type == 2) ? ($m['genre_movies'] ?? '') : (($type == 3) ? ($m['genre_games'] ?? '') : ''));
// cover url (fallback placeholder)
$cover = !empty($m['cover']) ? url('uploads/covers/' . $m['cover']) : url('assets/placeholder.jpg');
?>

<div class="detail-container">

  <div class="detail-header">
    <h1 class="detail-title"><?php e($m['title'] ?? '') ?></h1>
    <span class="detail-badge">Disponibilité : <?= (int)($m['stock'] ?? 0) ?></span>
  </div>

  <div class="detail-card">
    <img src="<?= $cover ?>" alt="Couverture de <?php e($m['title'] ?? '') ?>" class="detail-cover">

    <div class="detail-info">

      <p class="detail-meta">
        <span class="detail-label">Type :</span>
        <?php
          if ($type == 1) e("Livre");
          elseif ($type == 2) e("Film");
          elseif ($type == 3) e("Jeu vidéo");
          else e("Inconnu");
        ?>
        <?php if ($genre): ?>
          &nbsp;·&nbsp;<span class="detail-label">Genre :</span> <?php e($genre) ?>
        <?php endif; ?>
        <?php if (!empty($m['year'])): ?>
          &nbsp;·&nbsp;<span class="detail-label">Année :</span> <?= (int)$m['year'] ?>
        <?php endif; ?>
      </p>

      <?php if ($type == 1): // livre ?>
        <p class="detail-meta">
          <span class="detail-label">Auteur :</span> <?php e($m['author'] ?? '') ?>
          <?php if (!empty($m['isbn'])): ?> &nbsp;·&nbsp;<span class="detail-label">ISBN :</span> <?php e($m['isbn'] ?? '') ?><?php endif; ?>
          <?php if (!empty($m['pages'])): ?> &nbsp;·&nbsp;<span class="detail-label">Pages :</span> <?= (int)$m['pages'] ?><?php endif; ?>
        </p>
      <?php elseif ($type == 2): // film ?>
        <p class="detail-meta">
          <span class="detail-label">Réalisateur :</span> <?php e($m['director'] ?? '') ?>
          <?php if (!empty($m['duration'])): ?> &nbsp;·&nbsp;<span class="detail-label">Durée :</span> <?= (int)$m['duration'] ?> min<?php endif; ?>
          <?php if (!empty($m['classification'])): ?> &nbsp;·&nbsp;<span class="detail-label">Classification :</span> <?php e($m['classification'] ?? '') ?><?php endif; ?>
        </p>
      <?php elseif ($type == 3): // jeu ?>
        <p class="detail-meta">
          <span class="detail-label">Éditeur :</span> <?php e($m['publisher'] ?? '') ?>
          <?php if (!empty($m['plateform'])): ?> &nbsp;·&nbsp;<span class="detail-label">Plateforme :</span> <?php e($m['plateform'] ?? '') ?><?php endif; ?>
          <?php if (!empty($m['min_age'])): ?> &nbsp;·&nbsp;<span class="detail-label">Âge min :</span> <?php e($m['min_age'] ?? '') ?><?php endif; ?>
        </p>
      <?php endif; ?>

      <?php if (!empty($m['description'])): ?>
        <p class="detail-description">
          <span class="detail-label">Résumé :</span> <?php e($m['description'] ?? '') ?>
        </p>
      <?php endif; ?>

      <?php if (is_logged_in()): ?>
        <div class="detail-actions">
          <?php if (!empty($active_loan)): // rendre ?>
            <form method="post" action="<?= url('media/return/'.$m['id']) ?>" onsubmit="return confirm('Confirmer le retour ?');">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <button class="rendre-btn">Rendre</button>
            </form>
          <?php elseif ((int)($m['stock'] ?? 0) > 0): // emprunter ?>
            <form method="post" action="<?= url('media/loan/'.$m['id']) ?>" onsubmit="return confirm('Confirmer l\'emprunt ?');">
              <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
              <button class="emprunt-btn">Emprunter</button>
            </form>
          <?php else: ?>
            <span class="detail-unavailable">Ce média n'est pas disponible.</span>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <p class="detail-login-invite"><em>Connecte-toi pour emprunter ce média.</em></p>
      <?php endif; ?>

    </div>
  </div>

</div>