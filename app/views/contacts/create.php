<?php
function old(string $field, array $old): string {
    return htmlspecialchars($old[$field] ?? '');
}
function err(string $field, array $errors): string {
    if (!isset($errors[$field])) return '';
    return '<span style="color:var(--danger);font-size:.8rem;">' . htmlspecialchars($errors[$field]) . '</span>';
}
?>

<div class="page-header">
    <h1 class="page-title">New Contact</h1>
    <a href="/contacts" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Back to Contacts
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/contacts/store" novalidate>

            <!-- Basic Info -->
            <div class="form-section-label">Basic Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="name">Full Name <span style="color:var(--danger)">*</span></label>
                    <input type="text" id="name" name="name"
                           value="<?= old('name', $old) ?>" required maxlength="150">
                    <?= err('name', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="company">Company</label>
                    <input type="text" id="company" name="company"
                           value="<?= old('company', $old) ?>" maxlength="150">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           value="<?= old('email', $old) ?>" maxlength="255">
                    <?= err('email', $errors) ?>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?= old('phone', $old) ?>" maxlength="50">
                    <?= err('phone', $errors) ?>
                </div>
            </div>

            <!-- Address -->
            <div class="form-section-label" style="margin-top:1.5rem;">Address</div>
            <div class="form-grid">
                <div class="form-group full">
                    <label for="address1">Line 1</label>
                    <input type="text" id="address1" name="address1"
                           value="<?= old('address1', $old) ?>" maxlength="255">
                </div>

                <div class="form-group full">
                    <label for="address2">Line 2</label>
                    <input type="text" id="address2" name="address2"
                           value="<?= old('address2', $old) ?>" maxlength="255">
                </div>

                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city"
                           value="<?= old('city', $old) ?>" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="state">State / Province</label>
                    <input type="text" id="state" name="state"
                           value="<?= old('state', $old) ?>" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="zip">ZIP / Postal Code</label>
                    <input type="text" id="zip" name="zip"
                           value="<?= old('zip', $old) ?>" maxlength="20">
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country"
                           value="<?= old('country', $old) ?>" maxlength="100">
                </div>
            </div>

            <!-- Notes -->
            <div class="form-section-label" style="margin-top:1.5rem;">Notes</div>
            <div class="form-grid">
                <div class="form-group full">
                    <textarea id="notes" name="notes" rows="4"><?= old('notes', $old) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i> Save Contact
                </button>
                <a href="/contacts" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
