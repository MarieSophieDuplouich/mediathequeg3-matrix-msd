<?php
$labelType = function ($type_id) {
  switch ((int)$type_id) {
    case 1: return 'Livre'; // type book
    case 2: return 'Film'; // type movie
    case 3: return 'Jeu'; // type game
    default: return 'Inconnu'; // fallback
  }
};
?>

<?php
// sécurise $items
$items = isset($items) && is_array($items) ? $items : []; // ensure array
?>

<?php if (empty($items)): ?>
    <!-- no media -->
    <p>Aucun média disponible pour le moment.</p>
<?php else: ?>
    <!-- grid medias -->
    <div class="media-grid">
        <?php foreach ($items as $i): ?>
            <!-- card media -->
            <div class="media-card">
                <!-- cover (fallback default.jpg) -->
                <img 
                    src="<?= url('uploads/covers/' . (!empty($i['cover']) ? $i['cover'] : 'default.jpg'))?>" 
                    alt="Couverture de <?= e($i['title']) ?>" class="img-index"
                >
                <!-- titre -->
                <h3 class="title"><?= e($i['title']) ?></h3>

                <!-- stock -->
                <p class="dispo">Disponibilité : <?= e($i['stock']) ?></p>

                <!-- link détail -->
                <a href="<?= url('media/detail/' . $i['id']) ?>" class="btn btn-primary">Voir</a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($pages) && $pages > 1): ?>
  <?php
    // pagination build
    $cur = max(1, (int)($page ?? 1)); // current
    $last = max(1, (int)($pages ?? 1)); // last
    $query = $_GET ?? []; unset($query['page']); // base query sans page

    // url maker
    $makeUrl = function (int $p) use ($query) {
      $q = $query; $q['page'] = $p;
      return url('home/index') . '?' . http_build_query($q);
    };
  ?>

<nav class="pagination">
    <?php if ($cur > 1): ?>
        <a href="<?= $makeUrl($cur - 1) ?>" class="page-link">« Précédent</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $last; $i++): ?>
        <?php if ($i === $cur): ?>
            <span class="page-link current"><?= $i ?></span>
        <?php else: ?>
            <a href="<?= $makeUrl($i) ?>" class="page-link"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($cur < $last): ?>
        <a href="<?= $makeUrl($cur + 1) ?>" class="page-link">Suivant »</a>
    <?php endif; ?>
</nav>

<?php endif; ?>
