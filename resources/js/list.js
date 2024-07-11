import $ from 'jquery';
import { conn } from './websocket';
import { populateChatRoom, setChatScrollBottom } from './chat';
import { getFormattedTime } from './time';

// Array to store chat rooms
let chatRooms = [];
let searching = false;

// Fetches the list of chat rooms and populates the UI
export function populateRoomList() {
    return $.ajax({
        url: '/chat/list',
        method: 'GET',
        dataType: 'json',
        processData: false,
        contentType: false,
        success: function(data) {
            // Sort chat rooms by creation date
            chatRooms = data.sort(function(a, b) {
                return b.created_at - a.created_at;
            });

            // Clear the chat room list
            $('.chat-rooms').empty();

            // Render each chat room in the list
            chatRooms.forEach((value) => {
                renderChatRoom(value.id, value.author, value.name, value.message, value.created_at, value.avatar, value.type);
            });
        }
    });
}

// Renders a single chat room in the list
export function renderChatRoom(roomId, author, name, message, time, avatar, type = 'message', prepend = false) {
    const isCurrentUser = $('#username').text() === author;
    const senderName = isCurrentUser ? "You" : author;

    const roomListContent = $(`
        <div class="chat">
            <div class="avatar rounded-circle user-select-none">
                <span class="avatar-50"></span>
            </div>
            <div class="chat-info w-100 ps-3">
                <p class="room-name"></p>
                <p class="room-last-message"></p>
                <p class="room-last-message-date"></p>
            </div>
        </div>
    `);

    // Set chat room data and text
    roomListContent.attr('data-id', roomId);
    roomListContent.find('.room-name').text(name);

    if (avatar !== null) {
        roomListContent.find('.avatar span').append($(`<img src="chat/show-file/${roomId}/${avatar}/avatar">`));
    } else {
        roomListContent.find('.avatar span').text(name.charAt(0).toUpperCase());
    }

    if (message !== null) {
        if (type === 'message') {
            updateMessageTruncation(roomListContent, senderName, message);
        } else {
            updateMessageIconType(roomListContent, senderName, type)
        }
        roomListContent.attr('data-last-message-time', time);
        roomListContent.find('.room-last-message-date').text(getFormattedTime(time));
    } else {
        roomListContent.find('.room-last-message').text('No messages yet');
    }

    // Prepend or append the chat room based on the provided parameters
    if (prepend) {
        $('.chat-rooms').prepend(roomListContent);
    } else {
        $('.chat-rooms').append(roomListContent);
    }
}

// Sorts chat rooms when a new message is received
function sortChatRooms(roomId, message, time, author, type) {
    const roomIndex = chatRooms.findIndex(room => room.id === roomId);

    if (roomIndex !== -1 && !searching) {
        chatRooms[roomIndex].message = message;
        chatRooms[roomIndex].author = author;
        chatRooms[roomIndex].created_at = time;
        chatRooms[roomIndex].type = type;

        // Move the updated room to the top of the list
        const updatedRoom = chatRooms.splice(roomIndex, 1)[0];
        chatRooms.unshift(updatedRoom);

        // Clear the chat room list
        $('.chat-rooms').empty();

        // Re-render the chat rooms
        chatRooms.forEach((value) => {
            renderChatRoom(value.id, value.author, value.name, value.message, value.created_at, value.type);
        });
    }
}

// Displays the chat room and hides the room list on small screens
export function showRoom(roomId) {
    if (!roomId) return;

    getRoomDetails(roomId);

    if ($(window).width() <= 992) {
        $('#empty-conversation').addClass('d-none');
        $('.chat-list').addClass('d-none');
        $('.chat-messages').removeClass('d-none');
    }
}

// Fetches the details of a specific chat room
function getRoomDetails(roomId) {
    return $.ajax({
        url: '/chat/show/' + roomId,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                alert(response.error);
            } else {
                populateChatRoom(response);
                setChatScrollBottom();
            }
        }
    });
}

// Creates a new chat room and sends the data via WebSocket
export function saveRoom(form) {
    return $.ajax({
        url: '/chat/create',
        method: 'POST',
        data: new FormData(form),
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend: function() {
            $(form).find('div').find('span.error-text').text('');
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;
                // Render the new chat room in the list
                renderChatRoom(data.roomId, null, data.name, null, null, null, true);
                
                // Send the new room data via WebSocket
                const createRoomData = {
                    command: 'create',
                    room: data.roomId,
                    name: data.name,
                    avatar: data.avatar,
                    users: data.users
                };
                
                conn.send(JSON.stringify(createRoomData));

                const roomModalElement = document.getElementById('createRoomModal');
                const roomModal = bootstrap.Modal.getInstance(roomModalElement) || new bootstrap.Modal(roomModalElement);
                roomModal.hide();

                $('#create-room-form').find('input').val('');
                $('#create-room-form').find('select').val('').trigger('change');
            }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $.each(xhr.responseJSON.messages, function(prefix, val) {
                if (prefix.indexOf('.*') > -1) {
                    const sliced = prefix.replace('.*', '');
                    $(form).find('div').find('span.' + sliced + '_error').text(val);
                } else {
                    $(form).find('div').find('span.' + prefix + '_error').text(val);
                }
            });
        }
    });
}

// Search for a room by name and hide rooms that don't match the input
export function searchRoom() {
    // let foundedRooms = [];
    // chatRooms.map((element) => {
    //     if (element.name.toLowerCase().indexOf(input.toString().toLowerCase()) > -1) {
    //         foundedRooms.push(element);
    //     }
    // });

    // if (foundedRooms) {
    //     // Clear the chat room list
    //     $('.chat-rooms').empty();

    //     foundedRooms.forEach(value => renderChatRoom(value.id, value.author, value.name, value.message, value.created_at));
    // }

    const text = $('#search-room').val().toString().toLowerCase();

    searching = text !== '' ? true : false;
    
    $('.chat-rooms .chat').filter(function() {
        // Check if the room name contains the input text
        $(this).find('.room-name').text().toLowerCase().indexOf(text) === -1 ? $(this).hide() : $(this).show();
    });
}

// Updates the room list with the last message
export function updateRoomListLastMessage(roomId, messageContent, messageTime, senderName, messageType = 'message') {
    const $roomList = $('.chat-rooms');

    $roomList.find('.chat').each(function() {
        const room = $(this).data('id');

        if (room == roomId) {
            const $room = $(this);
            const $lastMessageTime = $(this).find('.room-last-message-date');

            // Update time value
            $lastMessageTime.text(getFormattedTime(messageTime));
            $(this).attr('data-last-message-time', messageTime);
            console.log(messageType);
            if (messageType === 'message') {
                updateMessageTruncation($room, senderName, messageContent);
            } else {
                updateMessageIconType($room, senderName, messageType)
            }

            sortChatRooms(roomId, messageContent, messageTime, senderName, messageType);

            return false;
        }
    });
}

// Check message max characters and update visible message length
function updateMessageTruncation($room, senderName, messageContent) {
    const $lastMessage = $room.find('.room-last-message');
    $lastMessage.attr('data-original-message', messageContent);

    const truncateAndUpdate = () => {
        const chatWidth = $lastMessage.width();
        const maxChars = Math.floor(chatWidth / 4);

        const originalMessage = $lastMessage.data('original-message');

        if (senderName.length + originalMessage.length > maxChars) {
            const truncatedMessage = truncateMessage(originalMessage, maxChars - senderName.length);
            $lastMessage.text(`${senderName}: ${truncatedMessage}`);
        } else {
            $lastMessage.text(`${senderName}: ${originalMessage}`);
        }
    }
    
    setTimeout(truncateAndUpdate, 0);

    $(window).off('resize.updateMessageTruncation').on('resize.updateMessageTruncation', truncateAndUpdate);
}

// Truncates the message to fit within the maximum length
function truncateMessage(message, maxLength) {
    if (message.length <= maxLength) {
        return message;
    }

    const lastSpaceIndex = message.lastIndexOf(' ', maxLength);

    return message.substring(0, lastSpaceIndex === -1 ? maxLength : lastSpaceIndex) + '...';
}

function updateMessageIconType($room, senderName, type) {
    const $lastMessage = $room.find('.room-last-message');

    // Define icon classes for different message types
    let iconClass = '';
    if (type === 'image') {
        iconClass = 'fas fa-file-image';
    } else if (type === 'video') {
        iconClass = 'fas fa-file-video';
    } else {
        iconClass = 'fas fa-file-lines';
    }

    // Update the last message with the icon and sender name
    $lastMessage.html(`${senderName}: <i class="${iconClass}"></i> ${type.charAt(0).toUpperCase() + type.slice(1)}`);
}