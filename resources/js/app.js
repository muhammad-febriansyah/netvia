import $ from 'jquery';
import Swal from 'sweetalert2';
import Cleave from 'cleave.js';
import { createIcons, icons } from 'lucide';
import 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';

// Expose jQuery globally so inline page scripts (DataTables/Select2 init) can use it.
window.$ = window.jQuery = $;
window.Swal = Swal;
window.Cleave = Cleave;

// Wire Select2 onto the global jQuery instance.
select2();

/**
 * Indonesian DataTables language strings, reused by every server-side table.
 */
const dataTablesLang = {
    sProcessing: 'Memuat…',
    sLengthMenu: 'Tampilkan _MENU_ data',
    sZeroRecords: 'Data tidak ditemukan',
    sEmptyTable: 'Tidak ada data',
    sInfo: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
    sInfoEmpty: 'Menampilkan 0 data',
    sInfoFiltered: '(disaring dari _MAX_ total)',
    sSearch: '',
    sSearchPlaceholder: 'Cari…',
    oPaginate: { sFirst: '«', sLast: '»', sNext: '›', sPrevious: '‹' },
};

const Netvia = {
    /**
     * Refresh Lucide icons; call after any DOM injection (e.g. DataTable redraw).
     */
    icons() {
        createIcons({ icons });
    },

    /**
     * Success toast (top-end), per the project convention.
     */
    toast(title, icon = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
        });
    },

    /**
     * Delete confirmation dialog. Resolves to true when confirmed.
     *
     * @returns {Promise<boolean>}
     */
    async confirmDelete(text = 'Tindakan ini tidak bisa dibatalkan.') {
        const result = await Swal.fire({
            title: 'Hapus data ini?',
            text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            reverseButtons: true,
        });

        return result.isConfirmed;
    },

    /**
     * Render an AJAX error consistently: field errors (422) + an error alert.
     */
    ajaxError(xhr) {
        const data = xhr.responseJSON || {};

        if (xhr.status === 422 && data.errors) {
            $('.field-error').remove();
            $('.input-invalid').removeClass('input-invalid');

            Object.entries(data.errors).forEach(([field, messages]) => {
                const input = $(`[name="${field}"]`);
                input.addClass('input-invalid');
                input.closest('.form-field').append(
                    `<p class="field-error mt-1.5 text-[12.5px] text-red-600">${messages[0]}</p>`,
                );
            });
        }

        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: data.message || 'Terjadi kesalahan. Silakan coba lagi.',
        });
    },

    /**
     * Initialise Select2 on the given scope.
     */
    initSelect2(scope = document) {
        $(scope)
            .find('.select2')
            .each(function () {
                const $el = $(this);
                if ($el.data('select2')) {
                    return;
                }
                $el.select2({
                    width: '100%',
                    placeholder: $el.data('placeholder') || 'Pilih…',
                    allowClear: $el.data('allow-clear') ?? false,
                });
            });
    },

    /**
     * Initialise Rupiah masks (thousands separator on type; raw integer parsed
     * server-side via rupiah_clean()).
     */
    initRupiah(scope = document) {
        $(scope)
            .find('.rupiah-mask')
            .each(function () {
                if (this.dataset.cleaveBound) {
                    return;
                }
                this.dataset.cleaveBound = '1';
                new Cleave(this, {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralPositiveOnly: true,
                    delimiter: '.',
                });
            });
    },

    /**
     * Build a server-side DataTable with the project defaults.
     */
    dataTable(selector, options = {}) {
        const table = $(selector).DataTable({
            processing: true,
            serverSide: true,
            language: dataTablesLang,
            ...options,
        });

        table.on('draw', () => Netvia.icons());

        return table;
    },
};

window.Netvia = Netvia;

$(function () {
    // Send the CSRF token with every AJAX request.
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
    });

    Netvia.icons();
    Netvia.initSelect2();
    Netvia.initRupiah();

    // Convert a server flash message (set in the layout) into a toast.
    const flash = window.__flash;
    if (flash && flash.message) {
        Netvia.toast(flash.message, flash.type || 'success');
    }
});
