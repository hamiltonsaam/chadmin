/**
 * Returns a human-readable due status label for any due date string.
 *
 * @param string $dueDateStr  Date in 'Y-m-d' or 'd/m/Y' format
 * @return array{
 *   status:    string,   // 'overdue' | 'due_soon' | 'ok'
 *   label:     string,   // e.g. 'OVERDUE — 10 days ago'
 *   days:      int,      // negative = past, positive = future
 *   tag:       string,   // short tag e.g. 'OVERDUE' | 'DUE IN 14 DAYS' | 'OK'
 * }
 */
function due_status(string $dueDateStr): array
{
    if ($dueDateStr === '') {
        return ['status' => 'ok', 'label' => '—', 'days' => 0, 'tag' => '—'];
    }

    $today   = new DateTimeImmutable('today');
    $dueDate = DateTimeImmutable::createFromFormat('Y-m-d', $dueDateStr)
            ?: DateTimeImmutable::createFromFormat('d/m/Y', $dueDateStr);

    if (!$dueDate) {
        return ['status' => 'ok', 'label' => $dueDateStr, 'days' => 0, 'tag' => $dueDateStr];
    }

    $diff  = (int) $today->diff($dueDate)->days;
    $isPast = $dueDate < $today;
    $days  = $isPast ? -$diff : $diff;

    $dayWord = abs($days) === 1 ? 'day' : 'days';

    if ($isPast) {
        return [
            'status' => 'overdue',
            'label'  => 'OVERDUE — ' . abs($days) . ' ' . $dayWord . ' ago',
            'days'   => $days,
            'tag'    => 'OVERDUE',
        ];
    }

    if ($days === 0) {
        return [
            'status' => 'overdue',
            'label'  => 'DUE TODAY',
            'days'   => 0,
            'tag'    => 'DUE TODAY',
        ];
    }

    // You can adjust this threshold — currently 30 days = due_soon
    if ($days <= 30) {
        return [
            'status' => 'due_soon',
            'label'  => 'DUE IN ' . $days . ' ' . $dayWord,
            'days'   => $days,
            'tag'    => 'DUE IN ' . $days . ' ' . $dayWord,
        ];
    }

    return [
        'status' => 'ok',
        'label'  => 'DUE IN ' . $days . ' ' . $dayWord,
        'days'   => $days,
        'tag'    => 'DUE IN ' . $days . ' ' . $dayWord,
    ];
}