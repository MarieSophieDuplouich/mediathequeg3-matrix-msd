<?php
require_once ROOT_PATH . '/models/admin_model.php';

// 1. Traiter la suppression si le formulaire est soumis
handle_user_delete_post();

// 2. Récupérer la liste des utilisateurs
$users = db_select("
    SELECT id, name, email, created_at
    FROM users
    WHERE deleted_at IS NULL
    ORDER BY created_at DESC
");
?>
<h2>Utilisateurs inscrits</h2>
<table class="category-table">
    <thead>
        <tr>
            <th style="color:#434040;">ID</th>
            <th style="color:#434040;">Nom</th>
            <th style="color:#434040;">Email</th>
            <th style="color:#434040;">Date d'inscription</th>
            <th style="color:#434040;">Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($users)): ?>
            <tr>
                <td colspan="5">Aucun utilisateur inscrit.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['id']) ?></td>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><?= e($user['created_at']) ?></td>
                    <td>
                       <form method="post" action="" 
                            onsubmit="return confirm('Confirmer la suppression de cet utilisateur ?');">
                            <input type="hidden" name="user_id" value="<?= e($user['id']) ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>