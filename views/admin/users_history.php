<h2>Historique des emprunts</h2>
<table>
    <tr>
         <th style="color:#434040;">Utilisateur</th>
         <th style="color:#434040;">Média</th>
         <th style="color:#434040;">Date d'emprunt</th>
         <th style="color:#434040;">Date de retour prévue</th>
         <th style="color:#434040;">Date de retour effective</th>
    </tr>
    <?php foreach ($history as $entry): ?>
        <tr>
            <td><?= e($entry['user_name']) ?></td>
            <td><?= e($entry['media_title']) ?></td>
            <td><?= e($entry['loan_date']) ?></td>
            <td><?= e($entry['expected_return_date']) ?></td>
            <td><?= e($entry['actual_return_date'] ?? '-') ?></td>
        </tr>
    <?php endforeach; ?>
</table>

