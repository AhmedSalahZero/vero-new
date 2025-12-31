<?php 
<?php

public function createJournalEntry(
    float $amount = 30000,
    string $date = '2025-11-19',
    int $debitAccountId = 134,
    int $creditAccountId = 225,
    int $journalId = null,                  // Optional now
    int $currencyId = 74,
    string $ref = '-------',
    string $message = '',
    string $moveName = null
) {
    // Prepare move (journal entry) data
    $moveData = [
        'date'         => $date,
        'journal_id'   => $journalId,       // Can be false/null → Odoo will pick default
        'ref'          => $ref,
        'name'         => $moveName ?? '/', // '/' lets Odoo auto-generate the number
        'line_ids'     => [
            [0, 0, [
                'account_id'   => $debitAccountId,
                'debit'        => $amount,
                'credit'       => 0.0,
                'currency_id'  => $currencyId !== 74 ? $currencyId : false,
                'name'         => $message ?: '/',
                'partner_id'   => false,
            ]],
            [0, 0, [
                'account_id'   => $creditAccountId,
                'debit'        => 0.0,
                'credit'       => $amount,
                'currency_id'  => $currencyId !== 74 ? $currencyId : false,
                'name'         => $message ?: '/',
                'partner_id'   => false,
            ]],
        ],
    ];

    // Optional: bypass move validity check only if you're 100% sure it's balanced
    $context = [
        'check_move_validity' => true,
        // 'skip_account_move_synchronization' => true, // only if needed
    ];

    $moveId = $this->execute(
        'account.move',
        'create',
        [$moveData],
        ['context' => $context]
    );

    // Optionally post the entry immediately
    if ($moveId) {
        $this->execute('account.move', 'action_post', [$moveId]);
    }


    return $moveId;
}
