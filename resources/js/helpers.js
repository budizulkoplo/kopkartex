import ClipboardJS from "clipboard";
import Swal from 'sweetalert2';

export function formatRupiah(angka, prefix = "Rp") {
    if (typeof angka !== "number") {
        angka = parseFloat(angka);
        if (isNaN(angka)) return "";
    }

    return angka.toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0
    }).replace("Rp", prefix);
}
/**
 * Init copy-to-clipboard untuk semua tombol dengan selector tertentu.
 * @param {string} selector - default: '.copy-btn'
 */
export function initClipboard(selector = ".copy-btn") {
    const clipboard = new ClipboardJS(selector);

    clipboard.on("success", (e) => {
         Swal.fire({
            toast: true,
            position: 'top-end',   // posisi: 'top', 'top-start', 'top-end', 'bottom-start', 'bottom-end'
            icon: 'success',       // 'success', 'error', 'warning', 'info', 'question'
            title: 'Text berhasil disalin: ' + e.text,
            showConfirmButton: false,
            timer: 3000,           // auto close 3 detik
            timerProgressBar: true
        });
        e.clearSelection();
    });

    clipboard.on("error", () => {
        alert("❌ Gagal menyalin!");
    });

    return clipboard;
}