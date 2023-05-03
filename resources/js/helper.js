import moment from "moment";

export function formatDate(value) {
    if (value) {
        return moment(String(value)).format('YYYY-MM-DD');
    }
}
export function formatDateTimestamp(value) {
    if (value) {
        return moment(String(value)).format('YYYY-MM-DD HH:MM:SS A');
    }
}
export function formatMonth(value) {
    if (value) {
        return moment(String(value)).format('YYYY-MM');
    }
}
