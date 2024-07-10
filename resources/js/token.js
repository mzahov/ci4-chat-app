import $ from 'jquery';

export function getAccessToken() {
    const accessToken = window.localStorage.getItem('access_token');
    return accessToken;
}

function getRefreshToken() {
    const refreshToken = window.localStorage.getItem('refresh_token');
    return refreshToken;
}

function setAccessToken(token) {
    if (!tokenValidation(token)) {
        throw new Error('Invalid JWT token');
    }
    window.localStorage.setItem('access_token', token);
}

function setRefreshToken(token) {
    if (!tokenValidation(token)) {
        throw new Error('Invalid JWT token');
    }
    window.localStorage.setItem('refresh_token', token);
}

function tokenValidation(token) {
    if (!token || typeof token !== 'string') {
        return false;
    }

    const tokenParts = token.split('.');
    return tokenParts.length === 3;
}

export function tokenExpired() {
    const token = getAccessToken();
    if (!token) throw new Error('Missing token');
    
    const decodedToken = JSON.parse(atob(token.split('.')[1]));
    const currentTime = Math.floor(Date.now() / 1000);

    return decodedToken.exp < currentTime;
}


export function issueJwt() {
    return new Promise((resolve, reject) => {
        $.ajax({
            url: "/auth/token",
            type: "GET",
            dataType: 'json',
            success: function(data) {
                try {
                    setAccessToken(data.access_token);
                    setRefreshToken(data.refresh_token);
                    resolve(data.access_token);
                } catch (error) {
                    reject(error);
                }
            },
            error: function(err) {
                reject(err);
            }
        });
    });
}

export function renewToken() {
    const refreshToken = getRefreshToken();
    
    if (!refreshToken) {
        return Promise.reject(new Error('Refresh token not found'));
    }

    return new Promise((resolve, reject) => {
        $.ajax({
            url: '/auth/refresh',
            method: 'POST',
            data: { refresh_token: refreshToken },
            dataType: 'json',
            success: function(response) {
                try {
                    const newAccessToken = response.access_token;
                    setAccessToken(newAccessToken);
                    resolve(newAccessToken);
                } catch (error) {
                    reject(error);
                }
            },
            error: function(err) {
                reject(err);
            }
        });
    });
}

$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
    const token = getAccessToken();
    if (token) {
        jqXHR.setRequestHeader('Authorization', 'Bearer ' + token);
    }
});

let isRefreshing = false;
let requestQueue = [];

function processQueue(error) {
    while (requestQueue.length) {
        const req = requestQueue.shift();
        if (!error) {
            $.ajax(req);
        } else {
            if (typeof req.error === 'function') {
                req.error(error);
            }
        }
    }
}

$(document).ajaxError(function(event, jqXHR, ajaxSettings) {
    if (jqXHR.status === 401) {
        if (!isRefreshing) {
            isRefreshing = true;

            renewToken().then(newAccessToken => {
                isRefreshing = false;
                processQueue();

                const retryRequest = {
                    ...ajaxSettings,
                    headers: {
                        ...ajaxSettings.headers,
                        'Authorization': 'Bearer ' + newAccessToken
                    }
                };
                $.ajax(retryRequest);
            }).catch(() => {
                isRefreshing = false;
                // window.location = '/login';
                processQueue(new Error('Token refresh failed'));
            });
        } else {
            requestQueue.push({
                ...ajaxSettings,
                headers: ajaxSettings.headers || {}
            });
        }
    }
});