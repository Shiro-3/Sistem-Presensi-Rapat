@push('scripts')
<script>
(() => {
    const acakAngka = (n) => crypto.getRandomValues(new Uint32Array(1))[0] % n;
    const pilih = (s) => s[acakAngka(s.length)];

    function buatPassword(panjang = 12) {
        const besar = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const kecil = 'abcdefghijkmnpqrstuvwxyz';
        const angka = '23456789';
        const semua = besar + kecil + angka;

        const hasil = [pilih(besar), pilih(kecil), pilih(angka)];
        while (hasil.length < panjang) hasil.push(pilih(semua));

        for (let i = hasil.length - 1; i > 0; i--) {   // acak urutan
            const j = acakAngka(i + 1);
            [hasil[i], hasil[j]] = [hasil[j], hasil[i]];
        }
        return hasil.join('');
    }

    document.querySelectorAll('.btn-acak').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById(btn.dataset.target).value = buatPassword();
        });
    });

    document.querySelectorAll('.btn-salin').forEach(btn => {
        btn.addEventListener('click', async () => {
            const nilai = document.getElementById(btn.dataset.target).value;
            if (!nilai) return;
            try {
                await navigator.clipboard.writeText(nilai);
                showToast('Password disalin ke clipboard');
            } catch (e) {
                window.prompt('Salin password (Ctrl+C):', nilai);
            }
        });
    });
})();
</script>
@endpush