import moment from "moment";
import accounting from "accounting";

export function formatDate(value) {
    if (value) {
        return moment(String(value)).format("YYYY-MM-DD");
    }
}
export function formatDateTimestamp(value) {
    if (value) {
        return moment(String(value)).format("YYYY-MM-DD HH:MM:SS A");
    }
}
export function formatMonth(value) {
    if (value) {
        return moment(String(value)).format("YYYY-MM");
    }
}

export function formatStatus(value) {
    switch (value) {
        case 1:
            return `<div class="badge badge-danger">BELUM DIBAYAR</div>`;
            break;

        case 2:
            return `<div class="badge badge-warning">BELUM LUNAS</div>`;
            break;

        case 3:
            return `<div class="badge badge-success">LUNAS</div>`;
            break;

        default:
            return `<div class="badge badge-primary">TIDAK SESUAI</div>`;
            break;
    }
}

export function formatClass(value) {
    switch (value) {
        case "App\\Models\\Bill":
            return `<div class="badge badge-info">TAGIHAN</div>`;
            break;
        case "App\\Models\\Debt":
            return `<div class="badge badge-warning">HUTANG</div>`;
            break;
        case "App\\Models\\Trans":
            return `<div class="badge badge-primary">TRANSAKSI</div>`;
            break;
        case "App\\Models\\Pay":
            return `<div class="badge badge-success">PEMBAYARAN</div>`;
            break;
        default:
            return `<div class="badge badge-secondary">LAIN LAIN</div>`;
            break;
    }
}

export function formatDiff(a, b) {
    var c = a - b;
    if (c < 0) {
        c = accounting.formatMoney(-1 * c, "Rp. ", 0);
        return `<div class="text-danger">- ${c}</div>`;
    } else {
        c = accounting.formatMoney(c, "Rp. ", 0);
        return `<div class="text-succes">+ ${c}</div>`;
    }
}

export function formatMoney(value) {
    return accounting.formatMoney(value, "Rp. ", 0);
}

export function formatMoney_2(c, mode) {
    c = accounting.formatMoney(c, "Rp. ", 0);
    if (mode == 2) {
        return `<div class="text-danger">- ${c}</div>`;
    } else {
        return `<div class="text-success">+ ${c}</div>`;
    }
}

export function formatlink(label) {
    return `<div class="btn btn-primary"> ${label}</div>`;
}
