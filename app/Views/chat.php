<?php $this->extend('layout') ?>

<?php $this->section('content'); ?>
<main class="chat-app">
    <div class="row h-100">
        <div class="col-12 col-lg-4 h-100 chat-list">
            <div class="card card-chat">
                <div class="card-body overflow-hidden h-100">
                    <div class="header pb-3">
                        <div class="user-profile d-flex align-items-center pb-3">
                            <div class="avatar rounded-circle user-select-none">
                                <span class="avatar-50"><?=auth()->user()->username[0]?></span>
                            </div>

                            <div class="fw-bold ps-3">
                                <div class="d-flex justify-content-between">
                                    <span id="username"><?=auth()->user()->username?></span>
                                </div>
                                <p class="new-room m-0">
                                    <a href="/logout" class="btn btn-sm btn-link p-0 text-decoration-none logout-btn">Logout</a>
                                </p>
                            </div>

                        </div>
                        <div class="d-flex">
                            <input type="search" class="form-control" id="search-room" placeholder="Search...">
                            <button class="btn btn-chat rounded-pill ms-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createRoomModal"><i class="fas fa-pen-to-square"></i></button>
                        </div>
                    </div>
                    <div class="chat-rooms"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-8 ps-lg-0 h-100 chat-messages d-none d-lg-block">
            <div class="card card-chat">
                <div class="card-header d-none" id="chat-header">
                    <div class="d-flex align-items-center">
                        <span class="back-button d-lg-none me-2"><i class="fas fa-arrow-left"></i></span>
                        <span class="lead" id="room-name"></span>
                        <div class="avatar-group" id="room-users"></div>
                    </div>
                </div>
                <div class="card-body overflow-y-auto h-100" id="chat-body">
                    <div class="align-items-center d-flex justify-content-center h-100 text-muted" id="empty-conversation">
                        Please select a chat room to join the conversation!
                    </div>
                </div>
                <div class="card-footer d-none" id="chat-footer">
                    <div class="input-group">
                        <input type="file" id="upload-chat-file-input" class="d-none">
                        <button class="btn btn-upload-file" type="button"><i class="fa-solid fa-circle-plus fs-4"></i></button>
                        <textarea class="form-control ps-3 rounded-pill border-start-0 border-end-0" id="message" rows="1" placeholder="Type a message"></textarea>
                        <button class="btn btn-chat rounded-pill mx-2" id="send-message" type="button"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="col-12 col-lg-3 ps-lg-0 h-100 chat-details d-none">
            <div class="card card-chat">
                <div class="card-body overflow-y-auto h-100">
                    <div class="row">
                        <div class="col-12" id="details-description">
                            <h1 class="details-header">Description</h1>
                            <p class="description">
                                Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quod, sint et.
                                Iure suscipit perspiciatis necessitatibus deleniti mollitia, culpa molestiae
                                ducimus officia animi non, illum pariatur reprehenderit voluptatibus. Veritatis, molestiae quod.
                            </p>
                            <hr>
                        </div>
                        <div class="col-12" id="details-members">
                            <h1 class="details-header">Members</h1>
                            <div class="members">
                                <div class="member">
                                    <div class="avatar rounded-circle user-select-none">
                                        <span class="avatar-32">G</span>
                                    </div>

                                    <p class="username">GreyDemon<span class="online"></span></p>
                                </div>
                                <div class="member">
                                    <div class="avatar rounded-circle user-select-none">
                                        <span class="avatar-32">G</span>
                                    </div>

                                    <p class="username">Goshe<span class="online"></span></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-12" id="details-files">
                            <hr>
                            <h1 class="details-header">Media & Files</h1>
                            <ul class="nav nav-tabs nav-pills nav-fill border-0 rounded-nav pb-3" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="media-tab" data-bs-toggle="tab" data-bs-target="#media" type="button" role="tab" aria-controls="media" aria-selected="true">Media</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="file-tab" data-bs-toggle="tab" data-bs-target="#file" type="button" role="tab" aria-controls="file" aria-selected="false">Files</button>
                                </li>
                            </ul>
                            <div class="tab-content" id="myTabContent">
                                <div class="gallery tab-pane fade show active" id="media" role="tabpanel" aria-labelledby="media-tab">
                                    <div class="item">
                                        <img data-src="https://fastly.picsum.photos/id/577/536/354.jpg?hmac=MGf_fraImy4RGbmwigVVeUJNDtQ2Oul2rXlEJDPAjKE" loading="lazy">
                                    </div>
                                </div>
                                <div class="files tab-pane fade show" id="file" role="tabpanel" aria-labelledby="file-tab">
                                    <div class="item">
                                        <img data-src="https://fastly.picsum.photos/id/577/536/354.jpg?hmac=MGf_fraImy4RGbmwigVVeUJNDtQ2Oul2rXlEJDPAjKE" loading="lazy">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div> -->
    </div>
</main>

<div class="modal" id="createRoomModal" tabindex="-1" aria-labelledby="createRoomModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="createRoomModalLabel">Create a new room</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="post" id="create-room-form">
                    <div class="mb-3">
                        <label for="room-name-field" class="form-label">Name <sup class="text-danger">*</sup></label>
                        <input type="text" class="form-control" name="name" id="room-name-field" aria-describedby="room-name-desc" />
                        <span class="text-danger error-text name_error"></span>
                        <small id="room-name-desc" class="form-text text-muted d-block">Enter a name for the room.</small>
                    </div>
                    <div class="mb-3">
                        <label for="room-description-field" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="room-description-field" aria-describedby="room-description-desc"></textarea>
                        <span class="text-danger error-text description_error"></span>
                        <small id="room-description-desc" class="form-text text-muted d-block">Enter a description for the room.</small>
                    </div>
                    <div class="mb-3">
                        <label for="room-users-field" class="form-label">Users <sup class="text-danger">*</sup></label>
                        <select name="users[]" id="room-users-field" data-placeholder="Choose users" aria-describedby="room-users-desc" multiple="multiple">
                            <?php foreach($users as $user): ?>
                            <option value="<?=$user->id?>"><?=$user->username?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-danger error-text users_error"></span>
                        <small id="room-users-desc" class="form-text text-muted d-block">Select the users who will have access to this room.</small>
                    </div>
                    <div class="mb-3">
                        <label for="room-image-field" class="form-label">Image</label>
                        <input type="file" name="image" id="room-image-field" class="form-control" aria-describedby="room-image-desc">
                        <span class="text-danger error-text image_error"></span>
                        <small id="room-image-desc" class="form-text text-muted d-block">Select an image for the room or use default.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="save-room-button">Save</button>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>