
<a href="#" class="modal-close" data-dismiss="modal" aria-label="Close"><em class="ti ti-close"></em></a>      
<div class="popup-body popup-body-md">
    <div class="row justify-content-center">
        <div class="col-lg-12 col-xl-12">
            <div class="status status-empty">
                <div class="status-icon bg-warning border-warning">
                    <em class="ti ti-wallet text-white"></em>
                </div>
                <span class="status-text">{{__('Before proceed to purchase token, please add your receiving wallet address. In order to receive your token, the wallet address is mandatory.')}}</span>
                <a href="javascript:void(0)" class="btn btn-primary add-user-wallet">{{__('Click here to add wallet')}}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function($) {
        var $ajax_modal = $('#ajax-modal');
        msg_is_wrong = (typeof msg_wrong !== "undefined") ? msg_wrong : "Something is Wrong!",
        $('.add-user-wallet').on('click', function (e) {
            $mc = $('.add-user-wallet').parents('.modal')
            $mc.modal('hide');
            e.preventDefault();
             if(user_wallet_address.length > 5){
                $.post(user_wallet_address, {
                    _token: csrf_token
                }).done(response => {
                    $ajax_modal.html(response);
                    init_inside_modal();
                    if ($ajax_modal.children('.modal').length > 0) {
                        $ajax_modal.children('.modal').modal('show');
                    }
                }).fail(function(xhr, status, error) {
                    show_toast('error',msg_is_wrong+'\n'+error);
                    _log(xhr, status, error);
                });
            }      
        })
    })(jQuery);
</script>