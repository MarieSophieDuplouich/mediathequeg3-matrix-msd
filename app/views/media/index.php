<h1></h1>

<?php if (empty($items)): ?>
  <p>Aucun média disponible pour le moment.</p> <!-- rien à lister -->
<?php else: ?>
  <ul>
    <?php foreach ($items as $m): ?>
      <?php $cover = cover_path($m['cover'] ?? ''); // compute cover ?>
      <li>
        <img class="search-image" src="<?= url($cover) ?>" alt="Couverture de <?= e($m['title'] ?? '') ?>"> <!-- img -->
        <h3 class=media-detail-title><a href="<?= url('media/detail/'.$m['id']) ?>"><?= e($m['title']) ?></a></h3> <!-- titre + lien -->
        <p>Disponibilité : <?= (int)$m['stock'] ?></p> <!-- dispo -->
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php if (!empty($pages) && $pages > 1): ?>
  <?php
    // pagination vars
    $cur   = max(1, (int)($page ?? 1)); // current page
    $last  = max(1, (int)($pages ?? 1)); // last page
    $query = $_GET ?? []; unset($query['page']); // base query no page

    // builder url page
    $makeUrl = function (int $p) use ($query) {
      $q = $query; $q['page'] = $p;
      return url('media') . '?' . http_build_query($q);
    };
  ?>
  <nav aria-label="pagination">
    <?php if ($cur > 1): ?><a href="<?= $makeUrl($cur - 1) ?>">« Précédent</a><?php endif; ?> <!-- prev -->
    <?php for ($i = 1; $i <= $last; $i++): ?>
      <?php if ($i === $cur): ?>[<?= $i ?>]<?php else: ?><a href="<?= $makeUrl($i) ?>"><?= $i ?></a><?php endif; ?> <!-- page num -->
    <?php endfor; ?>
    <?php if ($cur < $last): ?><a href="<?= $makeUrl($cur + 1) ?>">Suivant »</a><?php endif; ?> <!-- next -->
  </nav>
<?php endif; ?>
