<?php
// Formulaire d'ajout ou de modification d'un média

// ----------- EN-TÊTE DE PAGE -----------
?>
<div class="page-header">
    <!-- Titre dynamique selon ajout ou modification -->
    <h1><?= isset($media) ? 'Modifier' : 'Ajouter' ?> un média</h1>
</div>

<form method="POST" enctype="multipart/form-data">
    <!-- Jeton CSRF pour sécuriser le formulaire -->
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <?php if (isset($media)): ?>
        <!-- En modification, stocke la couverture précédente -->
        <input type="hidden" name="old_cover" value="<?= e($media['cover']) ?>">
    <?php endif; ?>

    <!-- Choix du type de média -->
    <label>Type</label>
    <select name="type_id" id="type_id" required>
    <?php foreach ($types as $t): ?>
        <option
            value="<?= (int)$t['id'] ?>"
            data-typeid="<?= (int)$t['id'] ?>"
            <?= isset($media) && (int)$media['type_id'] === (int)$t['id'] ? 'selected' : '' ?>
        ><?= e($t['label']) ?></option>
    <?php endforeach; ?>
    </select>

    <!-- Titre du média -->
    <label>Titre</label>
    <input type="text" name="title" value="<?= e($media['title'] ?? '') ?>" required maxlength="200">

    <!-- Genres par type -->
    <!-- Livre -->
    <div id="genre-livre" class="genre-fields">
        <label>Genre</label>
        <?php $genres_books = ['Aventure','Théâtre','Manga','Drame','Fantastique','Horreur','Science-fiction','Thriller','Romance','Poésie','BD','Polar','Historique','Biographie','Philosophie']; ?>
        <select name="genre_books">
            <option value="">— choisir —</option>
            <?php foreach ($genres_books as $g): ?>
                <option value="<?= e($g) ?>"
                    <?= (!empty($media['genre_books']) && $media['genre_books'] === $g) ? 'selected' : '' ?>>
                    <?= e($g) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Film -->
    <div id="genre-film" class="genre-fields">
        <label>Genre</label>
        <?php $genres_movies = ['Action','Aventure','Comédie','Drame','Fantastique','Horreur','Science-fiction','Thriller','Romance','Documentaire','Animation','Mystère','Crime','Guerre','Western','Musical','Historique','Biopic','Famille']; ?>
        <select name="genre_movies">
            <option value="">— choisir —</option>
            <?php foreach ($genres_movies as $g): ?>
                <option value="<?= e($g) ?>"
                    <?= (!empty($media['genre_movies']) && $media['genre_movies'] === $g) ? 'selected' : '' ?>>
                    <?= e($g) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Jeu -->
    <div id="genre-jeu" class="genre-fields">
        <label>Genre</label>
        <?php $genres_games = ['Action','Aventure','RPG','MMORPG','FPS','TPS','Jeux de sport','Courses','Simulation','Stratégie','Battle Royal','MOBA','Combat','Plateforme','Horreur','Puzzle/Réflexion','Rogue-like/Rogue-lite','Indie']; ?>
        <select name="genre_games">
            <option value="">— choisir —</option>
            <?php foreach ($genres_games as $g): ?>
                <option value="<?= e($g) ?>"
                    <?= (!empty($media['genre_games']) && $media['genre_games'] === $g) ? 'selected' : '' ?>>
                    <?= e($g) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Stock disponible -->
    <label>Disponibilité</label>
    <input type="number" name="stock" value="<?= e($media['stock'] ?? 1) ?>" min="1" required>

    <!-- Upload de l'image de couverture -->
    <label>Image de couverture</label>
    <input type="file" name="cover" accept=".jpg,.png,.gif">
    <?php if (!empty($media['cover'])): ?>
        <!-- Affiche la miniature de la couverture actuelle (mode édition) -->
        <img src="/uploads/covers/thumb_<?= e($media['cover']) ?>" alt="Couverture actuelle" style="max-height:100px;">
    <?php endif; ?>

    <!-- Champs spécifiques selon le type -->
    <!-- Livre -->
    <div id="livre-fields" class="type-fields">
        <label>Auteur</label>
        <input type="text" name="author" value="<?= e($media['author'] ?? '') ?>" maxlength="100">

        <label>ISBN</label>
        <input type="text" name="isbn" value="<?= e($media['isbn'] ?? '') ?>" pattern="\d{10}|\d{13}" maxlength="13">

        <label>Nombre de pages</label>
        <input type="number" name="pages" value="<?= e($media['pages'] ?? '') ?>" min="1" max="9999">

        <label>Année</label>
        <input type="number" name="year" value="<?= e($media['year'] ?? '') ?>" min="1900" max="<?= date('Y') ?>">
    </div>

    <!-- Film -->
    <div id="film-fields" class="type-fields">
        <label>Réalisateur</label>
        <input type="text" name="director" value="<?= e($media['director'] ?? '') ?>" maxlength="100">

        <label>Durée (min)</label>
        <input type="number" name="duration" value="<?= e($media['duration'] ?? '') ?>" min="1" max="999">

        <label>Classification</label>
        <select name="classification">
            <?php $classifs = ['Tous publics','-12','-16','-18']; ?>
            <?php foreach ($classifs as $c): ?>
                <option value="<?= e($c) ?>" <?= (isset($media['classification']) && $media['classification'] === $c) ? 'selected' : '' ?>>
                    <?= e($c) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Jeu -->
    <div id="jeu-fields" class="type-fields">
        <label>Éditeur</label>
        <input type="text" name="publisher" value="<?= e($media['publisher'] ?? '') ?>" maxlength="100">

        <label>Plateforme</label>
        <select name="plateform">
            <?php $plats = ['PC','PlayStation','Xbox','Nintendo','Mobile']; ?>
            <?php foreach ($plats as $p): ?>
                <option value="<?= e($p) ?>" <?= (isset($media['plateform']) && $media['plateform'] === $p) ? 'selected' : '' ?>>
                    <?= e($p) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Âge minimum</label>
        <select name="min_age">
            <?php foreach (['3','7','12','16','18'] as $a): ?>
                <option value="<?= e($a) ?>" <?= (isset($media['min_age']) && (string)$media['min_age'] === $a) ? 'selected' : '' ?>>
                    <?= e($a) ?>+
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Description du média -->
    <label>Résumé</label>
    <textarea name="description" maxlength="2000"><?= e($media['description'] ?? '') ?></textarea>

    <!-- Bouton d'enregistrement -->
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</form>

<script>
// Script JS pour afficher/masquer les champs selon le type sélectionné

// Sélectionne le champ du type
const typeSelect = document.getElementById('type_id');

// Associe les champs "fields" et "genre" pour chaque type (livre, film, jeu)
const byId = {
  1: { fields: document.getElementById('livre-fields'), genre: document.getElementById('genre-livre') },
  2: { fields: document.getElementById('film-fields'),  genre: document.getElementById('genre-film')  },
  3: { fields: document.getElementById('jeu-fields'),   genre: document.getElementById('genre-jeu')   }
};

// Fonction pour afficher les bons champs selon le type choisi
function toggleByTypeId() {
  const opt = typeSelect.options[typeSelect.selectedIndex];
  const id = opt ? parseInt(opt.getAttribute('data-typeid'), 10) : 0;

  // Masque tous les champs au départ
  Object.values(byId).forEach(cfg => {
    if (cfg.fields) cfg.fields.style.display = 'none';
    if (cfg.genre)  cfg.genre.style.display  = 'none';
  });

  // Affiche uniquement les champs liés au type sélectionné
  if (byId[id]) {
    if (byId[id].fields) byId[id].fields.style.display = 'block';
    if (byId[id].genre)  byId[id].genre.style.display  = 'block';
  }
}

// Ajoute le listener sur le select type
typeSelect.addEventListener('change', toggleByTypeId);
// Initialise l'affichage au chargement
toggleByTypeId();
</script>