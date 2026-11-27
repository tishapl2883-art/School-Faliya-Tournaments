<?php $__env->startSection('title', 'Player Registration'); ?>
<?php $__env->startSection('content'); ?>
<h1 class="title">Player Registration</h1>
<?php if($site['registration_open'] !== '1'): ?>
    <div class="card empty">Registration is closed. Please contact the organiser.</div>
<?php else: ?>
<form class="card form" method="post" action="<?php echo e(route('register')); ?>" enctype="multipart/form-data" data-once>
    <?php echo csrf_field(); ?>
    <label class="full">Full name *<input name="name" value="<?php echo e(old('name')); ?>" maxlength="100" required></label>
    <label>Mobile number *<input name="mobile" value="<?php echo e(old('mobile')); ?>" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" required></label>
    <label>Date of birth *<input type="date" name="dob" value="<?php echo e(old('dob')); ?>" required></label>
    <label class="full">Village / Faliya *<input name="village" value="<?php echo e(old('village')); ?>" maxlength="100" required></label>
    <?php $__currentLoopData = ['role' => ['Playing role', \App\Models\Player::ROLES], 'batting_style' => ['Batting style', \App\Models\Player::BATTING], 'bowling_style' => ['Bowling style', \App\Models\Player::BOWLING], 'tshirt_size' => ['T-shirt size', \App\Models\Player::SIZES]]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field => [$label, $options]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <label><?php echo e($label); ?> *
            <select name="<?php echo e($field); ?>" required>
                <option value="">Select</option>
                <?php $__currentLoopData = $options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php if(old($field) === $o): echo 'selected'; endif; ?>><?php echo e($o); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </label>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <label>Name on jersey<input name="jersey_name" value="<?php echo e(old('jersey_name')); ?>" maxlength="20"></label>
    <label>Jersey number<input type="number" name="jersey_number" value="<?php echo e(old('jersey_number')); ?>" min="0" max="999"></label>
    <label class="full">Player photo * (JPG, PNG or WEBP, up to 4 MB)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
    <?php if((int) $site['registration_fee'] > 0): ?>
        <?php
            $upiParams = $site['upi_id'] ? http_build_query([
                'pa' => $site['upi_id'],
                'pn' => 'JAYMAL BAKULBHAI PATEL',
                'am' => (int) $site['registration_fee'],
                'cu' => 'INR',
                'mc' => '0000',
                'mode' => '02',
                'purpose' => '00',
                'tn' => $site['title'].' Registration',
            ], '', '&', PHP_QUERY_RFC3986) : '';
            $phonepeUrl = $upiParams ? 'phonepe://pay?'.$upiParams : '';
            $androidPhonepeIntent = $upiParams ? 'intent://pay?'.$upiParams.'#Intent;scheme=upi;package=com.phonepe.app;end' : '';
            $upiUrl = $upiParams ? 'upi://pay?'.$upiParams : '';
        ?>
        <div class="full pay" style="text-align:center">
            <strong style="font-size:17px">Registration fee: &#8377;<?php echo e($site['registration_fee']); ?></strong>
            <?php if(! empty($site['payment_qr'])): ?>
                <?php $qrSrc = str_starts_with($site['payment_qr'], 'images/') ? asset($site['payment_qr']) : asset('storage/'.$site['payment_qr']); ?>
                <div style="margin:10px auto">
                    <?php if($phonepeUrl): ?>
                        <a href="<?php echo e($phonepeUrl); ?>" data-phonepe-intent="<?php echo e($androidPhonepeIntent); ?>" title="Tap QR to pay &#8377;<?php echo e($site['registration_fee']); ?> in PhonePe" style="display:inline-block">
                            <img src="<?php echo e($qrSrc); ?>" alt="Tap to pay via PhonePe QR" style="width:240px;max-width:100%;border-radius:12px;border:2px solid var(--gold);background:#fff;cursor:pointer">
                        </a>
                    <?php else: ?>
                        <img src="<?php echo e($qrSrc); ?>" alt="Payment QR Code" style="width:240px;max-width:100%;border-radius:12px;border:2px solid var(--gold);background:#fff">
                    <?php endif; ?>
                </div>
                <span style="font-size:13px">Tap the QR code or button below to open <strong>PhonePe</strong> for &#8377;<?php echo e($site['registration_fee']); ?>.</span>
            <?php endif; ?>
            <?php if($upiParams): ?>
                <div class="actions" style="justify-content:center;margin:10px 0 6px">
                    <a class="btn small" href="<?php echo e($phonepeUrl); ?>" data-phonepe-intent="<?php echo e($androidPhonepeIntent); ?>" style="background:#5f259f;color:#fff">Pay &#8377;<?php echo e($site['registration_fee']); ?> in PhonePe</a>
                    <a class="btn small gold" href="<?php echo e($upiUrl); ?>">Other UPI App</a>
                    <?php if(! empty($qrSrc)): ?>
                        <a class="btn small" href="<?php echo e($qrSrc); ?>" download="parnera-phonepe-qr.jpg">Save QR</a>
                    <?php endif; ?>
                    <button type="button" class="btn small" id="copy-upi" data-upi="<?php echo e($site['upi_id']); ?>">Copy UPI ID</button>
                </div>
                <span style="font-size:12px">UPI ID: <b><?php echo e($site['upi_id']); ?></b></span>
            <?php endif; ?>
        </div>
        <?php if($upiParams): ?>
            <script>
            (() => {
                if (/android/i.test(navigator.userAgent)) {
                    document.querySelectorAll('[data-phonepe-intent]').forEach((a) => {
                        a.setAttribute('href', a.dataset.phonepeIntent);
                    });
                }
                const copyBtn = document.getElementById('copy-upi');
                copyBtn?.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(copyBtn.dataset.upi);
                        copyBtn.textContent = 'Copied!';
                        setTimeout(() => { copyBtn.textContent = 'Copy UPI ID'; }, 1800);
                    } catch (e) {
                        prompt('Copy this UPI ID:', copyBtn.dataset.upi);
                    }
                });
            })();
            </script>
        <?php endif; ?>
        <label>UPI transaction ID *<input name="payment_ref" value="<?php echo e(old('payment_ref')); ?>" maxlength="60" required></label>
        <label>Payment screenshot *<input type="file" name="payment_proof" accept="image/jpeg,image/png,image/webp" required></label>
    <?php endif; ?>
    <label class="full check"><input type="checkbox" name="terms" value="1" required><span>I accept the <a href="<?php echo e(route('page', 'terms')); ?>" target="_blank">terms and conditions</a>.</span></label>
    <button class="btn gold full">Submit Registration</button>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Kamlesh\Downloads\parnera-champions-trophy\parnera\resources\views/public/register.blade.php ENDPATH**/ ?>