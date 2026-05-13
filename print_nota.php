<?php
/**
 * FILE: print_nota.php
 */
require_once 'database/db.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data && isset($_POST['data'])) {
    $data = json_decode($_POST['data'], true);
}

if (!$data) exit('Data tidak ditemukan.');

$items = $data['items'];
$business = $data['business'];
$totals = $data['totals'];
$noteNumber = $data['noteNumber'];
$customerName = $data['customerName'];
$customerObj = $data['customerObj'] ?? null;
$noteDateFormatted = $data['noteDateFormatted'];

$maxLines = 15;
$totalPages = ceil(count($items) / $maxLines);
if ($totalPages == 0) $totalPages = 1;

function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

$cssContent = file_get_contents('nota_print.css');
?>
<style>
    <?php echo $cssContent; ?>
</style>

<div id="print-content">
    <?php 
    $cumulativeSubtotal = 0;
    for ($i = 0; $i < $totalPages; $i++): 
        $isFirstPage = ($i === 0);
        $isLastPage = ($i === $totalPages - 1);
        $start = $i * $maxLines;
        $pageItems = array_slice($items, $start, $maxLines);
        $pageSubtotal = 0;
        foreach($pageItems as $it) $pageSubtotal += ($it['qty'] * $it['price']);
    ?>
    <div class="print-page">
        <div class="print-header">
            <div class="shop-info">
                <img src="<?php echo $business['logoPath'] ?: 'assets/logo.png'; ?>" onerror="this.src='assets/logo.png'">
                <div class="shop-details">
                    <h2><?php echo $business['bizName'] ?: 'SMARTNOTE'; ?></h2>
                    <p class="biz-type"><?php echo $business['bizType']; ?></p>
                    <p class="address"><?php echo $business['bizAddress']; ?></p>
                    <p class="biz-type">Telp: <?php echo $business['bizPhone'] ?: $business['hp']; ?></p>
                </div>
            </div>
            <div class="nota-info">
                <h1>Nota Pembayaran</h1>
                <p><strong>No:</strong> <?php echo $noteNumber; ?></p>
                <p><strong>Kepada:</strong> <?php echo $customerName; ?></p>
                <?php if ($customerObj && !empty($customerObj['hp'])): ?>
                    <p>Telp: <?php echo $customerObj['hp']; ?></p>
                <?php endif; ?>
                <p style="margin-top:4px; color:#666; font-size:7pt;">Hal: <?php echo ($i+1) . ' / ' . $totalPages; ?></p>
            </div>
        </div>

        <table class="print-table">
            <thead>
                <tr>
                    <th style="width:30px;">No</th>
                    <th>Deskripsi</th>
                    <th style="width:40px;">Qty</th>
                    <th style="width:90px; text-align:right;">Harga</th>
                    <th style="width:110px; text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$isFirstPage): ?>
                <tr style="font-style:italic; font-size:8pt;">
                    <td colspan="4" style="padding: 6px 0; border-bottom: 1px solid #eee;">Pindahan dari halaman sebelumnya...</td>
                    <td style="text-align: right; padding: 6px 0; border-bottom: 1px solid #eee; font-weight:bold;"><?php echo formatRupiah($cumulativeSubtotal); ?></td>
                </tr>
                <?php endif; ?>

                <?php 
                foreach($pageItems as $idx => $item): 
                    $rowTotal = $item['qty'] * $item['price'];
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo ($start + $idx + 1); ?></td>
                    <td><?php echo $item['description'] ?: '-'; ?></td>
                    <td style="text-align:center;"><?php echo $item['qty']; ?></td>
                    <td style="text-align:right;"><?php echo formatRupiah($item['price']); ?></td>
                    <td style="text-align:right; font-weight: 500;"><?php echo formatRupiah($rowTotal); ?></td>
                </tr>
                <?php endforeach; ?>

                <?php 
                $cumulativeSubtotal += $pageSubtotal;
                if ($totalPages > 1 && !$isLastPage): 
                ?>
                <tr style="font-style:italic; font-size:8pt;">
                    <td colspan="4" style="padding: 6px 0; border-top: 1px solid #eee;">Subtotal Halaman <?php echo ($i+1); ?>...</td>
                    <td style="text-align: right; padding: 6px 0; border-top: 1px solid #eee; font-weight:bold;"><?php echo formatRupiah($cumulativeSubtotal); ?></td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if ($isLastPage): ?>
        <div class="print-footer-container">
            <div class="footer-left">
                <div class="info-box">
                    <strong>Terbilang:</strong><br>
                    <span style="font-style: italic;"><?php echo $data['terbilang']; ?></span>
                </div>
                <div class="info-box">
                    <?php echo $data['footer']; ?>
                </div>
                <div style="margin-top: 25px; text-align: center; width: 180px; font-size: 8.5pt;">
                    Pelanggan,<br><br><br><br><br>
                    ( .............................. )
                </div>
            </div>
            <div class="footer-right">
                <table class="total-table">
                    <tr><td>Subtotal</td><td style="text-align: right;"><?php echo formatRupiah($totals['subtotal']); ?></td></tr>
                    <tr><td>Servis</td><td style="text-align: right;"><?php echo formatRupiah($totals['service']); ?></td></tr>
                    <tr><td>Transport</td><td style="text-align: right;"><?php echo formatRupiah($totals['transport']); ?></td></tr>
                    <tr><td>Diskon</td><td style="text-align: right;">- <?php echo formatRupiah($totals['discount']); ?></td></tr>
                    <tr class="total-row-grand">
                        <td style="padding: 8px 0;">TOTAL</td>
                        <td style="text-align: right; padding: 8px 0;"><?php echo formatRupiah($totals['grandTotal']); ?></td>
                    </tr>
                </table>
                <div style="margin-top: 20px; text-align: center; font-size: 8.5pt;">
                    <?php echo $business['bizCity'] ?: 'Kota'; ?>, <?php echo $noteDateFormatted; ?><br>
                    Hormat Kami,<br><br><br><br><br>
                    <strong>( <?php echo $business['bizOwner'] ?: 'Pemilik'; ?> )</strong>
                </div>
            </div>
        </div>
        <?php else: ?>
        <p style="text-align:right; font-size:8pt; font-style:italic; margin-top:10px; color: #94a3b8; border-top: 1px dashed #e2e8f0; padding-top: 10px;">Bersambung...</p>
        <?php endif; ?>
    </div>
    <?php endfor; ?>
</div>
