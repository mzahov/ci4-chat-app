import $ from 'jquery';
window.$ = $;
import '@popperjs/core';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
import select2 from "select2";
select2();
import { manageConnection, conn } from './websocket';
import { showRoom, saveRoom, searchRoom } from './list';
import { setMessage, sendFile, resetChatRoom, resetChatState } from './chat';
// import { lazyLoadDetailsImages, toggleDetailsBox } from './details';

$(function () {
    manageConnection(); // Open new WS Connection or keep the old one

    $('#room-users-field').select2({width: '100%'});

    // Send messages
    $('#send-message').on('click', setMessage);

    $('#message').on('keydown', function(e) {
        if ((e.which === 13 || e.key == 'Enter') && !e.shiftKey) {
            e.stopPropagation();
            setMessage();
            return false;
        }
    });

    $('#message').on('input', function() {
        $(this).css('height', 'auto');
        $(this).css('height', (this.scrollHeight) + 'px');
    })

    // Open chat room
    let currentRoom = null;

    $('.chat-rooms').on('click', '.chat', function() {
        const roomId = $(this).data('id');
        
        if (currentRoom === roomId) return;

        resetChatRoom();
        resetChatState();

        showRoom(roomId);
        // toggleDetailsBox();
        
        currentRoom = roomId;
    });

    // Return to chat list
    $('.back-button').on('click', function() {
        $('.chat-list').removeClass('d-none');
        $('.chat-messages').addClass('d-none');

        resetChatRoom();
        resetChatState();

        // toggleDetailsBox(false);
        currentRoom = null;

        const emptyConversation = $('<div class="align-items-center d-flex justify-content-center h-100 text-muted" id="empty-conversation">').text('Please select a chat room to join the conversation!');
        $('#chat-body').append(emptyConversation);
    });

    // On Resize change chat rooms/messages view
    $(window).on('resize', function() {
        if ($(window).width() >= 992) {
            $('.chat-list').removeClass('d-none');
            $('.chat-messages').removeClass('d-none');
        } else {
            if (currentRoom) {
                $('.chat-list').addClass('d-none');
                $('.chat-messages').removeClass('d-none');
            } else {
                $('.chat-list').removeClass('d-none');
                $('.chat-messages').addClass('d-none');
            }
        }
        // lazyLoadDetailsImages();
    }).trigger('resize');

    // Create Room 
    $('#save-room-button').on('click', function(e) {
        e.preventDefault();

        const form = $('#create-room-form')[0];
        saveRoom(form);
    });

    $('.logout-btn').on('click', function() {
        conn.close();
        localStorage.removeItem('access_token');
        localStorage.removeItem('refresh_token');
    });

    // Search in chat rooms
    $('#search-room').on('input', searchRoom);

    // Upload chat files
    $('.btn-upload-file').on('click', function() {
        $('#upload-chat-file-input').trigger('click');
    });

    $('#upload-chat-file-input').on('change', function() {
        const file = this.files[0];

        if (file && currentRoom) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('room_id', currentRoom);

            $.ajax({
                url: '/chat/upload',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    if (response.success) {
                        sendFile(currentRoom, response.file_name, response.file_type);          
                    } else {
                        console.error('File upload error: ' + response.error);
                    }
                }
            });
        } else {
            console.log(currentRoom);
            console.log(file);
        }
    });

});