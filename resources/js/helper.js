import moment from "moment";
import accounting from "accounting";
import { functions } from "lodash";

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
export function formatlowerCase(value) {
    return value.toLowerCase();
}

export function convertDate(date) {
    // Convert string to Date object
    var datetimeObject = new Date(date);

    // Get the individual date and time components
    var year = datetimeObject.getFullYear();
    var month = ("0" + (datetimeObject.getMonth() + 1)).slice(-2);
    var day = ("0" + datetimeObject.getDate()).slice(-2);
    var hours = ("0" + datetimeObject.getHours()).slice(-2);
    var minutes = ("0" + datetimeObject.getMinutes()).slice(-2);

    // Create the desired datetime-locale format
    var formattedDatetime =
        year + "-" + month + "-" + day + "T" + hours + ":" + minutes;
    return formattedDatetime;
}

export function randColor(size) {
    function generate() {
        const randomColor =
            "#" + Math.floor(Math.random() * 16777215).toString(16);
        if (randomColor.length !== 7) {
            return generate();
        } else {
            return randomColor;
        }
    }

    var color = [];
    for (let index = 0; index < size; index++) {
        color.push(generate());
    }
    return color;
}

export function formatMonth(value) {
    if (value) {
        return moment(String(value)).format("YYYY-MM");
    }
}
export function formatDay(value) {
    if (value) {
        return moment(String(value)).format("MM-DD");
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

export function formatStatusDispen(value) {
    switch (value) {
        case 0:
            return `<div class="badge badge-danger">NON AKTIF</div>`;
            break;

        case 1:
            return `<div class="badge badge-warning">AKTIF</div>`;
            break;
        default:
            return `<div class="badge badge-primary">TIDAK SESUAI</div>`;
            break;
    }
}

export function formatBg(value) {
    const color = [
        "bg-danger",
        "bg-warning",
        "bg-success",
        "bg-secondary",
        "bg-info",
        "bg-primary",
        "bg-dark",
    ];
    const length = color.length;
    if (value > length) {
        const index = value % length;
        return color[index];
    } else {
        return color[value];
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
            return `<div class="badge badge-primary">EKSTERNAL</div>`;
            break;
        case "App\\Models\\Pay":
            return `<div class="badge badge-success">PEMBAYARAN</div>`;
            break;
        default:
            return `<div class="badge badge-secondary">LAIN LAIN</div>`;
            break;
    }
}

export function formatAccount(value) {
    switch (value) {
        case 1:
            return `<div class="badge badge-info">HUTANG</div>`;
            break;
        case 2:
            return `<div class="badge badge-warning">PERIODIK</div>`;
            break;
        default:
            return `<div class="badge badge-secondary">INSIDENTAL</div>`;
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
        return `<div class="text-success">+ ${c}</div>`;
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
