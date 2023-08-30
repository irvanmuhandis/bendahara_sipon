import axios from "axios";
import Cookies from 'js-cookie';

const instance = axios.create({
    baseURL: "http://sipon.kyaigalangsewu.net", // Replace with your API base URL
});

function getCookieValue(cookieName) {
    const cookieString = document.cookie;
    const cookies = cookieString.split(";");

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i].trim();

        if (cookie.startsWith(cookieName + "=")) {
            const tokenWithPipe = cookie.substring(cookieName.length + 1);
            const tokenParts = tokenWithPipe.split("|");
            const valueAfterPipe = tokenParts[1];
            return valueAfterPipe;
        }
    }

    return null;
}

instance.interceptors.request.use(
    (config) => {
        var token = "Cookies.get('sipon_session')";
token = '129|IXz1vA0z0vWODtNC7cVva9E0yLdwzzz7rnZXiGvs';

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

export default instance;
