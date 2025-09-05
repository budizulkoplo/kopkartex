
// ==========================
// JQUERY GLOBAL DULUAN
// ==========================
// import $ from "jquery";

// // expose ke global biar bisa dipakai inline di Blade
// window.$ = window.jQuery = $;
// daterangepicker

// ==========================
// CSS & PLUGIN YANG BUTUH JQUERY
// ==========================
// import './plugins/js/overlayscrollbars.browser.es6.min';
// import './plugins/js/popper.min';
// import './plugins/js/bootstrap.min';
// import './plugins/js/adminlte';
// import './plugins/loader/waitMe.js?script';
// import './plugins/DataTables/js/dataTables.js?script';
// import './plugins/DataTables/js/dataTables.bootstrap5.js?script';
// import './plugins/DataTables/js/dataTables.responsive.js?script';
// import './plugins/DataTables/js/responsive.bootstrap5.js?script';
// import './plugins/DataTables/js/dataTables.buttons.js?script';
// import './plugins/DataTables/js/buttons.bootstrap5.js?script';
// import './plugins/DataTables/button/buttons.html5.min.js?script';
// import './plugins/DataTables/button/buttons.print.min.js?script';
// import './plugins/DataTables/button/buttons.colVis.min.js?script';
// import './plugins/DataTables/lib/jszip.min.js?script';
// import './plugins/DataTables/lib/pdfmake.min.js?script';
// import './plugins/DataTables/lib/vfs_fonts.js?script';
// import './plugins/fontawesome6.7.2/js/all.min.js?script';
// import './plugins/select2/select2.full.min.js?script';
// import './plugins/bootstrap-datepicker/bootstrap-datepicker.min.js?script';
// import './plugins/tooltipster/dist/js/tooltipster.bundle.min.js?script';
// import './plugins/EasyAutocomplete/dist/jquery.easy-autocomplete.min.js?script';
// import './plugins/tippy-bundle.umd.min.js?script';
// import './plugins/xlsx.full.min.js?script';
// import './plugins/quill/quill.min.js?script';
// import './plugins/pdf/pdf.min.js?script';
// import './plugins/pdf/pdf-lib.min.js?script';
// import './plugins/webdatarocks-1.4.19/webdatarocks.toolbar.min.js?script';
// import './plugins/webdatarocks-1.4.19/webdatarocks.js?script';
// import './plugins/bootstrap3-typeahead.min.js?script';
// import './plugins/jstree/jstree.min.js?script';

// ==========================
// BOOTSTRAP & VITE DEFAULT
// ==========================
import './bootstrap';

// ==========================
// LIBRARY ESM MODERN
// ==========================
// Font Awesome (CSS & JS icons)
import '@fortawesome/fontawesome-free/css/all.min.css';
import '@fortawesome/fontawesome-free/js/all.min.js';
import Swal from 'sweetalert2';
import ClipboardJS from 'clipboard';
window.Swal = Swal;
window.ClipboardJS = ClipboardJS;

import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// ==========================
// HELPER CUSTOM
// ==========================
import { formatRupiah, initClipboard } from './helpers';
window.formatRupiah = formatRupiah;
initClipboard();