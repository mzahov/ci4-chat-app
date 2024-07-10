import $ from 'jquery';
import { conn } from './websocket';
import { getFormattedTime } from './time';
import { updateRoomListLastMessage } from './list';

// DOM elements cache
const $CHAT_HEADER = $('#chat-header');
const $CHAT_BODY = $('#chat-body');
const $CHAT_FOOTER = $('#chat-footer');

const MAX_USERS = 2;

// Variables for message loading and pagination
const limit = 20;
let offset = 0,
    loading = false,
    noMoreMessages = false;

// Sends a message to the specified room
function sendMessage(roomId, messageContent) {
    const message = {
        command: 'message',
        room: roomId,
        message: messageContent
    };

    // Send the message via WebSocket
    conn.send(JSON.stringify(message));

    // Get the current timestamp
    const getDateTimestamp = new Date();
    const messageTime = Math.floor(getDateTimestamp / 1000);

    // Add the message to the chat UI
    populateMessage($('#username').text(), messageContent, messageTime, roomId, true);

    // Scroll chat to the bottom
    setChatScrollBottom();
}

export function sendFile(roomId, fileName, fileType) {
    const file = {
        command: 'file',
        file: fileName,
        file_type: fileType,
        room: roomId
    }
    conn.send(JSON.stringify(file));

    // Get the current timestamp
    const getDateTimestamp = new Date();
    const messageTime = Math.floor(getDateTimestamp / 1000);

    // Add the message to the chat UI
    populateMessage($('#username').text(), fileName, messageTime, roomId, true, false, fileType);

    // Scroll chat to the bottom
    setChatScrollBottom();
}

// Resets the chat room UI elements
export function resetChatRoom() {
    $('#room-name').text('');
    $('#room-users').empty();
    $CHAT_HEADER.addClass('d-none');
    $CHAT_BODY.empty();
    $('#empty-conversation').removeClass('d-none');
    $CHAT_FOOTER.addClass('d-none');
}

// Shows the chat header and footer, hides the empty conversation message
function showChatParts() {
    $CHAT_HEADER.removeClass('d-none');
    $('#empty-conversation').addClass('d-none');
    $CHAT_FOOTER.removeClass('d-none');
}

let isRoomOpen = false; 

// Resets the chat state variables
export function resetChatState() {
    offset = 0;
    isRoomOpen = false;
    loading = false;
    noMoreMessages = false;
    lastSender = null;
    lastMessageTime = 0;
    firstSender = null;
    firstMessageTime = Infinity;
}

// Populates the chat room with room data and messages
export function populateChatRoom(roomData) {
    resetChatState();

    $('#room-name').text('#' + roomData.room.name);
    $CHAT_FOOTER.data('room', roomData.room.id);

    $('#room-users').empty();

    if (roomData.roomUsers && roomData.roomUsers.length > 0) {
        // Display user avatars in the room
        $.each(roomData.roomUsers, function(index, user) {
            let avatar = '';
            if (MAX_USERS >= (index + 1)) {
                avatar = $('<div class="avatar rounded-circle user-select-none"><span class="avatar-32">' + user.username[0] + '</span></div>');
                $('#room-users').append(avatar);
            }
        });

        // Display additional users count if there are more than max users
        const countAdditionalRoomUsers = roomData.roomUsers.length - MAX_USERS;

        if (countAdditionalRoomUsers > 0) {
            const countedUsersAvatar = $('<div class="avatar rounded-circle user-select-none"><span class="avatar-32">+' + countAdditionalRoomUsers + '</span></div>');
            $('#room-users').append(countedUsersAvatar);
        }

    } else {
        $('#room-users').text('No users in the room');
    }
    isRoomOpen = true;
    
    $CHAT_BODY.empty();
    
    loadMessages(roomData.room.id);
    showChatParts();
}

// Variables to keep track of the last and first message details
let lastSender = null;
let lastMessageTime = 0;
let firstSender = null;
let firstMessageTime = Infinity;

// Populates the chat with a new message
export function populateMessage(author, messageContent, messageTime, roomId, isCurrentUser, prepend = false, messageType = 'message') {
    const messageClass = isCurrentUser ? "message-own" : "message-user";
    const currentUser = $('#username').text();
    const senderName = isCurrentUser ? "You" : author;
    const formattedTime = getFormattedTime(messageTime);

    // Check if the message belongs to the current room
    if (roomId !== $CHAT_FOOTER.data('room')) {
        return;
    }

    // Update the room list with the last message
    if (!loading) {
        updateRoomListLastMessage(roomId, messageContent, messageTime, senderName, messageType);
    }

    let appendToLastMessageBox = false;

    // Check if the message should be appended to the last message box
    if (prepend) {
        if (firstSender === senderName && ((firstMessageTime - messageTime) / 1000) <= 1) {
            appendToLastMessageBox = true;
        }
    } else {
        if (lastSender === senderName && ((messageTime - lastMessageTime) / 1000) <= 1) {
            appendToLastMessageBox = true;
        }
    }

    if (messageType !== 'message') appendToLastMessageBox = false;

    // Append the message to the existing message box or create a new one
    if (appendToLastMessageBox) {
        const targetMessageContainer = prepend ? $('.message-box').first() : $('.message-box').last();
        const newMessageContent = $('<p class="chat-message"><span></span></p>');
        newMessageContent.find('span').text(messageContent);
        targetMessageContainer.append(newMessageContent);
        targetMessageContainer.append('<div class="clearfix"></div>');
        targetMessageContainer.find('.message-time').addClass('d-none');
        const newMessageTime = $('<p class="message-time"></p>').text(formattedTime);
        targetMessageContainer.append(newMessageTime);
    } else {
        const messageBoxContent = $(`
            <div class="message ${messageClass}">
                <div class="message-box">
                    <p class="message-sender"></p>
                    <p class="chat-message"><span></span></p>
                    <div class="clearfix"></div>
                    <p class="message-time"></p>
                </div>
            </div>
        `);

        messageBoxContent.find('.message-sender').text(senderName);

        const fileUrl = `chat/show-file/${roomId}/${messageContent}/${messageType}`;

        if (messageType === 'image') 
        {
            messageBoxContent.find('.chat-message').remove();

            $(`<img src="${fileUrl}" alt="Image" width="200px" height="200px" class="chat-image">`).insertAfter(messageBoxContent.find('.message-sender'));
        } 
        else if (messageType === 'video') 
        {
            messageBoxContent.find('.chat-message').remove();
            
            $(`<video controls class="chat-video"><source src="${fileUrl}" type="video/mp4">Your browser does not support the video tag.</video>`).insertAfter(messageBoxContent.find('.message-sender'));
        } 
        else if (messageType === 'file') 
        {
            messageBoxContent.find('.chat-message span').append(`<a href="${fileUrl}" target="_blank" class="chat-file"><i class="fas fa-file-lines"></i> АСД</a>`);
        } 
        else 
        {
            messageBoxContent.find('.chat-message span').text(messageContent);
        }

        messageBoxContent.find('.message-time').text(formattedTime);

        const avatarContent = $(`
            <div class="avatar rounded-circle user-select-none">
                <span class="avatar-32"></span>
            </div>
        `);

        avatarContent.find('span').text(isCurrentUser ? currentUser.charAt(0) : author.charAt(0));

        if (isCurrentUser) {
            messageBoxContent.append(avatarContent);
        } else {
            messageBoxContent.prepend(avatarContent);
        }

        if (prepend) {
            $CHAT_BODY.prepend(messageBoxContent);
            firstSender = senderName;
            firstMessageTime = messageTime;
        } else {
            $CHAT_BODY.append(messageBoxContent);
            lastSender = senderName;
            lastMessageTime = messageTime;
        }
    }
}

// Loads messages for the specified room with pagination
function loadMessages(roomId) {

    if (!roomId) return;

    if (loading || noMoreMessages) return;

    loading = true;

    return $.ajax({
        url: 'chat/get-messages',
        method: 'GET',
        data: {
            roomId: roomId,
            limit: limit,
            offset: offset
        },
        dataType: 'json',
        success: function(data) {
            if (data) {
                const messages = Object.values(data);

                if (messages.length < limit) {
                    noMoreMessages = true;
                }

                if (messages.length > 0) {
                    const loadOldMessages = offset !== 0;
                    offset += limit;
                    let currentMessage = '';
                    let currentType = '';

                    if (!loadOldMessages) {
                        messages.forEach(message => {
                            if (message.file) {
                                currentMessage = message.file.name;
                                currentType = message.file.type;
                            } else {
                                currentMessage = message.message;
                                currentType = 'message';
                            }
                            
                            populateMessage(message.user, currentMessage, message.time, roomId, $('#username').text() === message.user, false, currentType);
                        });
                    } else {
                        messages.reverse().forEach(message => {
                            if (message.file) {
                                currentMessage = message.file.name;
                                currentType = message.file.type;
                            } else {
                                currentMessage = message.message;
                                currentType = 'message';
                            }
                            
                            populateMessage(message.user, currentMessage, message.time, roomId, $('#username').text() === message.user, true, currentType);
                        });
                    }
                    const previousHeight = $CHAT_BODY[0].scrollHeight;

                    if (loadOldMessages) {
                        $CHAT_BODY.scrollTop($CHAT_BODY[0].scrollHeight - previousHeight);
                    } else {
                        setChatScrollBottom();
                    }
                }
            }

            loading = false;
        },
        error: function() {
            console.error('Failed to load messages');
            loading = false;
        }
    });
}

// Sends a message to the chat room
export function setMessage() {
    const $message = $('#message');
    const roomId = $CHAT_FOOTER.data('room');

    if ($message.val() !== '' && roomId) {
        sendMessage(parseInt(roomId), $message.val());
    }

    $message.val('');
    $message.css('height', 'auto');
}

// Scrolls the chat to the bottom
export function setChatScrollBottom() {
    $CHAT_BODY.scrollTop($CHAT_BODY[0].scrollHeight);
}

// Event listener for scrolling to load more messages
$(function() {
    $CHAT_BODY.on('scroll', function() {
        if ($CHAT_BODY.scrollTop() === 0 && !loading && !noMoreMessages && isRoomOpen) {
            const roomId = $CHAT_FOOTER.data('room');
            loadMessages(roomId);
        }
    });
});