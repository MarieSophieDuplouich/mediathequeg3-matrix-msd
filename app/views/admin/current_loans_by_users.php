<h2>Emprunts en cours par utilisateur</h2>
<table class="loans-table">
    <tr>
        <th style="color:#434040;">Utilisateur</th>
        <th style="color:#434040;">Titre du média</th>
        <th style="color:#434040;">Date d'emprunt</th>
    </tr>
    <?php foreach ($current_loans as $loan): ?>
        <tr>
            <td><?= e($loan['name']) ?></td>
            <td><?= e($loan['title']) ?></td>
            <td><?= e($loan['loan_date']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>