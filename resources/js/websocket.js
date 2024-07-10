import { getAccessToken, tokenExpired, renewToken, issueJwt } from './token';
import { populateMessage, setChatScrollBottom } from './chat';
import { renderChatRoom, populateRoomList, updateRoomListLastMessage } from './list';


export let conn = null;
let timerReconnect = null;
let heartbeatInterval = null;

const reconnectInterval = 3000;
const heartbeatIntervalTime = 30000;

function connectWebSocket() {
    let accessToken = getAccessToken();

    if (!accessToken || accessToken === null) {
        issueJwt().then((newAccessToken) => {
            accessToken = newAccessToken;
            initiateWebSocketConnection(newAccessToken);
        }).catch(error => {
            console.error('Error issuing JWT:', error);
        });

        return;
    }

    if (tokenExpired()) {
        renewToken().then(newToken => {
            accessToken = newToken;
            initiateWebSocketConnection(accessToken);
        }).catch(error => {
            console.error('Token renewal failed:', error);
        });
    } else {
        initiateWebSocketConnection(accessToken);
    }
}

function initiateWebSocketConnection(token) {
    conn = new WebSocket(`ws://localhost:8080/?token=${token}`);
    
    populateRoomList(); // Ajax request to broadcast all chat rooms
    
    conn.onopen = function() {
        if (timerReconnect) {
            clearInterval(timerReconnect);
            timerReconnect = null;
        }
        
        startHeartbeat();
    };

    conn.onmessage = function(e) {
        console.log('Message received: ' + e.data);
        const response = JSON.parse(e.data);

        if (response.type === 'message' || response.type === 'file') {
            const content = response.type === 'message' ? response.message : response.file;
            const type = response.type === 'message' ? 'message' : response.file_type;
            const audio = new Audio('sounds/notification.mp3');
            audio.play();
            
            populateMessage(response.author, content, response.time, response.room, false, false, type);
            updateRoomListLastMessage(response.room, content, response.time, response.author, type)
            setChatScrollBottom();

        } else if (response.type === 'create') {
            renderChatRoom(response.id, null, response.name, null, null, null, true);
        }
    };

    conn.onerror = function(e) {
        console.error('WebSocket error:', e);
    };

    conn.onclose = function(e) {
        console.log('WebSocket closed:', e);

        if (heartbeatInterval) {
            clearInterval(heartbeatInterval);
            heartbeatInterval = null;
        }

        if (!timerReconnect) {
            timerReconnect = setInterval(() => {
                if (conn.readyState === WebSocket.CLOSED || conn.readyState === WebSocket.CLOSING) {
                    connectWebSocket();
                }
            }, reconnectInterval);
        }
    };
}

function startHeartbeat() {
    if (heartbeatInterval) {
        clearInterval(heartbeatInterval);
    }

    heartbeatInterval = setInterval(() => {
        if (conn.readyState === WebSocket.OPEN) {
            conn.send(JSON.stringify({ command: 'ping' }));
        }
    }, heartbeatIntervalTime);
}

export function getConnection() {
    return conn;
}

export function manageConnection() {
    if (navigator.onLine) { // Device is online
        connectWebSocket();
    } else { // Device is offline
        if (conn !== null) {
            conn.close();
        }
    }
}

window.addEventListener('online', manageConnection);
window.addEventListener('offline', manageConnection);