<div class="page-header">
    <h1>Administration</h1>
</div>

<!-- Section statistiques générales -->
<h2>Statistiques</h2>
<table class="category-table">
    <tr>
        <th style="color:#434040;">Category</th>
        <th style="color:#434040;">Total</th>
    </tr>

    <!-- Affiche le nombre total d'utilisateurs -->
    <tr>
        <td>Total Utilisateurs</td>
        <td><?= $stats['total_utilisateur'] ?></td>
    </tr>
    <!-- Affiche le nombre de nouveau utilisateurs sur 7 jours. -->
    <tr>
        <td>Nouveaux Utilisateur 7d</td>
        <td><?= $stats['nouveaux_utilisateur_7d'] ?></td>
    </tr>
    <!-- Affiche le nombre de nouveau utilisateurs sur 30 jours. -->
    <tr>
        <td>Nouveaux Utilisateur 30d</td>
        <td><?= $stats['nouveaux_utilisateur_30d'] ?></td>
    </tr>

    <!-- Affiche le nombre total de livres -->
    <tr>
        <td>Total Livres</td>
        <td><?= $stats['total_livres'] ?></td>
    </tr>
    <!-- Affiche le nombre total de films -->
    <tr>
        <td>Total Films</td>
        <td><?= $stats['total_films'] ?></td>
    </tr>
    <!-- Affiche le nombre total de jeux -->
    <tr>
        <td>Total Jeux</td>
        <td><?= $stats['total_jeux'] ?></td>
    </tr>
    <!-- Affiche le nombre d'emprunts en cours -->
    <tr>
        <td>Emprunts en cours</td>
        <td><?= $stats['emprunts_en_cours'] ?></td>
    </tr>
</table>

<!-- Section emprunts en retard, affichée uniquement si des emprunts en retard existent -->
<?php if (!empty($stats['overdue_loans'])): ?>
    <h3>📛 Emprunts en retard</h3>
    <table class="loans-table">
        <tr>
            <th>Utilisateur</th>
            <th>Media</th>
            <th>Date de Retour Attendu</th>
        </tr>
        <!-- Boucle sur chaque emprunt en retard et affiche les détails -->
        <?php foreach ($stats['overdue_loans'] as $e): ?>
            <tr>
                <td><?= e($e['user_name']) ?></td>
                <td><?= e($e['media_title']) ?></td>
                <td><?= e($e['expected_return_date']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<!-- Section liste des médias -->
<h2>Liste des Médias</h2>
<div class="admin-actions">
    <div class="center">

        <!-- Lien pour ajouter un nouveau média -->
        <a href="<?= url('admin/add') ?>" class="btn-add-media">➕ Ajouter un Media</a>

        <div class="admin-actions" style="margin: 20px 0; display: flex; flex-wrap: wrap; gap: 12px;">
            <a class="btn-admin btn-beige1" href="<?= url('admin/current_loans_by_users') ?>">Emprunts en cours par
                utilisateur</a>
            <a class="btn-admin btn-beige2" href="<?= url('admin/users_utilisation') ?>">Statistiques d’utilisation par
                utilisateur</a>
            <a class="btn-admin btn-beige3" href="<?= url('admin/users_history') ?>">Historique des emprunts</a>
            <a class="btn-admin btn-beige4" href="<?= url('admin/registered_users') ?>">Utilisateurs inscrits</a>
            <a class="btn-admin btn-beige5" href="<?= url('admin/current_late_loans_by_users') ?>">Emprunts en
                retard</a>
        </div>



        <table class="media-table">
            <tr>
                <th style="color:#434040;">Titre</th>
                <th style="color:#434040;">Type</th>
                <th style="color:#434040;">Stock</th>
                <th style="color:#434040;">Actions</th>
            </tr>
            <!-- Boucle sur les médias pour les afficher -->
            <?php foreach ($medias as $m): ?>
                <tr>
                    <td><img <?php $cover = cover_path($m['cover'] ?? ''); // compute cover ?> src="<?= url($cover) ?>"
                            alt="Couverture de <?= e($m['title'] ?? '') ?>" alt="Couverture de <?= e($m['title']) ?>"
                            class="img-index"><?= e($m['title']) ?></td>
                    <!-- Affiche le type du média (Livre, Film, Jeu, ou Inconnu) -->
                    <td><?= e($m['type_id'] == 1 ? 'Livre' : ($m['type_id'] == 2 ? 'Film' : ($m['type_id'] == 3 ? 'Jeu' : 'Inconnu'))) ?>
                    </td>
                    <td><?= e($m['stock']) ?></td>
                    <td class="actions">
                        <!-- Lien pour éditer le média -->
                        <a href="<?= url('admin/edit/' . $m['id']) ?>" class="action-btn">✏️ Modifier</a>
                        <!-- Lien pour supprimer le média, confirmation demandée -->
                        <a href="<?= url('admin/delete/' . $m['id']) ?>" class="action-btn"
                            onclick="return confirm('Delete?')">🗑️ Supprimer</a>
                        <!-- Bouton pour supprimer l'image du média -->
                        <form method="post" action="<?= url('admin/delete_image/' . $m['id']) ?>
                            <button type=" submit" class="action-btn"
                            onclick="return confirm('Supprimer l\'image de ce média ?')">🖼️ Supprimer image</button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Section des emprunts en cours -->
        <h2>Emprunts en Cours</h2>
        <table class="loans-table">
            <tr>
                <th style="color:#434040;">Utilisateur</th>
                <th style="color:#434040;">Media</th>
                <th style="color:#434040;">Date</th>
                <th style="color:#434040;">Date de Retour Attendu</th>
                <th style="color:#434040;">Action</th>

                <!-- Boucle sur tous les emprunts et affiche seulement ceux non rendus -->
                <?php foreach ($loans as $e): ?>
                    <?php if (!$e['actual_return_date']): ?>
                    <tr>
                        <td><?= e($e['user_name']) ?></td>
                        <td><?= e($e['media_title']) ?></td>
                        <td><?= e($e['loan_date']) ?></td>
                        <td><?= e($e['expected_return_date']) ?></td>
                        <!-- Lien pour enregistrer le retour du média -->
                        <td><a href="<?= url('admin/retour/' . $e['id']) ?>" class="action-btn">✅ Retour</a></td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>