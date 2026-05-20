<h2>Emprunts en retard par utilisateur</h2>
<table class="loans-table">
    <tr>
        <th style="color:#434040;">Utilisateur</th>
        <th style="color:#434040;">Média</th>
        <th style="color:#434040;">Date d'emprunt</th>
        <th style="color:#434040;">Date de retour prévue</th>
    </tr>
    <?php if (!empty($late_loans)): ?>
        <?php foreach ($late_loans as $loan): ?>
            <tr>
                <td><?= e($loan['user_name'] ?? '') ?></td>
                <td><?= e($loan['media_title'] ?? '') ?></td>
                <td><?= e($loan['loan_date'] ?? '') ?></td>
                <td><?= e($loan['expected_return_date'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="4" style="text-align:center;">Aucun emprunt en retard trouvé.</td>
        </tr>
    <?php endif; ?>
</table>