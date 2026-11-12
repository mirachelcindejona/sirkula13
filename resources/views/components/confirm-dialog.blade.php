<dialog id="confirm-dialog" class="confirm-dialog">
    <form method="dialog">
        <h3>Konfirmasi</h3>
        <p id="confirm-message"></p>
        <div class="form-actions">
            <button class="btn outline" type="button" data-confirm-cancel>Batal</button>
            <button class="btn primary" type="button" data-confirm-ok>Ya, lanjutkan</button>
        </div>
    </form>
</dialog>
<script>
    (() => {
        const dialog = document.getElementById('confirm-dialog');
        const message = document.getElementById('confirm-message');
        let pending = null;
        const finish = (accepted) => {
            const form = pending;
            pending = null;
            if (dialog.open) dialog.close();
            if (accepted && form) {
                form.dataset.confirmed = '1';
                form.requestSubmit();
            }
        };
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed === '1') {
                return;
            }
            event.preventDefault();
            message.textContent = form.dataset.confirm;
            pending = form;
            if (!dialog.open) dialog.showModal();
        });
        dialog.querySelector('[data-confirm-cancel]').addEventListener('click', () => finish(false));
        dialog.querySelector('[data-confirm-ok]').addEventListener('click', () => finish(true));
        dialog.addEventListener('cancel', (event) => {
            event.preventDefault();
            finish(false);
        });
    })();
</script>
