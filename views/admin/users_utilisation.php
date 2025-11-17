<h2>Statistiques d'utilisation par utilisateur</h2>
<table class="category-table">
    <tr>
        <th style="color:#434040;">Utilisateur</th>
        <th style="color:#434040;">Total emprunts</th>
        <th style="color:#434040;">Emprunts en cours</th>
    </tr>
    <?php foreach ($users_stats as $user): ?>
        <tr>
            <td><?= e($user['name']) ?></td>
            <td><?= e($user['total_loans']) ?></td>
            <td><?= e($user['loans_in_progress']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>