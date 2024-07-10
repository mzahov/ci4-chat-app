// import $ from 'jquery';

// const $CHAT_LIST = $('.chat-list');
// const $CHAT_DETAILS = $('.chat-details');
// const $CHAT_MESSAGES = $('.chat-messages');

// export function getRoomDetails() {
// }

// export function toggleDetailsBox(state = true) {
//     if (state) {
//         $CHAT_LIST.removeClass('col-lg-4').addClass('col-lg-3');
//         $CHAT_MESSAGES.removeClass('col-lg-8').addClass('col-lg-6');
//         $CHAT_DETAILS.addClass('d-lg-block');
//     } else {
//         $CHAT_LIST.removeClass('col-lg-3').addClass('col-lg-4');
//         $CHAT_MESSAGES.removeClass('col-lg-6').addClass('col-lg-8');
//         $CHAT_DETAILS.removeClass('d-lg-block');
//     }
// }

// export function lazyLoadDetailsImages() {
//     const $galleryItems = $('.chat-details .gallery').find('.item');

//     if ($galleryItems.length === 0) return;

//     $galleryItems.each(function() {
//         const $img = $(this).find('img');
//         $img.attr('src', $img.data('src'));
//     });
// }