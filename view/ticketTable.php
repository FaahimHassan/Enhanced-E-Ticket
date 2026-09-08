<form method="get" class="filter-row">
    <label>Filter status
        <select name="status">
            <option value="">All tickets</option>
            <option value="open" <?= $status==='open'?'selected':'' ?>>Open</option>
            <option value="resolved" <?= $status==='resolved'?'selected':'' ?>>Resolved</option>
        </select>
    </label>
    <button class="button small">Apply filter</button>
</form>
<div class="table-wrap"><table>
    <thead><tr><th>Ticket</th><th>Subject</th><th>Passenger / provider</th><th>Type</th><th>Status</th><th>Updated</th></tr></thead>
    <tbody>
    <?php foreach ($tickets as $ticket): ?>
        <tr>
            <td><a href="support_ticket.php?id=<?= $ticket['ticket_id'] ?>">#<?= $ticket['ticket_id'] ?></a></td>
            <td><a href="support_ticket.php?id=<?= $ticket['ticket_id'] ?>"><?= e($ticket['subject']) ?></a></td>
            <td><?= e($ticket['passenger_name']) ?><br><small><?= e($ticket['provider_name'] ?? 'Platform admin') ?></small></td>
            <td><?= e(ucfirst($ticket['category'])) ?> <?= $ticket['rating'] ? e($ticket['rating']).'/5' : '' ?></td>
            <td><span class="tag"><?= e($ticket['status']) ?></span></td>
            <td><?= e($ticket['updated_at']) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$tickets): ?><tr><td colspan="6">No tickets match this filter.</td></tr><?php endif; ?>
    </tbody>
</table></div>
