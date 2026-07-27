import QRCode from 'qrcode';

/** Modal bagikan profil: render QR code ke canvas + salin tautan ke clipboard. */
export default function shareProfile(url) {
    return {
        url,
        copied: false,

        renderQr() {
            QRCode.toCanvas(this.$refs.qr, this.url, { width: 200, margin: 1 }).catch(() => {});
        },

        copy() {
            navigator.clipboard.writeText(this.url).then(() => {
                this.copied = true;
                setTimeout(() => (this.copied = false), 2000);
            });
        },
    };
}
