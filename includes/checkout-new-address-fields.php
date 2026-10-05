<?php // includes/checkout-new-address-fields.php — Reusable new address form fields ?>
<div class="form-group">
    <label class="form-label">Recipient Name <span style="color:var(--color-danger)">*</span></label>
    <input type="text" name="full_name" class="form-control" placeholder="Rahul Sharma"
           value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
</div>
<div class="form-group">
    <label class="form-label">Phone Number <span style="color:var(--color-danger)">*</span></label>
    <input type="tel" name="phone" class="form-control" placeholder="9876543210"
           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
</div>
<div class="form-group">
    <label class="form-label">Site / Street Address <span style="color:var(--color-danger)">*</span></label>
    <textarea name="address" class="form-control" rows="2"
              placeholder="Plot No, Street, Sector..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
</div>
<div class="form-group">
    <label class="form-label">Apartment / Flat / Unit (Optional)</label>
    <input type="text" name="apartment" class="form-control" placeholder="Flat 4B, Tower C"
           value="<?php echo htmlspecialchars($_POST['apartment'] ?? ''); ?>">
</div>
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px;">
    <div class="form-group">
        <label class="form-label">City <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="city" class="form-control"
               value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label class="form-label">State</label>
        <input type="text" name="state" class="form-control"
               value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
    </div>
    <div class="form-group">
        <label class="form-label">Pincode <span style="color:var(--color-danger)">*</span></label>
        <input type="text" name="pincode" class="form-control" maxlength="6"
               value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>">
    </div>
</div>
<label style="display: flex; align-items: center; gap: 8px; margin-top: 8px; font-size: 0.88rem; cursor: pointer;">
    <input type="checkbox" name="save_address" value="1"> Save this address to my account
</label>
