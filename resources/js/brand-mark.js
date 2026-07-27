/**
 * Menyesuaikan ukuran font nama situs agar proporsional terhadap lebar logo
 * yang dirender — logo diunggah bebas rasio aspek (tinggi tetap via CSS),
 * jadi lebarnya baru diketahui setelah dirender di browser.
 */
export default function brandMark({ min = 10, max = 18, ratio = 0.135 } = {}) {
    return {
        fontSize: min,

        measure() {
            const width = this.$refs.logo?.getBoundingClientRect().width || 0;

            if (width > 0) {
                this.fontSize = Math.min(max, Math.max(min, Math.round(width * ratio)));
            }
        },
    };
}
