<div class="mt-20">
    <details>
        <summary class="fw-700" style="cursor:pointer;">Latest filings</summary>

        <div class="mt-16">
            <?php if (!$selectedFilings): ?>
                <div class="muted">No filing data yet.</div>
            <?php else: ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($selectedFilings as $filing): ?>
                            <tr>
                                <td><?= h(format_date_display($filing['filing_date'])) ?></td>
                                <td><?= h($filing['category']) ?></td>
                                <td><?= h($filing['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </details>
</div>