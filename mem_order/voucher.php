<?php
// voucher.php

function validateVoucher($conn, $voucher_code, $user_id) {
    // TODO: Create vouchers table
    // TODO: Create voucher_usages table
    
    $stmt = $conn->prepare("
        SELECT v.voucher_id, v.discount_amount, v.expiry_date, v.usage_limit,
               COALESCE(COUNT(vu.usage_id), 0) as usage_count
        FROM vouchers v
        LEFT JOIN voucher_usages vu ON v.voucher_id = vu.voucher_id AND vu.user_id = ?
        WHERE v.code = ?
        GROUP BY v.voucher_id
    ");
    
    $stmt->execute([$user_id, $voucher_code]);
    $voucher = $stmt->fetch();
    
    if (!$voucher) {
        return ['valid' => false, 'message' => 'Invalid voucher code'];
    }
    
    if ($voucher['expiry_date'] < date('Y-m-d')) {
        return ['valid' => false, 'message' => 'Voucher has expired'];
    }
    
    if ($voucher['usage_limit'] > 0 && $voucher['usage_count'] >= $voucher['usage_limit']) {
        return ['valid' => false, 'message' => 'Voucher has reached its usage limit'];
    }
    
    return [
        'valid' => true,
        'discount_amount' => $voucher['discount_amount'],
        'voucher_id' => $voucher['voucher_id']
    ];
}

function recordVoucherUsage($conn, $voucher_id, $user_id) {
    $stmt = $conn->prepare("
        INSERT INTO voucher_usages (voucher_id, user_id, used_at)
        VALUES (?, ?, NOW())
    ");
    
    return $stmt->execute([$voucher_id, $user_id]);
}
?>
