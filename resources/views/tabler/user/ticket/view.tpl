{include file='user/header.tpl'}

<div class="page-wrapper">
    <div class="container-xl">
        <div class="page-header d-print-none text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        <span class="home-title">{trans key='user.ticket_view_title'}</span>
                    </h2>
                    <div class="page-pretitle my-3">
                        <span class="home-subtitle">{trans key='user.ticket_view_subtitle'}</span>
                    </div>
                </div>
                {if $ticket->status !== 'closed'}
                <div class="col-auto">
                    <div class="btn-list">
                        <button class="btn btn-red" data-bs-toggle="modal"
                                data-bs-target="#close_ticket_confirm_dialog">
                            <i class="icon ti ti-x"></i>
                            {trans key='common.close'}
                        </button>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                           data-bs-target="#add-reply">
                            <i class="icon ti ti-plus"></i>
                            {trans key='user.add_reply'}
                        </a>
                    </div>
                </div>
                {/if}
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <div class="row row-cards">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="h1 my-2 mb-3">#{$ticket->id} {$ticket->title}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row-deck my-3">
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">{trans key='user.ticket_status'}</div>
                            </div>
                            <div class="h1 mb-3">{$ticket->status}</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">{trans key='user.ticket_type'}</div>
                            </div>
                            <div class="h1 mb-3">{$ticket->type}</div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="subheader">{trans key='user.ticket_open_time'}</div>
                                </div>
                                <div class="h1 mb-3">{$ticket->datetime}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center my-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="divide-y">
                                {foreach $comments as $comment}
                                <div>
                                    <div class="row">
                                        <div class="col">
                                            <div>
                                                {$comment->comment}
                                            </div>
                                            <div class="text-secondary my-1">{trans key='user.ticket_replied_at' name=$comment->commenter_name time=$comment->datetime}</div>
                                        </div>
                                        <div class="col-auto">
                                            <div>
                                                # {$comment->comment_id + 1}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="add-reply" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{trans key='user.add_reply'}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <textarea id="reply-comment" class="form-control" rows="15" placeholder="{trans key='user.reply_content_placeholder'}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">{trans key='common.cancel'}</button>
                    <button id="reply" class="btn btn-primary" data-bs-dismiss="modal"
                            hx-post="/user/ticket/{$ticket->id}" hx-swap="none"
                            hx-vals='js:{ comment: document.getElementById("reply-comment").value }'>
                        {trans key='user.reply'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal modal-blur fade" id="close_ticket_confirm_dialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{trans key='user.close_ticket'}</h5>
                    <button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p>{trans key='user.confirm_close_ticket'}</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn me-auto" data-bs-dismiss="modal">{trans key='common.cancel'}</button>
                    <button id="confirm_close" type="button" class="btn btn-primary"
                            hx-post="/user/ticket/{$ticket->id}/close" hx-swap="none">{trans key='common.confirm'}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {include file='user/footer.tpl'}
