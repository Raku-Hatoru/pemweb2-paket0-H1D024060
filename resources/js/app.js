document.addEventListener('DOMContentLoaded', () => {
    const returnForm = document.querySelector('[data-return-form]');

    if (!returnForm) {
        return;
    }

    const dueDate = returnForm.getAttribute('data-due-date');
    const dailyFine = Number.parseInt(returnForm.getAttribute('data-daily-fine') ?? '1000', 10);
    const returnDateInput = returnForm.querySelector('[data-return-date]');
    const lateDaysOutput = returnForm.querySelector('[data-late-days]');
    const fineOutput = returnForm.querySelector('[data-fine-display]');

    if (!(returnDateInput instanceof HTMLInputElement) || !lateDaysOutput || !fineOutput || !dueDate) {
        return;
    }

    const formatCurrency = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(value)}`;

    const calculateFine = () => {
        const selectedDate = returnDateInput.value;

        if (!selectedDate) {
            lateDaysOutput.textContent = '0';
            fineOutput.textContent = formatCurrency(0);
            return;
        }

        const due = new Date(`${dueDate}T00:00:00`);
        const returned = new Date(`${selectedDate}T00:00:00`);
        const milliseconds = returned.getTime() - due.getTime();
        const lateDays = milliseconds > 0 ? Math.floor(milliseconds / 86400000) : 0;
        const fine = lateDays * dailyFine;

        lateDaysOutput.textContent = String(lateDays);
        fineOutput.textContent = formatCurrency(fine);
    };

    returnDateInput.addEventListener('input', calculateFine);
    calculateFine();
});
